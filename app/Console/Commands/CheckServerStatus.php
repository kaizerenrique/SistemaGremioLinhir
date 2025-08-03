<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Traits\Estadodelservidor;

class CheckServerStatus extends Command
{
    use Estadodelservidor;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:server_check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el estado del servidor y envía reporte a Discord';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estado_servidor = $this->guardar_estado();
    }
}
