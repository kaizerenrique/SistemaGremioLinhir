<?php

namespace App\Console\Commands;

use App\Models\Personaje;
use App\Models\EspecialidadSemanal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateEspecialidadesSemanales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-especialidades-semanales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los valores semanales de las especialidades de los miembros';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $semanaInicio = Carbon::now()->startOfWeek();
        $miembros = Personaje::where('miembro', true)
                             ->with('lifetimeStatistics.gatheringStatistics')
                             ->get();

        $this->info("🔍 Procesando {$miembros->count()} miembros...");

        foreach ($miembros as $personaje) {
            $stats = $personaje->lifetimeStatistics;
            if (!$stats) {
                $this->warn("  ⚠️ {$personaje->Name}: No tiene estadísticas de por vida");
                continue;
            }

            // Recolectar valores
            $valores = [
                'Crafting_Total' => $stats->Crafting_Total ?? 0,
                'FishingFame'    => $stats->FishingFame ?? 0,
                'FarmingFame'    => $stats->FarmingFame ?? 0,
            ];

            foreach ($stats->gatheringStatistics as $g) {
                $valores[$g->resource_type] = $g->Total ?? 0;
            }

            $this->info("  ✅ {$personaje->Name} (ID {$personaje->id}) - Tipos: " . implode(', ', array_keys($valores)));

            foreach ($valores as $tipo => $valorActual) {
                $registro = EspecialidadSemanal::where([
                    'personaje_id'  => $personaje->id,
                    'semana_inicio' => $semanaInicio,
                    'tipo'          => $tipo,
                ])->first();

                if ($registro) {
                    $registro->valor_fin = $valorActual;
                    $registro->save();
                } else {
                    EspecialidadSemanal::create([
                        'personaje_id'  => $personaje->id,
                        'semana_inicio' => $semanaInicio,
                        'tipo'          => $tipo,
                        'valor_inicio'  => $valorActual,
                        'valor_fin'     => $valorActual,
                    ]);
                }
            }
        }

        $totalRegistros = EspecialidadSemanal::where('semana_inicio', $semanaInicio)->count();
        $this->info("✅ Total de registros para esta semana: {$totalRegistros}");
        $this->info('✅ Especialidades semanales actualizadas.');
        return Command::SUCCESS;
    }
}
