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
            'character_name'   => 'required|string|max:50',
            'discord_user_id'  => 'required|string|max:32',
            'discord_username' => 'nullable|string|max:100',
            'birthdate'        => 'nullable|date_format:Y-m-d|before_or_equal:today',
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

        if ($existing
            && !empty($existing->discord_user_id)
            && $existing->discord_user_id !== $validated['discord_user_id']) {
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
}
