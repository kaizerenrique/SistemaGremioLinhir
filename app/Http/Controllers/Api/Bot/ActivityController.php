<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Services\Guild\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ActivityController extends Controller
{
    public function __construct(private ActivityService $service) {}

    /**
     * POST /api/bot/activities
     *
     * Registra una actividad financiera y distribuye silver.
     *
     * Modos:
     *   - Normal: requiere `participants` con {discord_user_id, name, percent}
     *   - Pro-gremio: `pro_guild=true`, todo el bruto va a la cuenta común.
     *
     * Si viene `cta_id`, marca la CTA como pagada (evita doble pago).
     *
     * Payload:
     * {
     *   "cta_id": 12,                                // opcional
     *   "name": "Fame Farm",
     *   "start_time": "2026-10-12T18:00:00Z",
     *   "end_time":   "2026-10-12T20:00:00Z",
     *   "liquid_silver": 1000000,
     *   "items_value": 500000,
     *   "pro_guild": false,
     *   "participants": [
     *     { "discord_user_id": "123", "name": "Kaizer", "percent": 60 },
     *     { "discord_user_id": "456", "name": "Dmaro",  "percent": 40 }
     *   ],
     *   "registered_by_discord_id": "999",
     *   "registered_by_name": "Oficial"
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cta_id'                          => 'nullable|exists:ctas,id',
            'name'                            => 'required|string|max:120',
            'start_time'                      => 'required|date',
            'end_time'                        => 'required|date',
            'liquid_silver'                   => 'required|integer|min:0',
            'items_value'                     => 'integer|min:0',
            'pro_guild'                       => 'boolean',
            'participants'                    => 'array',
            'participants.*.discord_user_id'  => 'required_with:participants|string',
            'participants.*.name'             => 'required_with:participants|string',
            'participants.*.percent'          => 'required_with:participants|numeric|min:0',
            'registered_by_discord_id'        => 'required|string',
            'registered_by_name'              => 'required|string',
        ]);

        try {
            $activity = $this->service->create($data);
        } catch (ValidationException $e) {
            return $this->fail(
                collect($e->errors())->flatten()->first()
            );
        }

        return response()->json([
            'success'  => true,
            'activity' => $activity,
        ]);
    }

    /**
     * GET /api/bot/activities/{id}
     *
     * Devuelve el detalle completo con participantes y personajes vinculados.
     */
    public function show(int $id): JsonResponse
    {
        $activity = Activity::with([
            'participants.personaje:id,Name',
            'cta:id,title,start_time',
        ])->find($id);

        if (!$activity) {
            return $this->fail('Actividad no encontrada.');
        }

        return response()->json([
            'success'  => true,
            'activity' => $activity,
        ]);
    }

    /**
     * Respuesta de error estándar (siempre 200 con success=false).
     * El bot distingue errores por el campo `success`, no por el status HTTP.
     */
    private function fail(string $reason): JsonResponse
    {
        return response()->json([
            'success' => false,
            'reason'  => $reason,
        ], 200);
    }

    /**
     * GET /api/bot/activities?limit=N&offset=M
     *
     * Historial paginado de actividades financieras (más recientes primero).
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'limit'  => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        $limit  = (int) ($data['limit'] ?? 20);
        $offset = (int) ($data['offset'] ?? 0);

        $activities = Activity::with([
                'cta:id,title',
                'participants:id,activity_id,user_name,share',
            ])
            ->orderBy('start_time', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'success'    => true,
            'total'      => Activity::count(),
            'limit'      => $limit,
            'offset'     => $offset,
            'activities' => $activities,
        ]);
    }
}
