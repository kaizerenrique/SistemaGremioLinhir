<?php

namespace App\Services\Albion;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AlbionApiService
{
    
    protected string $baseUrl = 'https://gameinfo.albiononline.com/api/gameinfo';
    protected int $timeout = 4; // 4 segundos máximo (antes tardaba 13s)
    protected int $circuitBreakerTtl = 120; // 2 minutos bloqueado si falla

    /**
     * Evalúa si la API está caída para no saturar.
     */
    protected function isApiDown(): bool
    {
        return Cache::has('albion_api_down');
    }

    /**
     * Marca la API como caída temporalmente.
     */
    protected function markApiDown(string $endpoint, string $error): void
    {
        Cache::put('albion_api_down', true, $this->circuitBreakerTtl);
        Log::error("Albion API Down (Circuit Breaker Activo)", [
            'endpoint' => $endpoint,
            'error' => $error
        ]);
    }

    /**
     * Consulta un personaje por su ID.
     */
    public function getCharacter(string $id): ?object
    {
        if ($this->isApiDown()) {
            throw new \Exception("La API de Albion está en mantenimiento (Circuit Breaker).");
        }

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout(2)
                ->get("{$this->baseUrl}/players/{$id}");

            if ($response->status() === 502 || $response->status() === 503) {
                $this->markApiDown("/players/{$id}", '502 Bad Gateway');
                return null;
            }

            if ($response->successful()) {
                return $response->object();
            }

            return null;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->markApiDown("/players/{$id}", 'Timeout/Connection Error');
            return null;
        }
    }

    /**
     * Busca un personaje por nombre.
     */
    public function searchCharacter(string $name): ?array
    {
        if ($this->isApiDown()) {
            throw new \Exception("La API de Albion está en mantenimiento (Circuit Breaker).");
        }

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout(2)
                ->get("{$this->baseUrl}/search", ['q' => $name]);

            if ($response->status() === 502 || $response->status() === 503) {
                $this->markApiDown("/search", '502 Bad Gateway');
                return null;
            }

            if ($response->successful()) {
                return $response->json('players') ?? [];
            }

            return null;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->markApiDown("/search", 'Timeout/Connection Error');
            return null;
        }
    }

    /**
     * Obtiene los miembros de un gremio (Usado para sincronización masiva).
     */
    public function getGuildMembers(string $guildId): ?array
    {
        if ($this->isApiDown()) {
            throw new \Exception("La API de Albion está en mantenimiento (Circuit Breaker).");
        }

        try {
            $response = Http::timeout(10) // Darle un poco más de tiempo para listas grandes
                ->connectTimeout(3)
                ->get("{$this->baseUrl}/guilds/{$guildId}/members");

            if ($response->status() === 502 || $response->status() === 503) {
                $this->markApiDown("/guilds/{$guildId}/members", '502 Bad Gateway');
                return null;
            }

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->markApiDown("/guilds/{$guildId}/members", 'Timeout/Connection Error');
            return null;
        }
    }
}
