<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'alliance_id'];

    public function alliance()
    {
        return $this->belongsTo(Alliance::class);
    }

    public function battleParticipants()
    {
        return $this->hasMany(BattleParticipant::class);
    }
}
