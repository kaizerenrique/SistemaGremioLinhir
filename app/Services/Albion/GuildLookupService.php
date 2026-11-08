<?php

namespace App\Services\Albion;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GuildLookupService
{
    public function findById(string $guildId): ?array
    {
        $guildId = trim($guildId);
        if ($guildId === '') return null;

        return Cache::remember("albion_guild_{$guildId}", 3600, function () use ($guildId) {
            try {
                $res = Http::timeout(10)
                    ->get("https://gameinfo.albiononline.com/api/gameinfo/guilds/{$guildId}/data");

                if (!$res->successful()) return null;
                $data = $res->json();

                // --- NUEVA VALIDACIÓN ---
                // Si la API devuelve un JSON sin el Id o el Name, lo tratamos como no encontrado
                if (empty($data) || !isset($data['Id']) || !isset($data['Name'])) {
                    return null;
                }

                return [
                    'id'           => $data['Id'],
                    'name'         => $data['Name'],
                    'allianceId'   => $data['AllianceId'] ?? null,
                    'allianceName' => $data['AllianceName'] ?? null,
                    'memberCount'  => $data['MemberCount'] ?? null,
                ];
            } catch (\Throwable $e) {
                logger()->warning('GuildLookupService fallo', [
                    'id' => $guildId,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }
}
