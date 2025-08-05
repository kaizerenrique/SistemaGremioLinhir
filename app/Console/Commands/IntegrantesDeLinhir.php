<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Traits\Albion;

class IntegrantesDeLinhir extends Command
{
    use Albion;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:integrantes-de-linhir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Integrantes del gremio linhir';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $integrantesdelgremiolinhir = $this->integrantesdelgremiolinhir();
        $datospersonaje = $this->datospersonaje();
    }
}
