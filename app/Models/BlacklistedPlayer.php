<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistedPlayer extends Model
{
    protected $fillable = [
        'albion_player_id',
        'albion_player_name',
        'discord_user_id',
        'reason',
        'banned_by_name',
        'banned_by_discord_id',
    ];

    /**
     * Verifica si un jugador está en la lista negra (por ID de Albion o Discord).
     */
    public static function isBanned(?string $albionId = null, ?string $discordId = null): bool
    {
        if (!$albionId && !$discordId) return false;

        return self::query()
            ->where(function ($q) use ($albionId, $discordId) {
                if ($albionId) $q->where('albion_player_id', $albionId);
                if ($discordId) $q->orWhere('discord_user_id', $discordId);
            })
            ->exists();
    }
}
