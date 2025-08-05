<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CheckServerStatus;
use App\Console\Commands\IntegrantesDeLinhir;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');




Schedule::command(CheckServerStatus::class)
        ->everyFifteenSeconds()  // Ejecutar cada 15 segundos
        ->between('09:58', '11:10') // Solo en este rango horario UTC '09:58', '11:10'
        ->withoutOverlapping()   // Prevenir ejecuciones simultáneas
        ->onOneServer();         // Para entornos con múltiples servidores

Schedule::command(IntegrantesDeLinhir::class)->hourly();