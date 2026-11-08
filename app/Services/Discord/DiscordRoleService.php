<?php

namespace App\Services\Discord;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordRoleService
{
    protected ?string $botToken;
    protected string $apiBase = 'https://discord.com/api/v10';
    protected int $timeout = 15;

    public function __construct()
    {
        $this->botToken = config('services.discord.bot_token');
    }

    /**
     * Lista TODOS los roles del servidor (cache 5 min).
     * @return array<int, array{id:string,name:string,color:int,position:int}>
     */
    public function listRoles(): array
    {
        if (empty($this->botToken)) return [];

        $guildId = config('services.discord.servidor_discord_linhir');
        if (empty($guildId)) return [];

        return Cache::remember("discord_roles_{$guildId}", 300, function () use ($guildId) {
            try {
                $res = Http::withToken($this->botToken, 'Bot')
                    ->timeout($this->timeout)
                    ->get("{$this->apiBase}/guilds/{$guildId}/roles");

                if (!$res->successful()) {
                    Log::warning('DiscordRoleService: listRoles falló', ['status' => $res->status()]);
                    return [];
                }
                return $res->json();
            } catch (\Throwable $e) {
                Log::error('DiscordRoleService: listRoles excepción', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Busca un rol existente por nombre (case-insensitive).
     */
    public function findRoleByName(string $name): ?array
    {
        $needle = mb_strtolower(trim($name));
        foreach ($this->listRoles() as $role) {
            if (mb_strtolower($role['name'] ?? '') === $needle) {
                return $role;
            }
        }
        return null;
    }

    /**
     * Devuelve el rol existente O lo crea si no existe.
     * Devuelve null si no se pudo (sin permisos, etc.).
     *
     * @param bool $forceCreate fuerza crear aunque exista uno con el mismo nombre
     */
    public function findOrCreateRole(
        string $name,
        string $color = '#9A8667',
        bool $mentionable = false,
        bool $hoist = false,
        bool $forceCreate = false,
    ): ?array {
        if (!$forceCreate) {
            $existing = $this->findRoleByName($name);
            if ($existing) {
                return $existing;
            }
        }
        return $this->createRole($name, $color, $mentionable, $hoist);
    }

    public function createRole(
        string $name,
        string $color = '#9A8667',
        bool $mentionable = false,
        bool $hoist = false,
    ): ?array {
        if (empty($this->botToken)) return null;

        $guildId = config('services.discord.servidor_discord_linhir');
        if (empty($guildId)) return null;

        $colorInt = hexdec(ltrim($color, '#'));

        try {
            $res = Http::withToken($this->botToken, 'Bot')
                ->timeout($this->timeout)
                ->post("{$this->apiBase}/guilds/{$guildId}/roles", [
                    'name'        => $name,
                    'color'       => $colorInt,
                    'hoist'       => $hoist,
                    'mentionable' => $mentionable,
                    'permissions' => '0',
                ]);

            if (!$res->successful()) {
                Log::warning('DiscordRoleService: crear rol falló', [
                    'status' => $res->status(), 'body' => $res->body(),
                ]);
                return null;
            }

            // Invalidar caché para que el próximo listRoles refleje el nuevo rol
            Cache::forget('discord_roles_' . $guildId);
            return $res->json();
        } catch (\Throwable $e) {
            Log::error('DiscordRoleService: excepción', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
