<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Traits\Albion;

class UpdateFamaSemanal extends Command
{
    use Albion;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fama_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los registros de fama semanal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->actualizarFamaSemanal();
        $this->info('Registros de fama semanal actualizados correctamente');
    }
}
