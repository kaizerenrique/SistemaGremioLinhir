<?php

namespace App\Livewire\Modulo\Battles;

use Livewire\Component;
use App\Models\Battle;
use App\Models\KillEvent;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\BattleParticipant;
use App\Services\Discord\DiscordMessageService;
use Illuminate\Support\Facades\Log;

class MultiBattleShow extends Component
{
    
    use WithPagination;

    public string|array $ids = [];
    public $battles;

    // Filtros de la tabla de jugadores
    public string $searchPlayer = '';
    public string $guildFilter = '';
    public string $sortField = 'kills';
    public string $sortDirection = 'desc';
    public int $perPagePlayers = 25;

    // Resumen general
    public array $totals = [];

    // Estado para el botón de Discord
    public ?string $discordMessage = null;
    public ?string $discordError = null;
    public bool $sendingToDiscord = false;

    public function mount(string|array $ids): void
    {
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

        $this->battles = Battle::whereIn('id', $this->ids)
            ->orderBy('start_time')
            ->get();

        if ($this->battles->count() < 2) {
            abort(404, 'No se encontraron al menos 2 batallas con esos IDs.');
        }

        $this->calculateTotals();
    }

    private function calculateTotals(): void
    {
        $stats = BattleParticipant::whereIn('battle_id', $this->ids)
            ->selectRaw('
                SUM(kills) as total_kills,
                SUM(deaths) as total_deaths,
                COUNT(DISTINCT player_id) as unique_players,
                COUNT(DISTINCT CASE WHEN deaths > 0 THEN player_id END) as unique_casualties
            ')
            ->first();

        $totalKillEvents  = (int) ($stats->total_kills ?? 0);
        $totalDeathEvents = (int) ($stats->total_deaths ?? 0);
        $uniquePlayers    = (int) ($stats->unique_players ?? 0);
        $uniqueCasualties = (int) ($stats->unique_casualties ?? 0);
        $survivors        = $uniquePlayers - $uniqueCasualties;
        $survivalRate     = $uniquePlayers > 0 ? round(($survivors / $uniquePlayers) * 100, 1) : 0.0;

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
                : $firstStart->format('d/m') . ' – ' . $lastEnd->format('d/m/Y');

            $wallSeconds = $firstStart->diffInSeconds($lastEnd);
            $wallClock   = $this->formatDuration($wallSeconds);
        }

        $this->totals = [
            'sessions'          => $this->battles->count(),
            'kill_events'       => $totalKillEvents,
            'death_events'      => $totalDeathEvents,
            'unique_players'    => $uniquePlayers,
            'unique_casualties' => $uniqueCasualties,
            'survivors'         => $survivors,
            'survival_rate_pct' => $survivalRate,
            'total_fame'        => (int) $this->battles->sum('total_fame'),
            'avg_deaths_player' => $uniquePlayers > 0 ? round($totalDeathEvents / $uniquePlayers, 2) : 0.0,
            'kdr'               => $totalDeathEvents > 0 ? round($totalKillEvents / $totalDeathEvents, 2) : null,
            'active_time'       => $this->formatDuration($activeSeconds),
            'wall_clock'        => $wallClock,
            'date_range'        => $dateRange,
        ];
    }

    public function getGuildsSummaryProperty()
    {
        return BattleParticipant::whereIn('battle_id', $this->ids)
            ->whereNotNull('guild_id')
            ->leftJoin('guilds', 'battle_participants.guild_id', '=', 'guilds.id')
            ->leftJoin('alliances', 'guilds.alliance_id', '=', 'alliances.id')
            ->select(
                'battle_participants.guild_id',
                'guilds.name as guild_name',
                'alliances.name as alliance_name',
                DB::raw('COUNT(DISTINCT battle_participants.player_id) as players_count'),
                DB::raw('SUM(kills) as total_kills'),
                DB::raw('SUM(deaths) as total_deaths'),
                DB::raw('AVG(average_item_power) as avg_ip'),
                DB::raw('SUM(kill_fame) as total_fame')
            )
            ->groupBy('battle_participants.guild_id', 'guilds.name', 'alliances.name')
            ->orderByDesc('total_fame')
            ->get();
    }

    public function getAlliancesSummaryProperty()
    {
        return BattleParticipant::whereIn('battle_id', $this->ids)
            ->whereNotNull('alliance_id')
            ->leftJoin('alliances', 'battle_participants.alliance_id', '=', 'alliances.id')
            ->select(
                'battle_participants.alliance_id',
                'alliances.name as alliance_name',
                DB::raw('COUNT(DISTINCT battle_participants.player_id) as players_count'),
                DB::raw('SUM(kills) as total_kills'),
                DB::raw('SUM(deaths) as total_deaths'),
                DB::raw('AVG(average_item_power) as avg_ip'),
                DB::raw('SUM(kill_fame) as total_fame')
            )
            ->groupBy('battle_participants.alliance_id', 'alliances.name')
            ->orderByDesc('total_fame')
            ->get();
    }

    public function getPlayersProperty()
    {
        $query = BattleParticipant::whereIn('battle_id', $this->ids)
            ->leftJoin('guilds', 'battle_participants.guild_id', '=', 'guilds.id')
            ->select(
                'battle_participants.player_id',
                DB::raw('MAX(battle_participants.player_name) as player_name'),
                DB::raw('MAX(battle_participants.guild_id) as guild_id'),
                DB::raw('MAX(guilds.name) as guild_name'),
                DB::raw('SUM(kills) as kills'),
                DB::raw('SUM(deaths) as deaths'),
                DB::raw('SUM(kill_fame) as kill_fame'),
                DB::raw('SUM(damage_done) as damage_done'),
                DB::raw('SUM(support_healing_done) as healing_done'),
                DB::raw('COUNT(DISTINCT battle_participants.battle_id) as appearances')
            )
            ->groupBy('battle_participants.player_id');

        if ($this->searchPlayer !== '') {
            $query->having('player_name', 'like', '%' . $this->searchPlayer . '%');
        }
        if ($this->guildFilter !== '') {
            $query->having('guild_id', $this->guildFilter);
        }

        $allowedSorts = ['kills', 'deaths', 'damage_done', 'healing_done', 'appearances', 'player_name', 'kill_fame'];
        $sort = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'kills';
        $dir  = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        
        $query->orderBy($sort, $dir);

        return $query->paginate($this->perPagePlayers, ['*'], 'playersPage');
    }

    // ==================================================================
    //                      ENVIAR A DISCORD
    // ==================================================================

    public function sendToDiscord(DiscordMessageService $discord): void
    {
        $this->sendingToDiscord = true;
        $this->discordMessage = null;
        $this->discordError = null;

        $channelId = config('services.discord.battle_report_channel');

        if (empty($channelId)) {
            $this->discordError = 'El canal de Discord para reportes no está configurado en el .env.';
            $this->sendingToDiscord = false;
            return;
        }

        try {
            // 1. Obtener el Top 3 de jugadores por Kills (independiente de los filtros de la tabla)
            $topPlayers = BattleParticipant::whereIn('battle_id', $this->ids)
                ->selectRaw('MAX(player_name) as player_name, SUM(kills) as kills, SUM(deaths) as deaths')
                ->groupBy('player_id')
                ->orderByDesc('kills')
                ->limit(3)
                ->get();

            // 2. Formatear strings para el Embed
            $topPlayersText = $topPlayers->map(function ($p, $index) {
                return "**" . ($index + 1) . ". " . $p->player_name . "** — " . $p->kills . " kills (" . $p->deaths . " muertes)";
            })->join("\n");

            $topGuildsText = $this->guildsSummary->take(3)->map(function ($g, $index) {
                return "**" . ($index + 1) . ". " . $g->guild_name . "** — " . number_format($g->total_kills) . " kills";
            })->join("\n");

            $topAlliancesText = $this->alliancesSummary->take(3)->map(function ($a, $index) {
                return "**" . ($index + 1) . ". " . $a->alliance_name . "** — " . number_format($a->total_kills) . " kills";
            })->join("\n");

            // 3. Construir el Embed
            $embed = [
                'title'       => '⚔️ Reporte Multi-Batalla — ' . $this->totals['sessions'] . ' sesiones',
                'description' => "Periodo: **" . $this->totals['date_range'] . "**\n" .
                                 "Tiempo de combate: **" . $this->totals['active_time'] . "**",
                'color'       => 0xC54B47, // Color primary de Linhir
                'fields'      => [
                    [
                        'name'   => '📊 Resumen de Campaña',
                        'value'  => "**Kills Totales:** " . number_format($this->totals['kill_events'], 0, ',', '.') . "\n" .
                                    "**Bajas Únicas:** " . $this->totals['unique_casualties'] . " de " . $this->totals['unique_players'] . " jugadores\n" .
                                    "**K/D Campaña:** " . ($this->totals['kdr'] ?? '0.00') . "\n" .
                                    "**Fama Total:** " . number_format($this->totals['total_fame'], 0, ',', '.'),
                        'inline' => true,
                    ],
                    [
                        'name'   => '🏆 TOP Jugadores (Kills)',
                        'value'  => $topPlayersText ?: 'Sin datos',
                        'inline' => true,
                    ],
                    [
                        'name'   => '🛡️ TOP Gremios',
                        'value'  => $topGuildsText ?: 'Sin datos',
                        'inline' => true,
                    ],
                    [
                        'name'   => '🤝 TOP Alianzas',
                        'value'  => $topAlliancesText ?: 'Sin datos',
                        'inline' => true,
                    ],
                    [
                        'name'   => '🔗 Reportes Concatenados',
                        'value'  => collect($this->ids)->map(fn($id) => "[#{$id}](" . route('battles.show', $id) . ")")->join(' · '),
                        'inline' => false,
                    ]
                ],
                'footer'    => [
                    'text'     => 'Linhir System • Generado desde la web',
                    'icon_url' => asset('imagenes/linhir_escudo_180.png'),
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            $payload = [
                'embeds' => [$embed]
            ];

            // 4. Enviar usando tu servicio existente
            $result = $discord->sendMessage($channelId, $payload);

            if ($result && !empty($result['id'])) {
                $this->discordMessage = '¡Reporte enviado a Discord exitosamente!';
            } else {
                $this->discordError = 'No se pudo enviar el mensaje. Revisa los logs o la configuración del bot.';
            }

        } catch (\Throwable $e) {
            Log::error('Error enviando multi-batalla a Discord', [
                'ids' => $this->ids,
                'error' => $e->getMessage()
            ]);
            $this->discordError = 'Ocurrió un error al enviar: ' . $e->getMessage();
        } finally {
            $this->sendingToDiscord = false;
        }
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
        $this->resetPage('playersPage');
    }

    public function updatingSearchPlayer(): void { $this->resetPage('playersPage'); }
    public function updatingGuildFilter(): void { $this->resetPage('playersPage'); }
    public function updatedPerPagePlayers(): void { $this->resetPage('playersPage'); }

    public function resetFilters(): void
    {
        $this->searchPlayer = '';
        $this->guildFilter  = '';
        $this->sortField    = 'kills';
        $this->sortDirection = 'desc';
        $this->resetPage('playersPage');
    }

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

    public function render()
    {
        return view('livewire.modulo.battles.multi-battle-show', [
            'paginatedPlayers' => $this->players,
            'guildsSummary'    => $this->guildsSummary,
            'alliancesSummary' => $this->alliancesSummary,
            'topKillFameEvent' => KillEvent::whereIn('battle_id', $this->ids)->orderByDesc('killer_kill_fame')->first(),
        ]);
    }
}
