<?php

namespace App\Livewire\Modulo\Battles;

use Livewire\Component;
use App\Models\Battle;
use App\Models\KillEvent;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MultiBattleShow extends Component
{
    /** 
     * IDs de las batallas concatenadas. 
     * Se declara como string|array porque Livewire 3 inyecta el parámetro 
     * antes de llamar a mount(), y el parámetro viene como string desde la URL.
     */
    public string|array $ids = [];

    /** @var \Illuminate\Database\Eloquent\Collection<Battle> */
    public $battles;

    /** @var array Datos agregados (players, totals, byDay, topKill, ...). */
    public array $aggregated = [];

    /** Filtro de la tabla de jugadores. */
    public string $searchPlayer = '';
    public string $guildFilter = '';

    /** Sort de la tabla de jugadores. */
    public string $sortField = 'kills';
    public string $sortDirection = 'desc';

    /** Paginación de la tabla de jugadores. */
    public int $perPagePlayers = 25;

    public function mount(string|array $ids): void
    {
        // ---- 1. Parsear IDs ----
        $rawIds = is_string($ids) ? explode(',', $ids) : (array) $ids;
        
        $parsed = array_filter(
            array_map('intval', $rawIds),
            fn($id) => $id > 0
        );
        $parsed = array_values(array_unique($parsed));

        if (count($parsed) < 2) {
            abort(404, 'Se necesitan al menos 2 reportes para concatenar.');
        }

        $this->ids = $parsed;

        // ---- 2. Cargar batallas con participantes ----
        $this->battles = Battle::with([
                'participants.guild',
                'participants.alliance',
            ])
            ->whereIn('id', $this->ids)
            ->orderBy('start_time')
            ->get();

        if ($this->battles->count() < 2) {
            abort(404, 'No se encontraron al menos 2 batallas con esos IDs.');
        }

        // ---- 3. Construir agregación ----
        $this->aggregated = $this->buildAggregation();
    }

    // ==================================================================
    //                      CONSTRUCCIÓN DE LA AGREGACIÓN
    // ==================================================================

    private function buildAggregation(): array
    {
        $linhirGuildId = (string) config('app.linhir_gremio_id');

        $players = [];
        $totalKillEvents  = 0;
        $totalDeathEvents = 0;

        // ---- Recorrer todos los participantes de todas las batallas ----
        foreach ($this->battles as $battle) {
            $totalKillEvents  += (int) $battle->total_kills;
            $totalDeathEvents += (int) $battle->participants->sum('deaths');

            foreach ($battle->participants as $p) {
                $key = $p->player_id; // Albion ID: clave de deduplicación

                if (!isset($players[$key])) {
                    $players[$key] = [
                        'player_id'    => $p->player_id,
                        'player_name'  => $p->player_name,
                        'guild_id'     => $p->guild_id,
                        'guild'        => $p->guild?->name ?? 'Sin gremio',
                        'alliance'     => $p->alliance?->name,
                        'is_linhir'    => ((string) $p->guild_id === $linhirGuildId),
                        'kills'        => 0,
                        'deaths'       => 0,
                        'kill_fame'    => 0,
                        'damage_done'  => 0,
                        'healing_done' => 0,
                        'appearances'  => 0,
                        'battles'      => [],
                    ];
                }

                $players[$key]['kills']        += (int) $p->kills;
                $players[$key]['deaths']       += (int) $p->deaths;
                $players[$key]['kill_fame']    += (int) $p->kill_fame;
                $players[$key]['damage_done']  += (float) $p->damage_done;
                $players[$key]['healing_done'] += (float) $p->support_healing_done;
                $players[$key]['appearances']  += 1;
                $players[$key]['battles'][]     = $battle->id;
            }
        }

        $playersCollection = collect($players);

        // ---- Bajas evento vs bajas únicas ----
        $uniquePlayers    = $playersCollection->count();
        $uniqueCasualties = $playersCollection->where('deaths', '>', 0)->count();
        $survivors        = $uniquePlayers - $uniqueCasualties;
        $survivalRate     = $uniquePlayers > 0
            ? round(($survivors / $uniquePlayers) * 100, 1)
            : 0.0;

        // ---- Duración: activa vs pared ----
        $activeSeconds = $this->battles->sum(function (Battle $b) {
            if (!$b->start_time || !$b->end_time) return 0;
            return $b->start_time->diffInSeconds($b->end_time);
        });

        $firstStart = $this->battles->first()->start_time;
        $lastEnd    = $this->battles->last()->end_time;

        $dateRange = '—';
        $wallClock = '—';
        if ($firstStart && $lastEnd) {
            $dateRange = $firstStart->toDateString() === $lastEnd->toDateString()
                ? $firstStart->format('d/m/Y')
                : $firstStart->format('d/m/Y') . ' – ' . $lastEnd->format('d/m/Y');

            $wallSeconds = $firstStart->diffInSeconds($lastEnd);
            $wallClock   = $this->formatDuration($wallSeconds);
        }

        // ---- TOPs ----
        $topKills  = $playersCollection->sortByDesc('kills')->first();
        $topDamage = $playersCollection->sortByDesc('damage_done')->first();
        $topHeals  = $playersCollection->sortByDesc('healing_done')->first();

        // ---- Desglose por día ----
        $byDay = $this->battles
            ->groupBy(fn(Battle $b) => $b->start_time?->toDateString() ?? 'sin-fecha')
            ->map(function (Collection $group, string $date) {
                return [
                    'date'           => $date,
                    'sessions'       => $group->count(),
                    'kill_events'    => (int) $group->sum('total_kills'),
                    'death_events'   => (int) $group->sum(fn($b) => $b->participants->sum('deaths')),
                    'total_fame'     => (int) $group->sum('total_fame'),
                    'active_seconds' => $group->sum(
                        fn($b) => $b->start_time && $b->end_time
                            ? $b->start_time->diffInSeconds($b->end_time)
                            : 0
                    ),
                    'battle_ids'     => $group->pluck('id')->toArray(),
                ];
            })
            ->sortKeys()
            ->values();

        // ---- Top kill fame de la campaña ----
        $topKillFameEvent = KillEvent::whereIn('battle_id', $this->battles->pluck('id'))
            ->orderByDesc('killer_kill_fame')
            ->first();

        // ---- Sanity check (Σ kills reportados == Σ deaths participantes) ----
        if ($totalKillEvents !== $totalDeathEvents) {
            \Log::warning('MultiBattleShow: descuadre kills/deaths', [
                'battle_ids'   => $this->ids,
                'kill_events'  => $totalKillEvents,
                'death_events' => $totalDeathEvents,
            ]);
        }

        return [
            'players'  => $playersCollection->sortByDesc('kills')->values(),
            'byDay'    => $byDay,
            'topKillFameEvent' => $topKillFameEvent,
            'totals'   => [
                'sessions'          => $this->battles->count(),
                'kill_events'       => $totalKillEvents,
                'death_events'      => $totalDeathEvents,
                'unique_players'    => $uniquePlayers,
                'unique_casualties' => $uniqueCasualties,
                'survivors'         => $survivors,
                'survival_rate_pct' => $survivalRate,
                'total_fame'        => (int) $this->battles->sum('total_fame'),
                'avg_deaths_player' => $uniquePlayers > 0
                    ? round($totalDeathEvents / $uniquePlayers, 2)
                    : 0.0,
                'kdr'               => $totalDeathEvents > 0
                    ? round($totalKillEvents / $totalDeathEvents, 2)
                    : null,
                'active_time'       => $this->formatDuration($activeSeconds),
                'wall_clock'        => $wallClock,
                'date_range'        => $dateRange,
                'guilds_count'      => $this->battles->flatMap(
                    fn($b) => $b->participants->pluck('guild_id')
                )->filter()->unique()->count(),
            ],
            'topKills'  => $topKills,
            'topDamage' => $topDamage,
            'topHeals'  => $topHeals,
        ];
    }

    // ==================================================================
    //                          INTERACCIONES UI
    // ==================================================================

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function updatingSearchPlayer(): void
    {
        // Forzar regreso a la primera página de la tabla de jugadores.
        request()->merge(['playersPage' => 1]);
    }

    public function updatingGuildFilter(): void
    {
        request()->merge(['playersPage' => 1]);
    }

    public function updatedPerPagePlayers(): void
    {
        request()->merge(['playersPage' => 1]);
    }

    public function resetFilters(): void
    {
        $this->searchPlayer = '';
        $this->guildFilter  = '';
        $this->sortField    = 'kills';
        $this->sortDirection = 'desc';
        request()->merge(['playersPage' => 1]);
    }

    // ==================================================================
    //                              HELPERS
    // ==================================================================

    private function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) return '0m';

        $days  = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $mins  = intdiv($seconds % 3600, 60);

        $parts = [];
        if ($days > 0)  $parts[] = "{$days}d";
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($mins > 0 || empty($parts)) $parts[] = "{$mins}m";

        return implode(' ', $parts);
    }

    // ==================================================================
    //                              RENDER
    // ==================================================================

    public function render()
    {
        // ---- Filtrar y ordenar la colección de jugadores ----
        $players = $this->aggregated['players'];

        if ($this->searchPlayer !== '') {
            $needle = mb_strtolower(trim($this->searchPlayer));
            $players = $players->filter(
                fn($p) => mb_strpos(mb_strtolower($p['player_name']), $needle) !== false
            )->values();
        }

        if ($this->guildFilter !== '') {
            $players = $players->filter(
                fn($p) => (string) $p['guild_id'] === $this->guildFilter
            )->values();
        }

        $players = $this->sortDirection === 'asc'
            ? $players->sortBy($this->sortField)->values()
            : $players->sortByDesc($this->sortField)->values();

        // ---- Paginación manual (pageName dedicado) ----
        $page    = (int) request()->query('playersPage', 1);
        $perPage = max(5, min(100, $this->perPagePlayers));

        $paginatedPlayers = new LengthAwarePaginator(
            $players->forPage($page, $perPage)->values(),
            $players->count(),
            $perPage,
            $page,
            [
                'path'     => url('/battles/multi'),
                'query'    => ['ids' => implode(',', $this->ids)],
                'pageName' => 'playersPage',
            ]
        );

        // ---- Lista de gremios para el filtro ----
        $guilds = $this->aggregated['players']
            ->filter(fn($p) => !empty($p['guild_id']))
            ->groupBy('guild_id')
            ->map(fn($g) => [
                'id'   => $g->first()['guild_id'],
                'name' => $g->first()['guild'],
            ])
            ->sortBy('name')
            ->values();

        return view('livewire.modulo.battles.multi-battle-show', [
            'paginatedPlayers' => $paginatedPlayers,
            'guilds'           => $guilds,
        ]);
    }
}
