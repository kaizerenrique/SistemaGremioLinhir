<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPoint extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'discord_user_id',
        'total_points',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_points' => 'integer',
    ];

    /**
     * Get the personaje (character) associated with this user.
     * (Assumes Personaje model exists and has discord_user_id field).
     */
    public function personaje()
    {
        return $this->hasOne(Personaje::class, 'discord_user_id', 'discord_user_id');
    }

    /**
     * Get the Discord provider info (for nickname) if personaje is not found.
     */
    public function authProvider()
    {
        return $this->hasOne(AuthProvider::class, 'provider_id', 'discord_user_id')
            ->where('provider', 'discord');
    }

    /**
     * Get the display name (character name, or Discord username, or ID).
     */
    public function getDisplayNameAttribute()
    {
        if ($this->relationLoaded('personaje') && $this->personaje) {
            return $this->personaje->Name;
        }

        if ($this->relationLoaded('authProvider') && $this->authProvider) {
            return $this->authProvider->nickname ?? $this->discord_user_id;
        }

        return $this->discord_user_id;
    }
}
