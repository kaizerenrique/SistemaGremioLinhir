<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Guild\GuildBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class GuildBankController extends Controller
{
    public function __construct(private GuildBankService $bank) {}

    /**
     * GET /api/bot/guild-balance
     */
    public function balance(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'balance' => $this->bank->balance(),
        ]);
    }

    /**
     * POST /api/bot/guild-transactions
     * Registra un movimiento en la cuenta del gremio.
     *
     * Payload:
     * {
     *   "type": "ingreso" | "egreso" | "inicial",
     *   "amount": 1500000,
     *   "reason": "Botín ZvZ",
     *   "image_url": "https://...",  // opcional
     *   "performed_by_discord_id": "...",
     *   "performed_by_name": "..."
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'                    => 'required|in:inicial,ingreso,egreso',
            'amount'                  => 'required|integer|min:1',
            'reason'                  => 'required|string|max:255',
            'image_url'               => 'nullable|url|max:500',
            'performed_by_discord_id' => 'required|string',
            'performed_by_name'       => 'required|string',
        ]);

        try {
            $tx = $this->bank->register($data);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'reason'  => collect($e->errors())->flatten()->first(),
            ], 200);
        }

        return response()->json([
            'success'     => true,
            'transaction' => $tx,
            'new_balance' => $this->bank->balance(),
        ]);
    }

    /**
     * GET /api/bot/guild-transactions?limit=N&offset=M&type=ingreso|egreso|inicial
     *
     * Historial paginado de movimientos de la cuenta del gremio.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'limit'  => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
            'type'   => 'nullable|in:inicial,ingreso,egreso',
        ]);

        $limit  = (int) ($data['limit'] ?? 20);
        $offset = (int) ($data['offset'] ?? 0);

        $query = GuildTransaction::query();
        if (!empty($data['type'])) {
            $query->where('type', $data['type']);
        }

        $transactions = $query
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'success'      => true,
            'balance'      => $this->bank->balance(),
            'transactions' => $transactions,
        ]);
    }
}
