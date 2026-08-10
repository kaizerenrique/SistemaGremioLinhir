<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\AuthProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


trait DiscordComan
{

    /**
     * Realiza una prueba de conexión con la API de Discord.
     *
     * @return mixed
     */
    public function pruebadiscord()
    {
        try {
            // Usa el token del bot, no el client_id
            $this->token = config('services.discord.bot_token');

            // Realiza la solicitud GET a la API de Discord
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->token,
            ])->get('https://discord.com/api/v10/guilds/1096530483417993359');

            // Verifica si la solicitud fue exitosa
            if ($response->successful()) {
                // Decodifica la respuesta JSON
                return $response->json();
            } else {
                // Maneja errores de la API (por ejemplo, 401 Unauthorized)
                return [
                    'error' => true,
                    'message' => 'Error en la solicitud: ' . $response->status(),
                    'details' => $response->json(),
                ];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Maneja errores de conexión
            return [
                'error' => true,
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ];
        } catch (Exception $e) {
            // Maneja cualquier otro error
            return [
                'error' => true,
                'message' => 'Error inesperado: ' . $e->getMessage(),
            ];
        }
    }  
    
    /**
     * Obtiene la lista de usuarios del servidor de Discord y compara con los IDs de la base de datos.
     *
     * @param string $guildId
     * @return array
     */
    public function getMembersWithRoles()
    {
        
        try {
            // Token del bot
            $this->token = config('services.discord.bot_token');

            $guildId = config('services.discord.servidor_discord_linhir');

            // Obtener la lista de miembros del servidor
            $membersResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->token,
            ])->get("https://discord.com/api/v10/guilds/{$guildId}/members?limit=1000");

            // Obtener la lista de roles del servidor
            $rolesResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->token,
            ])->get("https://discord.com/api/v10/guilds/{$guildId}/roles");

            // Verificar si las solicitudes fueron exitosas
            if ($membersResponse->successful() && $rolesResponse->successful()) {
                $members = $membersResponse->json();
                $roles = $rolesResponse->json();

                // Mapear roles por ID para facilitar la búsqueda
                $rolesMap = [];
                foreach ($roles as $role) {
                    $rolesMap[$role['id']] = $role['name'];
                }

                // Obtener todos los IDs de Discord almacenados en la base de datos
                $discordUserIds = AuthProvider::where('provider', 'discord')
                    ->pluck('provider_id')
                    ->toArray();

                // Procesar miembros y asignar nombres de roles
                $membersWithRoles = [];
                foreach ($members as $member) {
                    $userRoles = [];
                    foreach ($member['roles'] as $roleId) {
                        if (isset($rolesMap[$roleId])) {
                            $userRoles[] = $rolesMap[$roleId];
                        }
                    }

                    // Verificar si el usuario está en la base de datos
                    $isRegistered = in_array($member['user']['id'], $discordUserIds);

                    // Obtener el apodo (nick) del usuario en el servidor
                    $nickname = $member['nick'] ?? $member['user']['username']; // Usar el nickname si existe, de lo contrario usar el username

                    $membersWithRoles[] = [
                        'user_id' => $member['user']['id'] ?? 'N/A',
                        'username' => $member['user']['username'] ?? 'N/A',
                        'discriminator' => $member['user']['discriminator'] ?? 'N/A',
                        'nickname' => $nickname, // Agregar el apodo del usuario
                        'roles' => $userRoles,
                        'is_registered' => $isRegistered, // Indica si el usuario está registrado en la base de datos
                    ];
                }

                return $membersWithRoles;
            } else {
                // Manejar errores de la API
                return [
                    'error' => true,
                    'message' => 'Error en la solicitud a la API de Discord',
                    'details' => [
                        'members' => $membersResponse->json(),
                        'roles' => $rolesResponse->json(),
                    ],
                ];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Manejar errores de conexión
            return [
                'error' => true,
                'message' => 'Error de conexión: ' . $e->getMessage(),
            ];
        } catch (Exception $e) {
            // Manejar cualquier otro error
            return [
                'error' => true,
                'message' => 'Error inesperado: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica si un usuario está en el servidor de Discord.
     *
     * @param string $discordUserId
     * @param string $guildId
     * @return bool
     */
    public function isUserInDiscordServer($discordUserId)
    {
        try {
            // Token del bot
            $this->token = config('services.discord.bot_token');

            $guildId = config('services.discord.servidor_discord_linhir');

            // Obtener la lista de miembros del servidor
            $membersResponse = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->token,
            ])->get("https://discord.com/api/v10/guilds/{$guildId}/members?limit=1000");

            // Verificar si la solicitud fue exitosa
            if ($membersResponse->successful()) {
                $members = $membersResponse->json();

                // Verificar si el usuario está en la lista de miembros
                foreach ($members as $member) {
                    if ($member['user']['id'] === $discordUserId) {
                        return true; // El usuario está en el servidor
                    }
                }

                return false; // El usuario no está en el servidor
            } else {
                // Manejar errores de la API
                throw new Exception('Error en la solicitud a la API de Discord: ' . $membersResponse->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Manejar errores de conexión
            throw new Exception('Error de conexión: ' . $e->getMessage());
        } catch (Exception $e) {
            // Manejar cualquier otro error
            throw new Exception('Error inesperado: ' . $e->getMessage());
        }
    }

    /**
     * Verifica si un el usuario está en el servidor de Discord.     
     */
    public function checkDiscordMembership()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        if (!$user) {
            return false; // No hay usuario autenticado
        }

        // Obtener el proveedor de Discord del usuario
        $discordProvider = $user->authProviders()
            ->where('provider', 'discord') // Asegúrate de que el campo sea 'provider' y no 'provider_name'
            ->first();

        if (!$discordProvider) {
            return false; // El usuario no tiene un proveedor de Discord vinculado
        }

        // Obtener el ID del servidor de Discord desde .env
        $guildId = config('services.discord.servidor_discord_linhir'); // Asegúrate de que este sea el ID del servidor

        if (!$guildId) {
            return false; // No se ha configurado el ID del servidor
        }

        // Verificar si el usuario está en el servidor de Discord
        try {
            return $this->isUserInDiscordServer($discordProvider->provider_id, $guildId);
        } catch (Exception $e) {
            // Manejar errores (opcional)
            return false;
        }
    }

    /**
     * Obtiene información general del servidor (nombre, icono, región, etc.)
     * Cache: 1 hora.
     */
    public function getGuildInfo()
    {
        $guildId = config('services.discord.servidor_discord_linhir');
        $cacheKey = "discord_guild_info_{$guildId}";
        return Cache::remember($cacheKey, 3600, function () use ($guildId) {
            $token = config('services.discord.bot_token');
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $token,
            ])->get("https://discord.com/api/v10/guilds/{$guildId}");

            if ($response->successful()) {
                return $response->json();
            }
            return null;
        });
    }

    /**
     * Obtiene los canales del servidor (texto, voz, categorías).
     * Cache: 1 hora.
     */
    public function getGuildChannels()
    {
        $guildId = config('services.discord.servidor_discord_linhir');
        $cacheKey = "discord_guild_channels_{$guildId}";
        return Cache::remember($cacheKey, 3600, function () use ($guildId) {
            $token = config('services.discord.bot_token');
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $token,
            ])->get("https://discord.com/api/v10/guilds/{$guildId}/channels");

            if ($response->successful()) {
                return $response->json();
            }
            return [];
        });
    }

    /**
     * Obtiene los roles del servidor.
     * Cache: 1 hora.
     */
    public function getGuildRoles()
    {
        $guildId = config('services.discord.servidor_discord_linhir');
        $cacheKey = "discord_guild_roles_{$guildId}";
        return Cache::remember($cacheKey, 3600, function () use ($guildId) {
            $token = config('services.discord.bot_token');
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $token,
            ])->get("https://discord.com/api/v10/guilds/{$guildId}/roles");

            if ($response->successful()) {
                return $response->json();
            }
            return [];
        });
    }

    /**
     * Obtiene una página de miembros (hasta 1000).
     * @param int $limit (máx 1000)
     * @param string|null $after (ID de usuario para paginación)
     * @return array ['members' => [...], 'next' => $nextUserId]
     */
    private function getGuildMembersPage($limit = 100, $after = null)
    {
        $token = config('services.discord.bot_token');
        $guildId = config('services.discord.servidor_discord_linhir');
        $query = http_build_query([
            'limit' => min($limit, 1000),
            'after' => $after,
        ]);
        $response = Http::withHeaders([
            'Authorization' => 'Bot ' . $token,
        ])->get("https://discord.com/api/v10/guilds/{$guildId}/members?{$query}");

        if ($response->successful()) {
            $members = $response->json();
            $next = null;
            if (count($members) === $limit) {
                $last = end($members);
                $next = $last['user']['id'] ?? null;
            }
            // Procesar cada miembro
            $registeredIds = AuthProvider::where('provider', 'discord')->pluck('provider_id')->toArray();
            $processed = array_map(function ($member) use ($registeredIds) {
                return [
                    'user_id' => $member['user']['id'],
                    'username' => $member['user']['username'],
                    'discriminator' => $member['user']['discriminator'] ?? '0',
                    'nickname' => $member['nick'] ?? $member['user']['username'],
                    'roles' => $member['roles'],
                    'is_registered' => in_array($member['user']['id'], $registeredIds),
                    'avatar' => $member['user']['avatar'] ?? null,
                ];
            }, $members);
            return [
                'members' => $processed,
                'next' => $next,
            ];
        }
        return ['members' => [], 'next' => null];
    }

    /**
     * Obtiene TODOS los miembros del servidor (recursivo) y los cachea.
     * Cache: 5 minutos.
     */
    public function getGuildAllMembers()
    {
        $guildId = config('services.discord.servidor_discord_linhir');
        $cacheKey = "discord_guild_all_members_{$guildId}";
        return Cache::remember($cacheKey, 300, function () {
            $all = [];
            $after = null;
            $limit = 100; // tamaño de página
            do {
                $result = $this->getGuildMembersPage($limit, $after);
                $all = array_merge($all, $result['members']);
                $after = $result['next'];
            } while ($after !== null);
            return $all;
        });
    }

    /**
     * Limpia la caché de miembros (útil después de cambios).
     */
    public function clearDiscordCache()
    {
        $guildId = config('services.discord.servidor_discord_linhir');
        Cache::forget("discord_guild_all_members_{$guildId}");
        Cache::forget("discord_guild_info_{$guildId}");
        Cache::forget("discord_guild_channels_{$guildId}");
        Cache::forget("discord_guild_roles_{$guildId}");
    }




    





}