<?php

namespace App\Services\Albion;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AllianceLookupService
{
    /**
     * Devuelve los datos de la alianza HRL desde Albion.
     * Usa la variable HRL_ALLIANCE_ID del .env o consulta la alianza del gremio Linhir.
     *
     * Cache: 15 min (los gremios de una alianza cambian poco pero no queremos staleness eterno).
     */
    public function findHrl(): ?array
    {
        $allianceId = config('app.hrl_alliance_id');

        if (empty($allianceId)) {
            // Fallback: consultar la alianza del gremio Linhir
            $allianceId = $this->getLinhirAllianceId();
        }

        if (empty($allianceId)) {
            logger()->warning('AllianceLookupService: no se pudo determinar HRL_ALLIANCE_ID.');
            return null;
        }

        return $this->findById($allianceId);
    }

    public function findById(string $allianceId): ?array
    {
        $allianceId = trim($allianceId);
        if ($allianceId === '') return null;

        return Cache::remember("albion_alliance_{$allianceId}", 900, function () use ($allianceId) {
            try {
                $res = Http::timeout(10)
                    ->get("https://gameinfo.albiononline.com/api/gameinfo/alliances/{$allianceId}");

                if (!$res->successful()) return null;
                $data = $res->json();

                return [
                    'id'          => $data['AllianceId'] ?? null,
                    'name'        => $data['AllianceName'] ?? null,
                    'tag'         => $data['AllianceTag'] ?? null,
                    'founder_id'  => $data['FounderId'] ?? null,
                    'founder_name'=> $data['FounderName'] ?? null,
                    'founded'     => $data['Founded'] ?? null,
                    'num_players' => $data['NumPlayers'] ?? 0,
                    'guilds'      => collect($data['Guilds'] ?? [])->map(fn($g) => [
                        'id'   => $g['Id']   ?? null,
                        'name' => $g['Name'] ?? null,
                    ])->filter(fn($g) => !empty($g['id']))->values()->all(),
                ];
            } catch (\Throwable $e) {
                logger()->warning('AllianceLookupService fallo', [
                    'id' => $allianceId, 'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Fallback: obtiene el AllianceId del gremio Linhir consultando su ficha en Albion.
     */
    private function getLinhirAllianceId(): ?string
    {
        $guildId = config('app.linhir_gremio_id');
        if (empty($guildId)) return null;

        try {
            $res = Http::timeout(10)
                ->get("https://gameinfo.albiononline.com/api/gameinfo/guilds/{$guildId}/data");
            if (!$res->successful()) return null;
            return $res->json()['AllianceId'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
