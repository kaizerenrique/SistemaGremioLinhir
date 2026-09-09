<?php

namespace App\Traits;

use App\Models\User;

trait DiscordRoleChecker
{
    /**
     * Verificar si un usuario de Discord tiene el rol de oficial en la web.
     */
    protected function isOfficer(string $discordUserId): bool
    {
        // Buscar el usuario local vinculado al Discord ID
        $user = User::whereHas('authProviders', function ($query) use ($discordUserId) {
            $query->where('provider_id', $discordUserId);
        })->first();

        // Si no existe, o no tiene el permiso, retorna false
        return $user && $user->can('review_reports');
    }
}