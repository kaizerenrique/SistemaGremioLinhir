<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AlliedGuild;
use App\Models\Personaje;
use App\Services\Albion\AlbionApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAlliedGuilds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-allied-guilds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los miembros de todos los gremios aliados a la base de datos';

    /**
     * Execute the console command.
     */
    public function handle(AlbionApiService $api)
    {
        $aliados = AlliedGuild::where('active', true)->get();

        if ($aliados->isEmpty()) {
            $this->info('No hay gremios aliados activos para sincronizar.');
            return Command::SUCCESS;
        }

        $this->info("Iniciando sincronización de {$aliados->count()} gremios aliados...");

        foreach ($aliados as $aliado) {
            $this->line("Consultando gremio: {$aliado->guild_name} ({$aliado->guild_id})...");

            try {
                $members = $api->getGuildMembers($aliado->guild_id);

                if ($members === null) {
                    $this->error("  ⚠️ Error al consultar {$aliado->guild_name}. Saltando al siguiente...");
                    continue;
                }

                if (empty($members)) {
                    $this->warn("  ⚠️ El gremio {$aliado->guild_name} no devolvió miembros (posible disolución).");
                    continue;
                }

                $idsActuales = [];
                $now = now();

                DB::transaction(function () use ($members, $aliado, &$idsActuales, $now) {
                    foreach ($members as $m) {
                        $idsActuales[] = $m['Id'];
                        
                        // updateOrCreate protege los datos del usuario (user_id, discord_user_id, etc.)
                        Personaje::updateOrCreate(
                            ['Id_albion' => $m['Id']],
                            [
                                'Name' => $m['Name'],
                                'GuildId' => $aliado->guild_id,
                                'allied_guild_id' => $aliado->id,
                                'miembro' => false, // Aseguramos que NO sea tratado como Linhir
                                'last_verified_at' => $now,
                            ]
                        );
                    }

                    // Detección de Bajas: Los que tenían este allied_guild_id pero ya no vinieron en la lista
                    Personaje::where('allied_guild_id', $aliado->id)
                        ->whereNotIn('Id_albion', $idsActuales)
                        ->update([
                            'allied_guild_id' => null,
                            'GuildId' => null,
                            'last_verified_at' => $now,
                        ]);
                });

                $this->info("  ✅ {$aliado->guild_name}: " . count($members) . " miembros sincronizados.");
                sleep(2); // Pausa de 2s para no saturar la API entre gremios

            } catch (\Exception $e) {
                $this->error("  ❌ Error crítico en {$aliado->guild_name}: " . $e->getMessage());
                Log::error("Fallo sincronizando aliado {$aliado->guild_id}", ['error' => $e->getMessage()]);
            }
        }

        $this->info('Sincronización de aliados finalizada.');
        return Command::SUCCESS;
    }
}
