<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Discord\NotificationService;
use App\Services\Guild\CtaService;
use Carbon\Carbon;


class SendCtaReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ctas:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía DMs de recordatorio para CTAs próximas';

    /**
     * Execute the console command.
     */
    public function handle(CtaService $ctas, NotificationService $discord): int
    {
        $pending = $ctas->pendingReminders();

        if ($pending->isEmpty()) {
            $this->info('Sin recordatorios pendientes.');
            return Command::SUCCESS;
        }

        foreach ($pending as $cta) {
            $start = Carbon::parse($cta->start_time)->utc();
            $body = "🔔 **Recordatorio de actividad**\n\n"
                  . "**{$cta->title}**\n"
                  . "📍 {$cta->location}\n"
                  . "⏰ Inicia <t:{$start->timestamp}:R>\n\n"
                  . "¡No olvides asistir!";

            foreach ($cta->confirmedParticipants as $p) {
                $discord->sendDirectMessage($p->discord_user_id, $body);
                usleep(1_200_000); // 1.2s entre DMs, igual que el Linhir Assistant
            }

            $ctas->markReminderSent($cta->id);
            $this->info("Recordatorio enviado para CTA #{$cta->id}");
        }

        return Command::SUCCESS;
    }
}
