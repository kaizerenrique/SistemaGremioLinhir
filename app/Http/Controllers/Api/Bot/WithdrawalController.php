<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personaje;
use App\Models\WithdrawalRequest;
use App\Services\Discord\NotificationService;
use App\Services\Guild\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    
    public function __construct(
        private WithdrawalService $service,
        private NotificationService $discord,
    ) {}

    /**
     * POST /api/bot/withdrawals
     * El usuario solicita un retiro de su saldo.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'discord_user_id' => 'required|string',
            'amount'          => 'required|integer|min:1',
        ]);

        $personaje = Personaje::where('discord_user_id', $data['discord_user_id'])->first();

        if (!$personaje) {
            return $this->fail('No tienes un personaje vinculado. Usa /register primero.');
        }

        try {
            $req = $this->service->request($personaje, $data['amount']);
        } catch (ValidationException $e) {
            return $this->fail(collect($e->errors())->flatten()->first());
        }

        return response()->json([
            'success'          => true,
            'request_id'       => $req->id,
            'current_balance'  => $personaje->saldoActual(),
        ]);
    }

    /**
     * GET /api/bot/withdrawals/pending
     */
    public function pending(): JsonResponse
    {
        $requests = WithdrawalRequest::with('personaje:id,Name')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        return response()->json(['success' => true, 'requests' => $requests]);
    }

    /**
     * POST /api/bot/withdrawals/{id}/approve
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'reviewer_discord_id' => 'required|string',
            'reviewer_name'       => 'required|string',
        ]);

        $req = WithdrawalRequest::find($id);
        if (!$req) {
            return $this->fail('Solicitud no encontrada.');
        }

        try {
            $this->service->approve($req, $data['reviewer_discord_id'], $data['reviewer_name']);
        } catch (ValidationException $e) {
            return $this->fail(collect($e->errors())->flatten()->first());
        }

        // Notificar al miembro por DM
        $this->discord->sendDirectMessage(
            $req->discord_user_id,
            "✅ **Retiro aprobado**\nTu solicitud de **{$req->amount}** silver ha sido aprobada."
        );

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/bot/withdrawals/{id}/reject
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'reviewer_discord_id' => 'required|string',
            'reviewer_name'       => 'required|string',
            'reason'              => 'required|string|max:255',
        ]);

        $req = WithdrawalRequest::find($id);
        if (!$req) {
            return $this->fail('Solicitud no encontrada.');
        }

        try {
            $this->service->reject(
                $req, $data['reviewer_discord_id'], $data['reviewer_name'], $data['reason']
            );
        } catch (ValidationException $e) {
            return $this->fail(collect($e->errors())->flatten()->first());
        }

        $this->discord->sendDirectMessage(
            $req->discord_user_id,
            "❌ **Retiro rechazado**\nMotivo: {$data['reason']}"
        );

        return response()->json(['success' => true]);
    }

    private function fail(string $reason): JsonResponse
    {
        return response()->json(['success' => false, 'reason' => $reason], 200);
    }

    /**
     * GET /api/bot/withdrawals/mine?discord_user_id=X&limit=N
     *
     * Historial de solicitudes de retiro de un usuario (más recientes primero).
     */
    public function mine(Request $request): JsonResponse
    {
        $data = $request->validate([
            'discord_user_id' => 'required|string',
            'limit'           => 'nullable|integer|min:1|max:50',
        ]);

        $limit = (int) ($data['limit'] ?? 20);

        $requests = WithdrawalRequest::where('discord_user_id', $data['discord_user_id'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get([
                'id', 'amount', 'status',
                'reviewed_by_name', 'reviewed_at', 'notes',
                'created_at',
            ]);

        return response()->json([
            'success'  => true,
            'requests' => $requests,
        ]);
    }
}
