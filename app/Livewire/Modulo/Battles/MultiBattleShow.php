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

        // ---- 2. Cargar batallas (SIN participantes para evitar sobrecarga) ----
        $this->battles = Battle::whereIn('id', $this->ids)
            ->orderBy('start_time')
            ->get();

        if ($this->battles->count() < 2) {
            abort(404, 'No se encontraron al menos 2 batallas con esos IDs.');
        }

        // ---- 3. Calcular Totales Globales (Rápido con SQL) ----
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

    // ==================================================================
    //                      RESUMEN DE GREMIOS Y ALIANZAS
    // ==================================================================

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

    // ==================================================================
    //                      TABLA DE JUGADORES (SQL PAGINADO)
    // ==================================================================

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

        // Filtros
        if ($this->searchPlayer !== '') {
            $query->having('player_name', 'like', '%' . $this->searchPlayer . '%');
        }
        if ($this->guildFilter !== '') {
            $query->having('guild_id', $this->guildFilter);
        }

        // Ordenamiento
        $allowedSorts = ['kills', 'deaths', 'damage_done', 'healing_done', 'appearances', 'player_name', 'kill_fame'];
        $sort = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'kills';
        $dir  = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        
        $query->orderBy($sort, $dir);

        return $query->paginate($this->perPagePlayers, ['*'], 'playersPage');
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
