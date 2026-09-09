<?php

namespace App\Services\Tasks;

use App\Models\UserPoint;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class PointService
{
    /**
     * Obtener ranking de puntos (top N).
     */
    public function getRanking(int $limit = 10): Collection
    {
        return UserPoint::with('personaje')
            ->orderBy('total_points', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener puntos de un usuario y su posición.
     */
    public function getUserPoints(string $discordUserId): array
    {
        $userPoint = UserPoint::where('discord_user_id', $discordUserId)->first();
        $total = $userPoint ? $userPoint->total_points : 0;

        // Posición (1 = mayor puntaje)
        $position = UserPoint::where('total_points', '>', $total)->count() + 1;

        return [
            'total_points' => $total,
            'position' => $position,
            'discord_user_id' => $discordUserId,
        ];
    }

    /**
     * Añadir puntos manualmente (solo oficiales).
     * @throws \Exception
     */
    public function addPoints(string $discordUserId, int $amount, string $reason, string $performedBy): void
    {
        if ($amount <= 0) {
            throw new \Exception('El monto debe ser positivo.');
        }

        DB::transaction(function () use ($discordUserId, $amount, $reason, $performedBy) {
            $userPoint = UserPoint::firstOrNew(['discord_user_id' => $discordUserId]);
            $userPoint->total_points += $amount;
            $userPoint->save();

            PointTransaction::create([
                'discord_user_id' => $discordUserId,
                'amount' => $amount,
                'reason' => $reason,
                'performed_by_discord_id' => $performedBy,
            ]);
        });
    }

    /**
     * Gastar puntos (canje de recompensas, solo oficiales).
     * @throws \Exception
     */
    public function spendPoints(string $discordUserId, int $amount, string $reason, string $performedBy): void
    {
        if ($amount <= 0) {
            throw new \Exception('El monto debe ser positivo.');
        }

        $userPoint = UserPoint::where('discord_user_id', $discordUserId)->first();
        if (!$userPoint || $userPoint->total_points < $amount) {
            throw new \Exception('Puntos insuficientes.');
        }

        DB::transaction(function () use ($userPoint, $discordUserId, $amount, $reason, $performedBy) {
            $userPoint->total_points -= $amount;
            $userPoint->save();

            PointTransaction::create([
                'discord_user_id' => $discordUserId,
                'amount' => -$amount,
                'reason' => $reason,
                'performed_by_discord_id' => $performedBy,
            ]);
        });
    }

    /**
     * Obtener historial de transacciones de un usuario.
     */
    public function getHistory(string $discordUserId, int $limit = 10): Collection
    {
        return PointTransaction::where('discord_user_id', $discordUserId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener todos los usuarios con puntos (para búsquedas).
     */
    public function getAllUsersWithPoints(): Collection
    {
        return UserPoint::with('personaje')
            ->orderBy('total_points', 'desc')
            ->get();
    }
}