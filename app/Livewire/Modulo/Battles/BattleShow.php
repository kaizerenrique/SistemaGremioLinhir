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
    public $linhirLosses = []; // Propiedad correcta
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

        // LÓGICA DE EQUIPAMIENTO PERDIDO PARA LINHIR
        $linhirGuildId = config('app.linhir_gremio_id');

        // Filtramos los eventos donde la víctima es de Linhir
        $linhirDeaths = $this->killEvents->where('victim_guild_id', $linhirGuildId);

        $losses = [];
        foreach ($linhirDeaths as $event) {
            $playerName = $event->victim_name;

            if (!isset($losses[$playerName])) {
                $losses[$playerName] = [
                    'name' => $playerName,
                    'deaths' => 0,
                    'items' => []
                ];
            }

            $losses[$playerName]['deaths']++;

            // Agrupar los ítems perdidos
            foreach ($event->victimEquipment as $equip) {
                $itemName = $equip->item ? $equip->item->display_name : $equip->item_type;
                $imageUrl = $equip->item ? $equip->item->image_url : null;

                if (!isset($losses[$playerName]['items'][$itemName])) {
                    $losses[$playerName]['items'][$itemName] = [
                        'count' => 0,
                        'image' => $imageUrl,
                        'tier'  => $equip->item ? $equip->item->tier : 0,
                    ];
                }
                $losses[$playerName]['items'][$itemName]['count'] += $equip->count;
            }
        }

        // Ordenamos por cantidad de muertes (de mayor a menor)
        uasort($losses, fn($a, $b) => $b['deaths'] <=> $a['deaths']);

        // Convertimos a array
        $this->linhirLosses = array_values($losses);
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

        // Estadísticas por gremio
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
            'linhirLosses' => $this->linhirLosses, // 👈 Pasamos la nueva variable a la vista
            'guildFilter' => $this->guildFilter,
        ]);
    }
}
