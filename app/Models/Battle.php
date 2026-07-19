<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Battle extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id', 'start_time', 'end_time', 'timeout',
        'total_fame', 'total_kills', 'cluster_name',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'timeout' => 'datetime',
        'total_fame' => 'integer',
        'total_kills' => 'integer',
    ];

    // Relaciones
    public function participants()
    {
        return $this->hasMany(BattleParticipant::class);
    }

    public function killEvents()
    {
        return $this->hasMany(KillEvent::class);
    }

    // Método para obtener gremios implicados (a través de participantes)
    public function guilds()
    {
        return $this->hasManyThrough(Guild::class, BattleParticipant::class, 'battle_id', 'id', 'id', 'guild_id')
                    ->distinct();
    }

    public function alliances()
    {
        return $this->hasManyThrough(Alliance::class, BattleParticipant::class, 'battle_id', 'id', 'id', 'alliance_id')
                    ->distinct();
    }

    // Top Kills (jugadores)
    public function topKills($limit = 10)
    {
        return $this->participants()
                    ->orderBy('kills', 'desc')
                    ->limit($limit)
                    ->get();
    }

    // Top Damage
    public function topDamage($limit = 10)
    {
        return $this->participants()
                    ->orderBy('damage_done', 'desc')
                    ->limit($limit)
                    ->get();
    }

    // Top Healing
    public function topHealing($limit = 10)
    {
        return $this->participants()
                    ->orderBy('support_healing_done', 'desc')
                    ->limit($limit)
                    ->get();
    }

    // Top Kill Fame (el asesinato que más fama otorgó)
    public function topKillFame()
    {
        return $this->killEvents()
                    ->orderBy('killer_kill_fame', 'desc')
                    ->first();
    }
}
