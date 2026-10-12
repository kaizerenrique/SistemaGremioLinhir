<?php

namespace App\Services\Guild;

use App\Models\Cta;
use App\Models\CtaParticipant;
use App\Models\Composition;
use App\Models\CompositionWeapon;
use App\Models\Personaje;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CtaService
{
    /**
     * Crea una CTA. Asume que el bot ya creó el evento y el mensaje de Discord
     * y nos pasa los IDs.
     */
    public function create(array $data): Cta
    {
        $composition = Composition::findOrFail($data['composition_id']);

        $start = Carbon::parse($data['start_time'])->utc();
        $end   = Carbon::parse($data['end_time'])->utc();

        if ($end <= $start) {
            throw ValidationException::withMessages([
                'end_time' => 'La hora de fin debe ser posterior al inicio.',
            ]);
        }

        $reminderMinutes = (int) ($data['reminder_minutes'] ?? 10);
        $reminderAt = $start->copy()->subMinutes($reminderMinutes);

        return Cta::create([
            'composition_id'         => $composition->id,
            'title'                  => $data['title'],
            'location'               => $data['location'],
            'start_time'             => $start,
            'end_time'               => $end,
            'reminder_minutes'       => $reminderMinutes,
            'reminder_at'            => $reminderAt,
            'discord_message_id'     => $data['discord_message_id'] ?? null,
            'discord_channel_id'     => $data['discord_channel_id'] ?? null,
            'discord_event_id'       => $data['discord_event_id'] ?? null,
            'discord_role_id'        => $data['discord_role_id'] ?? null,
            'created_by_discord_id'  => $data['created_by_discord_id'],
            'created_by_name'        => $data['created_by_name'],
            'status'                 => 'scheduled',
        ]);
    }

    /**
     * Une a un usuario a una CTA escogiendo un arma libre.
     * Bloquea la fila para evitar race conditions.
     */
    public function join(int $ctaId, string $discordUserId, string $userName, int $weaponId): CtaParticipant
    {
        return DB::transaction(function () use ($ctaId, $discordUserId, $userName, $weaponId) {
            $cta = Cta::lockForUpdate()->findOrFail($ctaId);

            if ($cta->status !== 'scheduled') {
                throw ValidationException::withMessages([
                    'cta' => 'Esta actividad ya no está activa.',
                ]);
            }

            $weapon = CompositionWeapon::where('composition_id', $cta->composition_id)
                ->findOrFail($weaponId);

            // ¿Arma ya ocupada?
            $ocupada = $cta->confirmedParticipants()
                ->where('composition_weapon_id', $weaponId)
                ->where('discord_user_id', '!=', $discordUserId)
                ->exists();

            if ($ocupada) {
                throw ValidationException::withMessages([
                    'weapon' => 'Esa arma ya está ocupada. Elige otra.',
                ]);
            }

            // Vincular con personaje si existe
            $personajeId = Personaje::where('discord_user_id', $discordUserId)->value('id');

            return CtaParticipant::updateOrCreate(
                ['cta_id' => $ctaId, 'discord_user_id' => $discordUserId],
                [
                    'user_name'              => $userName,
                    'personaje_id'           => $personajeId,
                    'composition_weapon_id'  => $weaponId,
                    'status'                 => 'confirmed',
                    'participation_percent'  => 100,
                ]
            );
        });
    }

    /** Cambia el arma de un participante ya confirmado. */
    public function changeWeapon(int $ctaId, string $discordUserId, int $newWeaponId): CtaParticipant
    {
        return $this->join($ctaId, $discordUserId, '', $newWeaponId);
    }

    /** Marca ausencia (libera el arma). */
    public function markAbsent(int $ctaId, string $discordUserId): void
    {
        CtaParticipant::updateOrCreate(
            ['cta_id' => $ctaId, 'discord_user_id' => $discordUserId],
            [
                'user_name'              => '—',
                'composition_weapon_id'  => null,
                'status'                 => 'absent',
                'participation_percent'  => 0,
            ]
        );
    }

    /** Cancela la CTA (no borra: marca cancelled). */
    public function cancel(int $ctaId): void
    {
        Cta::whereKey($ctaId)->update(['status' => 'cancelled']);
    }

    /** CTAs cuyo recordatorio está listo para enviarse. */
    public function pendingReminders(int $limit = 50): \Illuminate\Support\Collection
    {
        return Cta::where('reminder_sent', false)
            ->where('status', 'scheduled')
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '<=', now())
            ->where('start_time', '>', now())
            ->orderBy('reminder_at')
            ->limit($limit)
            ->get();
    }

    public function markReminderSent(int $ctaId): void
    {
        Cta::whereKey($ctaId)->update(['reminder_sent' => true]);
    }
}
