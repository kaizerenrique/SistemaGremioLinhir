<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CheckServerStatus;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');




Schedule::command(CheckServerStatus::class)
        ->everyFifteenSeconds()  // Ejecutar cada 15 minutos
        ->between('9:00', '10:30') // Solo en este rango horario UTC
        ->withoutOverlapping()   // Prevenir ejecuciones simultáneas
        ->onOneServer();         // Para entornos con múltiples servidores