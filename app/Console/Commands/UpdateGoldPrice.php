<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Traits\AlbionEconomia;

class UpdateGoldPrice extends Command
{
    use AlbionEconomia;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-gold-price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el precio del oro desde la API externa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->consultarvalordeloro();
        $this->info('Registros del valor del oro');
    }
}
