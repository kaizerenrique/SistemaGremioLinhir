<?php

namespace App\Livewire\Modulo\Banco;

use Livewire\Component;
use App\Models\GuildTransaction;
use App\Services\Guild\GuildBankService;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class CuentaGremial extends Component
{
    use WithPagination;

    public $filter = '';
    public $showModal = false;
    public $type = 'ingreso';
    public $amount = '';
    public $reason = '';
    public $imageUrl = '';
    public $saldo = 0;

    protected $rules = [
        'type'     => 'required|in:inicial,ingreso,egreso',
        'amount'   => 'required|integer|min:1',
        'reason'   => 'required|string|max:255',
        'imageUrl' => 'nullable|url|max:500',
    ];

    public function mount(): void
    {
        $this->saldo = GuildTransaction::balance();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = GuildTransaction::query();
        if ($this->filter) {
            $query->where('type', $this->filter);
        }

        return view('livewire.modulo.banco.cuenta-gremial', [
            'transactions' => $query->orderBy('created_at', 'desc')->paginate(15),
        ]);
    }

    public function openModal(string $type = 'ingreso'): void
    {
        $this->type = $type;
        $this->reset(['amount', 'reason', 'imageUrl']);
        $this->resetValidation();
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function save(GuildBankService $bank): void
    {
        $this->validate();

        $user = Auth::user();
        $discordProvider = $user->authProviders()->where('provider', 'discord')->first();

        try {
            $bank->register([
                'type'                    => $this->type,
                'amount'                  => (int) $this->amount,
                'reason'                  => $this->reason,
                'image_url'               => $this->imageUrl ?: null,
                'performed_by_discord_id' => $discordProvider?->provider_id ?? (string) $user->id,
                'performed_by_name'       => $user->name,
            ]);

            $this->saldo = GuildTransaction::balance();

            $this->showModal = false;
            $this->reset(['amount', 'reason', 'imageUrl']);
            $this->dispatch('close-modal');
            $this->dispatch('notify', ['message' => 'Movimiento registrado.', 'type' => 'success']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', [
                'message' => collect($e->errors())->flatten()->first(),
                'type'    => 'error',
            ]);
        }
    }

    public function cancel(): void
    {
        $this->showModal = false;
        $this->reset(['amount', 'reason', 'imageUrl']);
        $this->dispatch('close-modal');
    }
}
