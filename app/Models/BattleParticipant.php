<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BattleParticipant extends Model
{
    protected $fillable = [
        'battle_id', 'player_id', 'player_name', 'guild_id', 'alliance_id',
        'kills', 'deaths', 'kill_fame', 'average_item_power',
        'damage_done', 'support_healing_done',
    ];

    protected $casts = [
        'average_item_power' => 'float',
        'damage_done' => 'float',
        'support_healing_done' => 'float',
        'kills' => 'integer',
        'deaths' => 'integer',
        'kill_fame' => 'integer',
    ];

    public function battle()
    {
        return $this->belongsTo(Battle::class);
    }

    public function guild()
    {
        return $this->belongsTo(Guild::class);
    }

    public function alliance()
    {
        return $this->belongsTo(Alliance::class);
    }
}
