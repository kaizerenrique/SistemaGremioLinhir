<?php

namespace App\Livewire\Modulo\Battles;

use Livewire\Component;
use App\Models\Battle;
use App\Models\KillEvent;
use App\Models\Guild;
use Illuminate\Support\Facades\DB;

class WarAnalysis extends Component
{
    public $startDate;
    public $endDate;
    public $enemyGuildId;

    public $linhirGuildId;
    public $enemyGuild;
    
    public $isAnalyzed = false;
    public $battles = [];
    public $battleIds = [];

    // Estadísticas Globales
    public $scoreboard = [
        'linhir_kills' => 0,
        'enemy_kills' => 0,
        'linhir_fame' => 0,
        'enemy_fame' => 0,
        'linhir_avg_ip' => 0,
        'enemy_avg_ip' => 0,
    ];

    // Listados Top
    public $topLinhirKillers = [];
    public $topEnemyKillers = [];
    public $topLinhirVictims = [];
    public $topEnemyVictims = [];

    public function mount()
    {
        $this->linhirGuildId = config('app.linhir_gremio_id');
        // Por defecto, analizar la última semana
        $this->startDate = now()->subDays(7)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function analyze()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'enemyGuildId' => 'required|string',
        ]);

        $this->enemyGuild = Guild::find($this->enemyGuildId);
        if (!$this->enemyGuild) return;

        // 1. Encontrar batallas donde AMBOS gremios participaron
        $this->battles = Battle::whereBetween('start_time', [
                $this->startDate . ' 00:00:00', 
                $this->endDate . ' 23:59:59'
            ])
            ->whereHas('participants', fn($q) => $q->where('guild_id', $this->linhirGuildId))
            ->whereHas('participants', fn($q) => $q->where('guild_id', $this->enemyGuildId))
            ->orderBy('start_time', 'desc')
            ->get();

        $this->battleIds = $this->battles->pluck('id')->toArray();

        if (empty($this->battleIds)) {
            $this->isAnalyzed = true;
            return; // No hubo enfrentamientos
        }

        // 2. Calcular Scoreboard H2H (Head to Head) usando KillEvents
        $stats = KillEvent::whereIn('battle_id', $this->battleIds)
            ->where(function ($q) {
                // Kills de Linhir al Enemigo
                $q->where(function ($sub) {
                    $sub->where('killer_guild_id', $this->linhirGuildId)
                        ->where('victim_guild_id', $this->enemyGuildId);
                })
                // Kills del Enemigo a Linhir
                ->orWhere(function ($sub) {
                    $sub->where('killer_guild_id', $this->enemyGuildId)
                        ->where('victim_guild_id', $this->linhirGuildId);
                });
            })
            ->selectRaw('
                SUM(CASE WHEN killer_guild_id = ? THEN 1 ELSE 0 END) as linhir_kills,
                SUM(CASE WHEN killer_guild_id = ? THEN 1 ELSE 0 END) as enemy_kills,
                SUM(CASE WHEN killer_guild_id = ? THEN killer_kill_fame ELSE 0 END) as linhir_fame,
                SUM(CASE WHEN killer_guild_id = ? THEN killer_kill_fame ELSE 0 END) as enemy_fame,
                AVG(CASE WHEN killer_guild_id = ? THEN killer_avg_ip ELSE NULL END) as linhir_avg_ip,
                AVG(CASE WHEN killer_guild_id = ? THEN killer_avg_ip ELSE NULL END) as enemy_avg_ip
            ', [
                $this->linhirGuildId, $this->enemyGuildId, 
                $this->linhirGuildId, $this->enemyGuildId,
                $this->linhirGuildId, $this->enemyGuildId
            ])
            ->first();

        $this->scoreboard = [
            'linhir_kills' => (int) $stats->linhir_kills,
            'enemy_kills' => (int) $stats->enemy_kills,
            'linhir_fame' => (int) $stats->linhir_fame,
            'enemy_fame' => (int) $stats->enemy_fame,
            'linhir_avg_ip' => round($stats->linhir_avg_ip ?? 0),
            'enemy_avg_ip' => round($stats->enemy_avg_ip ?? 0),
        ];

        // 3. Top Killers (Linhir matando al Enemigo)
        $this->topLinhirKillers = KillEvent::whereIn('battle_id', $this->battleIds)
            ->where('killer_guild_id', $this->linhirGuildId)
            ->where('victim_guild_id', $this->enemyGuildId)
            ->selectRaw('killer_name, killer_id, COUNT(*) as total, SUM(killer_kill_fame) as fame')
            ->groupBy('killer_id', 'killer_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 4. Top Killers (Enemigo matando a Linhir)
        $this->topEnemyKillers = KillEvent::whereIn('battle_id', $this->battleIds)
            ->where('killer_guild_id', $this->enemyGuildId)
            ->where('victim_guild_id', $this->linhirGuildId)
            ->selectRaw('killer_name, killer_id, COUNT(*) as total, SUM(killer_kill_fame) as fame')
            ->groupBy('killer_id', 'killer_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 5. Top Víctimas (Linhir muriendo)
        $this->topLinhirVictims = KillEvent::whereIn('battle_id', $this->battleIds)
            ->where('killer_guild_id', $this->enemyGuildId)
            ->where('victim_guild_id', $this->linhirGuildId)
            ->selectRaw('victim_name, victim_id, COUNT(*) as total')
            ->groupBy('victim_id', 'victim_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 6. Top Víctimas (Enemigo muriendo)
        $this->topEnemyVictims = KillEvent::whereIn('battle_id', $this->battleIds)
            ->where('killer_guild_id', $this->linhirGuildId)
            ->where('victim_guild_id', $this->enemyGuildId)
            ->selectRaw('victim_name, victim_id, COUNT(*) as total')
            ->groupBy('victim_id', 'victim_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $this->isAnalyzed = true;
    }

    public function render()
    {
        // Obtener gremios para el selector (excluyendo a Linhir)
        $guilds = Guild::where('id', '!=', $this->linhirGuildId)
            ->orderBy('name')
            ->get();

        return view('livewire.modulo.battles.war-analysis', [
            'guilds' => $guilds,
        ]);
    }
}
