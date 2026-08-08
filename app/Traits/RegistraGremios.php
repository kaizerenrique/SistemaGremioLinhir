<?php

namespace App\Traits;

use App\Models\Guild;
use App\Models\Alliance;

trait RegistraGremios
{
    protected function registrarGremio($guildId, $guildName, $allianceId = null, $allianceName = null)
    {
        if (empty($guildId)) {
            return null;
        }

        // Registrar alianza si existe
        if (!empty($allianceId) && !empty($allianceName)) {
            Alliance::firstOrCreate(
                ['id' => $allianceId],
                ['name' => $allianceName]
            );
        }

        // Registrar gremio
        $guild = Guild::firstOrCreate(
            ['id' => $guildId],
            [
                'name' => $guildName ?? 'Desconocido',
                'alliance_id' => !empty($allianceId) ? $allianceId : null,
            ]
        );

        return $guild;
    }
}