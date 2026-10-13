<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Discord\NotificationService;
use App\Services\Guild\CtaService;
use Carbon\Carbon;
use App\Jobs\SendCtaReminderJob;



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
    public function handle(CtaService $ctas): int
    {
        $pending = $ctas->pendingReminders();

        if ($pending->isEmpty()) {
            $this->info('Sin recordatorios pendientes.');
            return Command::SUCCESS;
        }

        $dispatched = 0;

        foreach ($pending as $cta) {
            SendCtaReminderJob::dispatch($cta->id);
            $dispatched++;
        }

        $this->info("Encolados {$dispatched} recordatorios.");
        return Command::SUCCESS;
    }
}
