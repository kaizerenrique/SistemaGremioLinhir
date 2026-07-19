<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KillEventEquipment extends Model
{
    protected $fillable = [
        'kill_event_id', 'player_role', 'slot', 'item_type', 'count', 'quality',
    ];

    public function killEvent()
    {
        return $this->belongsTo(KillEvent::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_type', 'unique_name');
    }
}
