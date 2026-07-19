<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alliance extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name'];

    public function guilds()
    {
        return $this->hasMany(Guild::class);
    }

    public function battleParticipants()
    {
        return $this->hasMany(BattleParticipant::class);
    }
}
