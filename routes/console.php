<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');




$schedule->command('server:check')
        ->everyFifteenSeconds()  // Ejecutar cada 15 minutos
        ->timeBetween('9:00', '10:30') // Solo en este rango horario UTC
        ->withoutOverlapping()   // Prevenir ejecuciones simultáneas
        ->onOneServer();         // Para entornos con múltiples servidores