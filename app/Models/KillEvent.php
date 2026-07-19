<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KillEvent extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id', 'battle_id', 'timestamp',
        'killer_id', 'killer_name', 'killer_guild_id', 'killer_alliance_id',
        'killer_avg_ip', 'killer_kill_fame',
        'victim_id', 'victim_name', 'victim_guild_id', 'victim_alliance_id',
        'victim_avg_ip',
        'number_of_participants', 'group_member_count', 'kill_area',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'killer_avg_ip' => 'float',
        'victim_avg_ip' => 'float',
        'killer_kill_fame' => 'integer',
    ];

    public function battle()
    {
        return $this->belongsTo(Battle::class);
    }

    public function equipment()
    {
        return $this->hasMany(KillEventEquipment::class);
    }

    // Obtener equipamiento del asesino
    public function killerEquipment()
    {
        return $this->equipment()->where('player_role', 'killer');
    }

    // Obtener equipamiento de la víctima
    public function victimEquipment()
    {
        return $this->equipment()->where('player_role', 'victim');
    }
}
