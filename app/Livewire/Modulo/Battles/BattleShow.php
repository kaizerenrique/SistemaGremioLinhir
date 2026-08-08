<?php

namespace App\Livewire\Modulo\Battles;

use Livewire\Component;
use App\Models\Battle;
use App\Models\KillEvent;
use Illuminate\Support\Facades\DB;

class BattleShow extends Component
{
    public $battleId;
    public $battle;
    public $participants;
    public $killEvents;
    public $lostEquipment;
    public $topKills = null;
    public $topHeals = null;
    public $topDamage = null;
    public $topKillFame = null;
    public $guildFilter = 'all';

    protected $queryString = [
        'guildFilter' => ['except' => 'all'],
    ];

    public function mount($id)
    {
        $this->battleId = $id;
        $this->loadData();
    }

    public function updatedGuildFilter()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->battle = Battle::with(['participants.guild', 'participants.alliance'])
            ->findOrFail($this->battleId);

        $participantsQuery = $this->battle->participants()
            ->with(['guild', 'alliance']);

        if ($this->guildFilter !== 'all') {
            $participantsQuery->where('guild_id', $this->guildFilter);
        }

        $this->participants = $participantsQuery->orderBy('kills', 'desc')->get();

        // TOPs
        $this->topKills = $this->participants->sortByDesc('kills')->first();
        $this->topHeals = $this->participants->sortByDesc('support_healing_done')->first();
        $this->topDamage = $this->participants->sortByDesc('damage_done')->first();

        $this->topKillFame = KillEvent::where('battle_id', $this->battleId)
            ->orderBy('killer_kill_fame', 'desc')
            ->first();

        $this->killEvents = KillEvent::where('battle_id', $this->battleId)
            ->with(['killerEquipment.item', 'victimEquipment.item'])
            ->orderBy('timestamp', 'asc')
            ->get();

        // Equipamiento perdido
        $lostItems = [];
        foreach ($this->killEvents as $event) {
            $victimId = $event->victim_id;
            if (!isset($lostItems[$victimId])) {
                $lostItems[$victimId] = [
                    'player_name' => $event->victim_name,
                    'guild_name' => $event->victimGuild ? $event->victimGuild->name : 'Sin gremio',
                    'items' => [],
                ];
            }
            foreach ($event->victimEquipment as $equip) {
                $item = $equip->item;
                $lostItems[$victimId]['items'][] = [
                    'slot' => $equip->slot,
                    'item_type' => $equip->item_type,
                    'quality' => $equip->quality,
                    'item_name' => $item ? $item->getLocalizedName() : $equip->item_type,
                    'image_url' => $item ? $item->image_url : null, // ✅ Ya usa la URL con "/"
                ];
            }
        }
        $this->lostEquipment = collect($lostItems);
    }

    public function render()
    {
        // Alianzas (todas)
        $alliances = $this->battle->participants()
            ->whereNotNull('alliance_id')
            ->select('alliance_id')
            ->with('alliance')
            ->groupBy('alliance_id')
            ->orderBy('alliance_id')
            ->get()
            ->pluck('alliance')
            ->filter();

        // Gremios (todos)
        $guilds = $this->battle->participants()
            ->whereNotNull('guild_id')
            ->select('guild_id')
            ->with('guild')
            ->groupBy('guild_id')
            ->orderBy('guild_id')
            ->get()
            ->pluck('guild')
            ->filter();

        // Estadísticas por gremio (todas)
        $guildStats = $this->battle->participants()
            ->select('guild_id', DB::raw('SUM(kills) as total_kills'), DB::raw('SUM(deaths) as total_deaths'), DB::raw('AVG(average_item_power) as avg_ip'))
            ->whereNotNull('guild_id')
            ->groupBy('guild_id')
            ->with('guild')
            ->orderBy('total_kills', 'desc')
            ->get();

        return view('livewire.modulo.battles.battle-show', [
            'battle' => $this->battle,
            'participants' => $this->participants,
            'alliances' => $alliances,
            'guilds' => $guilds,
            'guildStats' => $guildStats,
            'topKills' => $this->topKills,
            'topHeals' => $this->topHeals,
            'topDamage' => $this->topDamage,
            'topKillFame' => $this->topKillFame,
            'killEvents' => $this->killEvents,
            'lostEquipment' => $this->lostEquipment,
            'guildFilter' => $this->guildFilter,
        ]);
    }
}
