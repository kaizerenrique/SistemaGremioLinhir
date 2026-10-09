<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personaje;
use App\Traits\Albion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IdentityController extends Controller
{
    use Albion;

    /**
     * Verifica un personaje de Albion y lo vincula a un usuario de Discord.
     *
     * - Linhir solo existe en America (West), no requiere parámetro `server`.
     * - `birthdate` es opcional pero recomendado (para felicitaciones automáticas).
     */
    public function registerCharacter(Request $request): JsonResponse
    {


        $validated = $request->validate([
            'character_name'          => 'required|string|max:50',
            'discord_user_id'         => 'required|string|max:32',
            'discord_username'        => 'nullable|string|max:100',
            'birthdate'               => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'performed_by_discord_id' => 'nullable|string|max:32',
        ]);

        // ============================================================
        // 1. Buscar personaje en la API de Albion
        // ============================================================
        $candidates = $this->buscarpersonajepornombre($validated['character_name']);

        if (empty($candidates)) {
            return $this->rejected(
                "No se encontró el personaje **{$validated['character_name']}** "
                    . 'en el servidor America (West). Verifica el nombre exacto.'
            );
        }

        // 2. Coincidencia exacta (case-insensitive)
        $character = null;
        foreach ($candidates as $candidate) {
            if (strcasecmp($candidate->Name, $validated['character_name']) === 0) {
                $character = $candidate;
                break;
            }
        }

        if (!$character) {
            return $this->rejected(
                "No se encontró coincidencia exacta para **{$validated['character_name']}**. "
                    . 'Verifica mayúsculas y ortografía.'
            );
        }

        // ============================================================
        // 3. ¿Pertenece al gremio Linhir?
        // ============================================================
        $linhirGuildId = config('app.linhir_gremio_id');
        $isMember      = ($character->GuildId ?? null) === $linhirGuildId;

        // ============================================================
        // 4. ¿Ya está registrado por otro Discord user?
        // ============================================================
        $existing = Personaje::where('Id_albion', $character->Id)->first();

        if (
            $existing
            && !empty($existing->discord_user_id)
            && $existing->discord_user_id !== $validated['discord_user_id']
        ) {
            return $this->rejected(
                "El personaje **{$character->Name}** ya está vinculado a otra cuenta de Discord. "
                    . 'Si crees que es un error, contacta a un oficial.'
            );
        }

        // ============================================================
        // 5. Persistir vínculo
        // ============================================================
        try {
            $payload = [
                'Name'            => $character->Name,
                'GuildId'         => $character->GuildId ?? null,
                'miembro'         => $isMember,
                'discord_user_id' => $validated['discord_user_id'],
            ];

            // Solo persistimos birthdate si el usuario lo envió.
            // (No sobreescribimos un cumpleaños ya existente si esta vez lo omitió.)
            if (!empty($validated['birthdate'])) {
                $payload['birthdate'] = $validated['birthdate'];
            }

            Personaje::updateOrCreate(
                ['Id_albion' => $character->Id],
                $payload
            );
        } catch (\Throwable $e) {
            Log::error('Error persistiendo personaje en registerCharacter', [
                'character_id' => $character->Id,
                'error'        => $e->getMessage(),
            ]);
            return $this->rejected('Error interno al guardar el registro. Intenta más tarde.');
        }

        // ============================================================
        // 6. Respuesta
        // ============================================================
        $characterPayload = [
            'name'       => $character->Name,
            'id'         => $character->Id,
            'guild_id'   => $character->GuildId ?? null,
            'guild_name' => $character->GuildName ?? null,
            'birthdate'  => $validated['birthdate'] ?? null,
        ];

        if ($isMember) {
            Log::info('Discord user registró personaje miembro', [
                'discord_user_id' => $validated['discord_user_id'],
                'character'       => $character->Name,
                'has_birthdate'   => !empty($validated['birthdate']),
                'performed_by'    => $validated['performed_by_discord_id'] ?? 'self',
            ]);

            return response()->json([
                'success'   => true,
                'decision'  => 'member',
                'reason'    => "¡Bienvenido al gremio Linhir, {$character->Name}!",
                'character' => $characterPayload,
            ]);
        }

        Log::info('Discord user registró personaje público', [
            'discord_user_id' => $validated['discord_user_id'],
            'character'       => $character->Name,
            'guild'           => $character->GuildName ?? 'sin gremio',
            'performed_by'    => $validated['performed_by_discord_id'] ?? 'self',
        ]);

        return response()->json([
            'success'   => true,
            'decision'  => 'public',
            'reason'    => "El personaje {$character->Name} no es integrante actual del gremio Linhir.",
            'character' => $characterPayload,
        ]);
    }

    private function rejected(string $reason): JsonResponse
    {
        return response()->json([
            'success'  => false,
            'decision' => 'rejected',
            'reason'   => $reason,
        ], 200);
    }

    /**
     * Sincroniza el estado de un personaje ya registrado con Albion.
     *
     * Re-consulta la API de Albion y actualiza:
     *   - GuildId (por si cambió de gremio)
     *   - miembro (true/false según GuildId actual)
     *
     * NO modifica birthdate ni otros campos.
     */
    public function syncRegistration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'discord_user_id' => 'required|string|max:32',
        ]);

        // 1. Buscar personaje vinculado a ese Discord user
        $personaje = Personaje::where('discord_user_id', $validated['discord_user_id'])->first();

        if (!$personaje) {
            return response()->json([
                'success' => false,
                'reason'  => 'Este usuario de Discord no tiene un personaje registrado. '
                    . 'Usa `/register start` primero.',
            ], 200);
        }

        // 2. Re-consultar Albion
        $character = $this->consultarpersonaje($personaje->Id_albion);

        if (!$character || !isset($character->Id)) {
            Log::warning('syncRegistration: personaje no encontrado en Albion', [
                'discord_user_id' => $validated['discord_user_id'],
                'id_albion'       => $personaje->Id_albion,
            ]);
            return response()->json([
                'success' => false,
                'reason'  => 'No se pudo consultar el estado actual del personaje en Albion. '
                    . 'Intenta más tarde.',
            ], 200);
        }

        // 3. Determinar si sigue siendo miembro
        $linhirGuildId = config('app.linhir_gremio_id');
        $isMember      = ($character->GuildId ?? null) === $linhirGuildId;

        // 4. Persistir estado actualizado
        $personaje->update([
            'GuildId' => $character->GuildId ?? null,
            'miembro' => $isMember,
        ]);

        // 5. Log
        Log::info('syncRegistration ejecutado', [
            'discord_user_id' => $validated['discord_user_id'],
            'character'       => $character->Name,
            'is_member'       => $isMember,
            'guild'           => $character->GuildName ?? 'sin gremio',
        ]);

        return response()->json([
            'success'   => true,
            'is_member' => $isMember,
            'character' => [
                'name'       => $character->Name,
                'id'         => $character->Id,
                'guild_id'   => $character->GuildId ?? null,
                'guild_name' => $character->GuildName ?? null,
            ],
        ]);
    }

    /**
     * Devuelve el roster completo del gremio + estado de vinculación con Discord.
     *
     * Usado por el bot para el reporte diario de integridad:
     *   - Personajes del gremio (miembro=true) con su discord_user_id (puede ser null).
     *   - Personajes ex-miembros que aún están vinculados (para detectar retiros).
     */
    public function listMembers(Request $request): JsonResponse
    {
        $linhirGuildId = config('app.linhir_gremio_id');

        // ============================================================
        // 1. Roster completo del gremio (sincronizado por app:integrantes-de-linhir)
        // ============================================================
        $guildMembers = Personaje::where('miembro', true)
            ->orderBy('Name')
            ->get(['Name', 'Id_albion', 'GuildId', 'discord_user_id']);

        // ============================================================
        // 2. Todos los vinculados a Discord (incluye ex-miembros)
        //    Sirve para detectar quién salió del gremio pero sigue vinculado.
        // ============================================================
        $linkedToDiscord = Personaje::whereNotNull('discord_user_id')
            ->orderBy('Name')
            ->get(['Name', 'Id_albion', 'GuildId', 'miembro', 'discord_user_id']);

        // ============================================================
        // 3. Categorizar roster
        // ============================================================
        $guildRegistered   = $guildMembers->filter(fn($p) => !empty($p->discord_user_id));
        $guildUnregistered = $guildMembers->filter(fn($p) => empty($p->discord_user_id));

        return response()->json([
            'success'  => true,
            'guild_id' => $linhirGuildId,

            'totals' => [
                'guild_members'      => $guildMembers->count(),
                'linked_total'       => $linkedToDiscord->count(),
                'guild_registered'   => $guildRegistered->count(),
                'guild_unregistered' => $guildUnregistered->count(),
            ],

            // Integrantes del gremio CON discord_user_id (deberían tener @Linhir)
            'guild_registered' => $guildRegistered->map(fn($p) => [
                'name'            => $p->Name,
                'id_albion'       => $p->Id_albion,
                'discord_user_id' => $p->discord_user_id,
            ])->values(),

            // Integrantes del gremio SIN discord_user_id (deberían registrarse)
            'guild_unregistered' => $guildUnregistered->map(fn($p) => [
                'name'      => $p->Name,
                'id_albion' => $p->Id_albion,
            ])->values(),

            // Todos los vinculados (para detectar retiros: miembro=false + aún con rol)
            'all_linked' => $linkedToDiscord->map(fn($p) => [
                'name'            => $p->Name,
                'id_albion'       => $p->Id_albion,
                'miembro'         => (bool) $p->miembro,
                'discord_user_id' => $p->discord_user_id,
            ])->values(),
        ]);
    }
}
