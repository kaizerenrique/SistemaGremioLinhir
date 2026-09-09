<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'discord_user_id',
        'amount',
        'reason',
        'performed_by_discord_id',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // Solo usamos created_at

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the personaje (character) associated with this transaction.
     */
    public function personaje()
    {
        return $this->hasOne(Personaje::class, 'discord_user_id', 'discord_user_id');
    }

    /**
     * Get the display name of the user.
     */
    public function getUserNameAttribute()
    {
        if ($this->relationLoaded('personaje') && $this->personaje) {
            return $this->personaje->Name;
        }
        return $this->discord_user_id;
    }

    /**
     * Get the display name of the performer.
     */
    public function getPerformerNameAttribute()
    {
        $performer = Personaje::where('discord_user_id', $this->performed_by_discord_id)->first();
        return $performer ? $performer->Name : $this->performed_by_discord_id;
    }
}
