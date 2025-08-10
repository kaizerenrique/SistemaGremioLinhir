<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CheckServerStatus;
use App\Console\Commands\IntegrantesDeLinhir;
use App\Console\Commands\UpdateFamaSemanal;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');




Schedule::command(CheckServerStatus::class)
        ->everyFifteenSeconds()  // Ejecutar cada 15 segundos
        ->between('09:58', '11:10') // Solo en este rango horario UTC '09:58', '11:10'
        ->withoutOverlapping()   // Prevenir ejecuciones simultáneas
        ->onOneServer();         // Para entornos con múltiples servidores

Schedule::command(IntegrantesDeLinhir::class)->hourly();

// Ejecutar cada día a las 03:00 para mantener actualizados los valores
Schedule::command(UpdateFamaSemanal::class)->hourly();

// Ejecución especial los lunes a las 00:05 para capturar inicio de semana
Schedule::command(UpdateFamaSemanal::class)->weeklyOn(1, '00:10');

// Ejecución especial los domingos a las 23:55 para capturar fin de seman
Schedule::command(UpdateFamaSemanal::class)->weeklyOn(0, '23:50');   
