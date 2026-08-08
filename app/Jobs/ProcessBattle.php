<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Battle;
use App\Models\KillEvent;
use App\Models\BattleParticipant;
use App\Models\KillEventEquipment;
use App\Models\Guild;
use App\Models\Alliance;
use App\Models\Item;
use App\Traits\RegistraGremios;
use Carbon\Carbon;

class ProcessBattle implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RegistraGremios;

    protected $battleId;

    public function __construct($battleId)
    {
        $this->battleId = $battleId;
    }

    public function handle()
    {
        $battleId = $this->battleId;

        // 1. Obtener detalle de la batalla
        $response = Http::timeout(60)->get("https://gameinfo.albiononline.com/api/gameinfo/battles/{$battleId}");
        if (!$response->successful()) {
            $this->fail("No se pudo obtener el detalle de la batalla $battleId");
            return;
        }

        $data = $response->json();
        $this->storeBattle($data);

        // 2. Obtener eventos de la batalla (kills)
        $offset = 0;
        $limit = 51;
        $chunk = [];
        $events = [];
        do {
            $eventsUrl = "https://gameinfo.albiononline.com/api/gameinfo/events/battle/{$battleId}?offset=$offset&limit=$limit";
            $eventsResponse = Http::timeout(90)->retry(2, 5000)->get($eventsUrl);
            if ($eventsResponse->successful()) {
                $chunk = $eventsResponse->json();
                $events = array_merge($events, $chunk);
                $offset += $limit;
            } else {
                break;
            }
        } while (count($chunk) === $limit);

        // 3. Guardar eventos
        foreach ($events as $event) {
            $this->storeKillEvent($event, $battleId);
        }
    }

    private function storeBattle(array $data)
    {
        // 1. Registrar alianzas y gremios desde la lista principal
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

        // 2. Registrar gremios de los jugadores que puedan no estar en la lista principal
        foreach ($data['players'] ?? [] as $playerId => $playerData) {
            if (!empty($playerData['guildId'])) {
                $this->registrarGremio(
                    $playerData['guildId'],
                    $playerData['guildName'] ?? 'Desconocido',
                    $playerData['allianceId'] ?? null,
                    $playerData['allianceName'] ?? null
                );
            }
        }

        // 3. Guardar batalla
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

        // 4. Guardar participantes (jugadores)
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
        // Registrar gremios de asesino y víctima
        $killerGuildId = $event['Killer']['GuildId'] ?? null;
        $victimGuildId = $event['Victim']['GuildId'] ?? null;

        if ($killerGuildId) {
            $this->registrarGremio(
                $killerGuildId,
                $event['Killer']['GuildName'] ?? 'Desconocido',
                $event['Killer']['AllianceId'] ?? null,
                $event['Killer']['AllianceName'] ?? null
            );
        }

        if ($victimGuildId) {
            $this->registrarGremio(
                $victimGuildId,
                $event['Victim']['GuildName'] ?? 'Desconocido',
                $event['Victim']['AllianceId'] ?? null,
                $event['Victim']['AllianceName'] ?? null
            );
        }

        // Guardar evento de asesinato
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
    //    if (isset($event['Killer']['Equipment'])) {
    //        foreach ($event['Killer']['Equipment'] as $slot => $item) {
    //            if ($item) {
    //                $this->storeEquipment($killEvent->id, 'killer', $slot, $item);
    //            }
    //        }
    //    }

        // Equipamiento de la víctima
    //    if (isset($event['Victim']['Equipment'])) {
    //        foreach ($event['Victim']['Equipment'] as $slot => $item) {
    //            if ($item) {
    //                $this->storeEquipment($killEvent->id, 'victim', $slot, $item);
    //            }
    //        }
    //    }

        // Participantes y estadísticas de daño
        if (isset($event['Participants'])) {
            foreach ($event['Participants'] as $participant) {
                // Registrar gremio del participante si no existe
                if (!empty($participant['GuildId'])) {
                    $this->registrarGremio(
                        $participant['GuildId'],
                        $participant['GuildName'] ?? 'Desconocido',
                        $participant['AllianceId'] ?? null,
                        $participant['AllianceName'] ?? null
                    );
                }

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

        // Verificar si el ítem ya existe
        $item = Item::firstOrNew(['unique_name' => $uniqueName]);
        

        if (!$item->exists) {
            // Obtener datos del ítem desde la API
            $response = Http::get("https://gameinfo.albiononline.com/api/gameinfo/items/$uniqueName/data");
            if ($response->successful()) {
                $info = $response->json();
                $item->fill([
                    'item_type' => $info['itemType'] ?? null,
                    'tier' => $info['tier'] ?? null,
                    'enchantment_level' => $info['enchantmentLevel'] ?? 0,
                    'sprite_name' => $info['spriteName'] ?? null,
                    'localized_names' => $info['localizedNames'] ?? null,
                    'localized_descriptions' => $info['localizedDescriptions'] ?? null,
                    'image_url' => null,
                    'last_updated' => now(),
                ]);
                $item->save();
            } else {
                // Si falla, guardamos solo el uniqueName para no perder la referencia
                $item->save();
            }
        }

        // Guardar equipamiento
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