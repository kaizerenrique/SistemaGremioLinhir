<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cta;
use App\Services\Guild\CtaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CtaController extends Controller
{
    public function __construct(private CtaService $service) {}

    /** POST /api/bot/ctas — el bot registra una CTA recién creada en Discord. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'composition_id'        => 'required|exists:compositions,id',
            'title'                 => 'required|string|max:120',
            'location'              => 'required|string|max:120',
            'start_time'            => 'required|date',
            'end_time'              => 'required|date',
            'reminder_minutes'      => 'nullable|integer|min:1|max:120',
            'discord_message_id'    => 'nullable|string',
            'discord_channel_id'    => 'nullable|string',
            'discord_event_id'      => 'nullable|string',
            'discord_role_id'       => 'nullable|string',
            'created_by_discord_id' => 'required|string',
            'created_by_name'       => 'required|string',
        ]);

        try {
            $cta = $this->service->create($data);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'reason'  => collect($e->errors())->flatten()->first(),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'cta_id'  => $cta->id,
        ]);
    }

    /** GET /api/bot/ctas/active */
    public function active(): JsonResponse
    {
        $ctas = Cta::with('composition:id,name')
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->get(['id', 'title', 'location', 'start_time', 'end_time',
                   'composition_id', 'discord_message_id', 'discord_channel_id',
                   'discord_event_id', 'discord_role_id']);

        return response()->json(['success' => true, 'ctas' => $ctas]);
    }

    /** GET /api/bot/ctas/{id} */
    public function show(int $id): JsonResponse
    {
        $cta = Cta::with([
            'composition.weapons',
            'participants.weapon',
        ])->find($id);

        if (!$cta) {
            return response()->json(['success' => false, 'reason' => 'CTA no encontrada.'], 200);
        }

        return response()->json(['success' => true, 'cta' => $cta]);
    }

    /** POST /api/bot/ctas/{id}/join */
    public function join(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'discord_user_id' => 'required|string',
            'user_name'       => 'required|string',
            'weapon_id'       => 'required|integer|exists:composition_weapons,id',
        ]);

        try {
            $p = $this->service->join(
                $id, $data['discord_user_id'], $data['user_name'], $data['weapon_id']
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'reason'  => collect($e->errors())->flatten()->first(),
            ], 200);
        }

        return response()->json(['success' => true, 'participant' => $p]);
    }

    /** POST /api/bot/ctas/{id}/absent */
    public function absent(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'discord_user_id' => 'required|string',
        ]);

        $this->service->markAbsent($id, $data['discord_user_id']);

        return response()->json(['success' => true]);
    }

    /** POST /api/bot/ctas/{id}/cancel */
    public function cancel(int $id): JsonResponse
    {
        $this->service->cancel($id);
        return response()->json(['success' => true]);
    }

    /** GET /api/bot/ctas/pending-reminders */
    public function pendingReminders(): JsonResponse
    {
        $ctas = $this->service->pendingReminders();

        // Añadimos participantes confirmados para que el bot pueda mandarles DM
        $ctas->load('confirmedParticipants:id,cta_id,discord_user_id,user_name');

        return response()->json(['success' => true, 'ctas' => $ctas]);
    }

    /** POST /api/bot/ctas/{id}/reminder-sent */
    public function markReminderSent(int $id): JsonResponse
    {
        $this->service->markReminderSent($id);
        return response()->json(['success' => true]);
    }
}
