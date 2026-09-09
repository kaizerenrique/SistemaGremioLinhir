<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tasks\PointService;
use App\Traits\DiscordRoleChecker;


class PointController extends Controller
{
    use DiscordRoleChecker;

    protected PointService $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * Obtener ranking de puntos.
     */
    public function ranking(Request $request)
    {
        $limit = $request->get('limit', 10);
        $ranking = $this->pointService->getRanking($limit);

        // Agregar nombre del personaje
        $ranking->each(function ($item) {
            $item->personaje_name = $item->personaje->Name ?? $item->discord_user_id;
        });

        return response()->json($ranking);
    }

    /**
     * Obtener puntos de un usuario específico.
     */
    public function show(string $discordUserId)
    {
        $data = $this->pointService->getUserPoints($discordUserId);

        // Agregar nombre del personaje (si existe)
        $personaje = \App\Models\Personaje::where('discord_user_id', $discordUserId)->first();
        $data['personaje_name'] = $personaje->Name ?? $discordUserId;

        return response()->json($data);
    }

    /**
     * Añadir puntos manualmente (solo oficiales).
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'discord_user_id' => 'required|string',
            'amount' => 'required|integer|min:1',
            'reason' => 'required|string',
            'performed_by_discord_id' => 'required|string',
        ]);

        // Verificar permisos del oficial
        if (!$this->isOfficer($validated['performed_by_discord_id'])) {
            return response()->json(['message' => 'Forbidden: No tienes permisos de oficial.'], 403);
        }

        try {
            $this->pointService->addPoints(
                $validated['discord_user_id'],
                $validated['amount'],
                $validated['reason'],
                $validated['performed_by_discord_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Puntos añadidos correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Gastar puntos (canje, solo oficiales).
     */
    public function spend(Request $request)
    {
        $validated = $request->validate([
            'discord_user_id' => 'required|string',
            'amount' => 'required|integer|min:1',
            'reason' => 'required|string',
            'performed_by_discord_id' => 'required|string',
        ]);

        // Verificar permisos del oficial
        if (!$this->isOfficer($validated['performed_by_discord_id'])) {
            return response()->json(['message' => 'Forbidden: No tienes permisos de oficial.'], 403);
        }

        try {
            $this->pointService->spendPoints(
                $validated['discord_user_id'],
                $validated['amount'],
                $validated['reason'],
                $validated['performed_by_discord_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Puntos gastados correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obtener historial de transacciones de un usuario.
     */
    public function history(string $discordUserId, Request $request)
    {
        $limit = $request->get('limit', 10);
        $history = $this->pointService->getHistory($discordUserId, $limit);

        return response()->json($history);
    }

    /**
     * Obtener todos los usuarios con puntos (para búsquedas).
     */
    public function allUsers()
    {
        $users = $this->pointService->getAllUsersWithPoints();

        // Agregar nombre del personaje
        $users->each(function ($item) {
            $item->personaje_name = $item->personaje->Name ?? $item->discord_user_id;
        });

        return response()->json($users);
    }
}
