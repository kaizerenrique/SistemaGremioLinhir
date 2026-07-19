<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Battle;
use App\Models\Alliance;
use App\Models\Guild;
use App\Models\BattleParticipant;
use App\Models\KillEvent;
use App\Models\KillEventEquipment;
use App\Models\Item;
use Carbon\Carbon;

class ProcessBattle implements ShouldQueue
{
    use Batchable, Queueable, Dispatchable, InteractsWithQueue, SerializesModels; 

    public $battleId;
    public $timeout = 300; // 5 minutos máximo
    public $tries = 3; // Establecer el número máximo de intentos    
    public $backoff = 60; // También puedes definir el tiempo de espera entre reintentos (en segundos)

    /**
     * Create a new job instance.
     */
    public function __construct($battleId)
    {
        $this->battleId = $battleId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Obtener detalle de la batalla
        $detailUrl = "https://gameinfo.albiononline.com/api/gameinfo/battles/{$this->battleId}";
        $response = Http::timeout(60)->get($detailUrl);

        if (!$response->successful()) {
            // Reintentar después de 5 minutos si falla
            $this->release(300);
            return;
        }

        $data = $response->json();
        $this->storeBattle($data);

        // Obtener eventos de la batalla
        $offset = 0;
        $limit = 51;
        do {
            $eventsUrl = "https://gameinfo.albiononline.com/api/gameinfo/events/battle/{$this->battleId}?offset=$offset&limit=$limit";
            $eventsResponse = Http::timeout(60)->get($eventsUrl);

            if (!$eventsResponse->successful()) {
                break;
            }

            $events = $eventsResponse->json();
            if (empty($events)) {
                break;
            }

            foreach ($events as $event) {
                $this->storeKillEvent($event, $this->battleId);
            }

            $offset += $limit;
        } while (count($events) === $limit);
    }

        private function storeBattle(array $data)
    {
        // Guardar alianzas y gremios
        foreach ($data['alliances'] ?? [] as $id => $allianceData) {
            Alliance::updateOrCreate(['id' => $id], ['name' => $allianceData['name']]);
        }
        foreach ($data['guilds'] ?? [] as $id => $guildData) {
            Guild::updateOrCreate(
                ['id' => $id],
                [
                    'name' => $guildData['name'],
                    'alliance_id' => $guildData['allianceId'] ?: null,
                ]
            );
        }

        // Guardar batalla
        Battle::updateOrCreate(
            ['id' => $data['id']],
            [
                'start_time' => Carbon::parse($data['startTime']),
                'end_time' => Carbon::parse($data['endTime']),
                'timeout' => Carbon::parse($data['timeout']),
                'total_fame' => $data['totalFame'],
                'total_kills' => $data['totalKills'],
                'cluster_name' => $data['clusterName'] ?? null,
            ]
        );

        // Guardar participantes (resumen)
        foreach ($data['players'] ?? [] as $playerId => $playerData) {
            BattleParticipant::updateOrCreate(
                [
                    'battle_id' => $data['id'],
                    'player_id' => $playerId,
                ],
                [
                    'player_name' => $playerData['name'],
                    'guild_id' => $playerData['guildId'] ?: null,
                    'alliance_id' => $playerData['allianceId'] ?: null,
                    'kills' => $playerData['kills'],
                    'deaths' => $playerData['deaths'],
                    'kill_fame' => $playerData['killFame'],
                ]
            );
        }
    }

    private function storeKillEvent(array $event, int $battleId)
    {
        // Guardar evento
        $killEvent = KillEvent::updateOrCreate(
            ['id' => $event['EventId']],
            [
                'battle_id' => $battleId,
                'timestamp' => Carbon::parse($event['TimeStamp']),
                'killer_id' => $event['Killer']['Id'],
                'killer_name' => $event['Killer']['Name'],
                'killer_guild_id' => $event['Killer']['GuildId'] ?: null,
                'killer_alliance_id' => $event['Killer']['AllianceId'] ?: null,
                'killer_avg_ip' => $event['Killer']['AverageItemPower'] ?? 0,
                'killer_kill_fame' => $event['Killer']['KillFame'] ?? 0,
                'victim_id' => $event['Victim']['Id'],
                'victim_name' => $event['Victim']['Name'],
                'victim_guild_id' => $event['Victim']['GuildId'] ?: null,
                'victim_alliance_id' => $event['Victim']['AllianceId'] ?: null,
                'victim_avg_ip' => $event['Victim']['AverageItemPower'] ?? 0,
                'number_of_participants' => $event['numberOfParticipants'] ?? 0,
                'group_member_count' => $event['groupMemberCount'] ?? 0,
                'kill_area' => $event['KillArea'] ?? null,
            ]
        );

        // Equipamiento del asesino
        if (isset($event['Killer']['Equipment'])) {
            foreach ($event['Killer']['Equipment'] as $slot => $item) {
                if ($item) {
                    $this->storeEquipment($killEvent->id, 'killer', $slot, $item);
                }
            }
        }

        // Equipamiento de la víctima
        if (isset($event['Victim']['Equipment'])) {
            foreach ($event['Victim']['Equipment'] as $slot => $item) {
                if ($item) {
                    $this->storeEquipment($killEvent->id, 'victim', $slot, $item);
                }
            }
        }

        // Actualizar participantes con daño/curación (desde Participants)
        if (isset($event['Participants'])) {
            foreach ($event['Participants'] as $participant) {
                BattleParticipant::updateOrCreate(
                    [
                        'battle_id' => $battleId,
                        'player_id' => $participant['Id'],
                    ],
                    [
                        'player_name' => $participant['Name'],
                        'guild_id' => $participant['GuildId'] ?: null,
                        'alliance_id' => $participant['AllianceId'] ?: null,
                        'average_item_power' => $participant['AverageItemPower'] ?? 0,
                        'damage_done' => $participant['DamageDone'] ?? 0,
                        'support_healing_done' => $participant['SupportHealingDone'] ?? 0,
                    ]
                );
            }
        }
    }

    private function storeEquipment($killEventId, $role, $slot, $itemData)
    {
        $uniqueName = $itemData['Type'];

        // Obtener o crear ítem (solo si no existe)
        $item = Item::firstOrNew(['unique_name' => $uniqueName]);
        if (!$item->exists) {
            // Consultar datos del ítem a la API
            try {
                $itemResponse = Http::timeout(30)->get("https://gameinfo.albiononline.com/api/gameinfo/items/$uniqueName/data");
                if ($itemResponse->successful()) {
                    $itemInfo = $itemResponse->json();
                    $item->fill([
                        'item_type' => $itemInfo['itemType'] ?? null,
                        'tier' => $itemInfo['tier'] ?? null,
                        'enchantment_level' => $itemInfo['enchantmentLevel'] ?? 0,
                        'sprite_name' => $itemInfo['spriteName'] ?? null,
                        'localized_names' => $itemInfo['localizedNames'] ?? null,
                        'localized_descriptions' => $itemInfo['localizedDescriptions'] ?? null,
                        'image_url' => null,
                        'last_updated' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Si falla, solo guardamos el uniqueName
            }
            $item->save();
        }
        
        // Guardar equipamiento en la tabla relacionada
        KillEventEquipment::create([
            'kill_event_id' => $killEventId,
            'player_role' => $role,
            'slot' => $slot,
            'item_type' => $uniqueName,
            'count' => $itemData['Count'] ?? 1,
            'quality' => $itemData['Quality'] ?? 0,
        ]);
    }
}
