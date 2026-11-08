<?php

namespace App\Livewire\Modulo\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BlacklistedPlayer;
use App\Traits\Albion;

class BlacklistIndex extends Component
{
    use WithPagination, Albion;

    public $buscar = '';
    
    // Modal
    public $showModal = false;
    public $albion_player_id = '';
    public $albion_player_name = '';
    public $discord_user_id = '';
    public $reason = '';

    // Confirmación de eliminación (Perdonar)
    public $confirmingDeletion = false;
    public $playerToDeleteId = null;

    protected $queryString = ['buscar' => ['except' => '']];

    public function updatingBuscar() { $this->resetPage(); }

    public function render()
    {
        $players = BlacklistedPlayer::where('albion_player_name', 'like', "%{$this->buscar}%")
            ->orWhere('albion_player_id', 'like', "%{$this->buscar}%")
            ->orWhere('discord_user_id', 'like', "%{$this->buscar}%")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.modulo.admin.blacklist-index', compact('players'));
    }

    public function openModal()
    {
        $this->reset(['albion_player_id', 'albion_player_name', 'discord_user_id', 'reason']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function buscarEnAlbion()
    {
        if (empty($this->albion_player_id)) return;

        $personaje = $this->consultarpersonaje($this->albion_player_id);

        if ($personaje && isset($personaje->Name)) {
            $this->albion_player_name = $personaje->Name;
        } else {
            $this->addError('albion_player_id', 'Personaje no encontrado en Albion.');
        }
    }

    public function save()
    {
        $this->validate([
            'albion_player_id' => 'required|string|max:64|unique:blacklisted_players,albion_player_id',
            'albion_player_name' => 'required|string|max:255',
            'discord_user_id' => 'nullable|string|max:32',
            'reason' => 'required|string|max:1000',
        ]);

        $provider = auth()->user()->authProviders()->where('provider', 'discord')->first();

        BlacklistedPlayer::create([
            'albion_player_id' => $this->albion_player_id,
            'albion_player_name' => $this->albion_player_name,
            'discord_user_id' => $this->discord_user_id ?: null,
            'reason' => $this->reason,
            'banned_by_name' => auth()->user()->name,
            'banned_by_discord_id' => $provider?->provider_id ?? 'WEB',
        ]);

        $this->showModal = false;
        $this->dispatch('notify', ['message' => 'Jugador añadido a la Lista Negra.', 'type' => 'success']);
    }

    public function confirmDelete($id)
    {
        $this->playerToDeleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        $player = BlacklistedPlayer::find($this->playerToDeleteId);
        if ($player) {
            $player->delete();
            $this->dispatch('notify', ['message' => 'Jugador removido de la Lista Negra.', 'type' => 'success']);
        }
        $this->confirmingDeletion = false;
        $this->playerToDeleteId = null;
    }
}
