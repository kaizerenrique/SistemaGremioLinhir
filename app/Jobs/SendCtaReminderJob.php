<?php

namespace App\Jobs;

use App\Models\Cta;
use App\Services\Discord\NotificationService;
use App\Services\Guild\CtaService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCtaReminderJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout del Job en segundos. Con 100 participantes × 1.2s = 120s.
     * Damos margen de sobra.
     */
    public int $timeout = 300;

    /**
     * Reintentos: 2 intentos son suficientes. Si falla dos veces,
     * se descarta y el siguiente `ctas:send-reminders` volverá a
     * intentar (porque reminder_sent sigue en false).
     */
    public int $tries = 2;

    /** Backoff entre reintentos (segundos). */
    public int $backoff = 30;

    /**
     * Tiempo máximo que el lock único permanece en caché (segundos).
     * Defensa contra locks atrapados: si el Job falla definitivamente,
     * en 1h el lock expira y la CTA puede reintentar limpiamente.
     */
    public int $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $ctaId)
    {
        //
    }

    /**
     * Clave de unicidad: una sola instancia por CTA en la cola.
     * SIN ESTE MÉTODO, todos los Jobs comparten la misma clave y
     * solo el primero se encola (los demás son rechazados).
     */
    public function uniqueId(): string
    {
        return (string) $this->ctaId;
    }

    /**
     * Execute the job.
     */
    public function handle(CtaService $ctas, NotificationService $discord): void
    {
        $cta = Cta::with('confirmedParticipants')->find($this->ctaId);

        if (!$cta) {
            Log::warning('SendCtaReminderJob: CTA no encontrada', ['cta_id' => $this->ctaId]);
            return;
        }

        // La CTA puede haber sido cancelada o ya enviada entre dispatch y ejecución
        if ($cta->status !== 'scheduled' || $cta->reminder_sent) {
            return;
        }

        $start = Carbon::parse($cta->start_time)->utc();
        $body = "🔔 **Recordatorio de actividad**\n\n"
              . "**{$cta->title}**\n"
              . "📍 {$cta->location}\n"
              . "⏰ Inicia <t:{$start->timestamp}:R>\n\n"
              . "¡No olvides asistir!";

        $sent = 0;
        $failed = 0;

        foreach ($cta->confirmedParticipants as $p) {
            try {
                if ($discord->sendDirectMessage($p->discord_user_id, $body)) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('SendCtaReminderJob: error enviando DM', [
                    'cta_id'          => $cta->id,
                    'discord_user_id' => $p->discord_user_id,
                    'error'           => $e->getMessage(),
                ]);
            }

            // 1.2 s entre DMs, igual que el Linhir Assistant.
            usleep(1_200_000);
        }

        // Marcar como enviado SOLO si el job termina sin excepción no capturada.
        // Si el proceso muere a mitad, quedará sin marcar y se reintentará.
        $ctas->markReminderSent($cta->id);

        Log::info('SendCtaReminderJob: recordatorio enviado', [
            'cta_id' => $cta->id,
            'sent'   => $sent,
            'failed' => $failed,
        ]);
    }
}