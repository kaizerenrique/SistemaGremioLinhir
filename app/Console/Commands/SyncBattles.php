<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Jobs\ProcessBattle;
use Illuminate\Support\Facades\Bus;

class SyncBattles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-battles {guildId?} {--range=week} {--limit=50} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza batallas del gremio usando colas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $guildId = $this->argument('guildId') ?? config('app.linhir_gremio_id');
        $range = $this->option('range');
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        if (!$guildId) {
            $this->error('No se proporcionó un guildId.');
            return Command::FAILURE;
        }

        $this->info("🔍 Obteniendo batallas del gremio: $guildId");

        $url = "https://gameinfo.albiononline.com/api/gameinfo/battles";
        $response = Http::get($url, [
            'guildId' => $guildId,
            'range' => $range,
            'limit' => $limit,
            'sort' => 'recent',
        ]);

        if (!$response->successful()) {
            $this->error('Error al obtener la lista de batallas.');
            return Command::FAILURE;
        }

        $battles = $response->json();
        if (empty($battles)) {
            $this->warn('No se encontraron batallas.');
            return Command::SUCCESS;
        }

        $this->info("📊 Se encontraron " . count($battles) . " batallas.");

        // Filtrar las batallas ya procesadas (opcional)
        $existingIds = \App\Models\Battle::whereIn('id', collect($battles)->pluck('id'))->pluck('id')->toArray();

        $jobs = [];
        foreach ($battles as $battle) {
            $id = $battle['id'];
            if (!$force && in_array($id, $existingIds)) {
                $this->line("⏩ Batalla $id ya existe, omitiendo (usa --force para reprocesar).");
                continue;
            }
            $jobs[] = new ProcessBattle($id);
        }

        if (empty($jobs)) {
            $this->info('✅ Todas las batallas ya están sincronizadas.');
            return Command::SUCCESS;
        }

        $this->info("⏳ Encolando " . count($jobs) . " trabajos...");

        // Despachar el batch (esto ejecutará los jobs en paralelo)
        Bus::batch($jobs)->dispatch();

        $this->info('✅ Los trabajos han sido encolados. Ejecuta "php artisan queue:work" para procesarlos.');
        return Command::SUCCESS;
    }
}
