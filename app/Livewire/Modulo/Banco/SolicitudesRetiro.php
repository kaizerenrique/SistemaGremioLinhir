<?php

namespace App\Livewire\Modulo\Banco;

use Livewire\Component;
use App\Models\WithdrawalRequest;
use App\Services\Discord\NotificationService;
use App\Services\Guild\WithdrawalService;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class SolicitudesRetiro extends Component
{
    use WithPagination;

    public $filter = 'pending';

    // Modal rechazo
    public $showRejectModal = false;
    public $rejectingRequest = null;
    public $rejectReason = '';

    protected $queryString = ['filter' => ['except' => 'pending']];

    public function updatingFilter(): void { $this->resetPage(); }

    public function render()
    {
        return view('livewire.modulo.banco.solicitudes-retiro', [
            'requests' => WithdrawalRequest::with('personaje:id,Name')
                ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
                ->orderBy('created_at', 'desc')
                ->paginate(15),
        ]);
    }

    public function approve(int $id, WithdrawalService $service, NotificationService $discord): void
    {
        $req = WithdrawalRequest::find($id);
        if (!$req) {
            $this->dispatch('notify', ['message' => 'Solicitud no encontrada.', 'type' => 'error']);
            return;
        }

        $user = Auth::user();
        $provider = $user->authProviders()->where('provider', 'discord')->first();

        try {
            $service->approve(
                $req,
                $provider?->provider_id ?? (string) $user->id,
                $user->name
            );

            $discord->sendDirectMessage(
                $req->discord_user_id,
                "✅ **Retiro aprobado**\nTu solicitud de **{$req->amount}** silver fue aprobada."
            );

            $this->dispatch('notify', ['message' => 'Solicitud aprobada.', 'type' => 'success']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', [
                'message' => collect($e->errors())->flatten()->first(),
                'type'    => 'error',
            ]);
        }
    }

    public function openRejectModal(int $id): void
    {
        $this->rejectingRequest = WithdrawalRequest::find($id);
        $this->rejectReason = '';
        $this->resetValidation();
        $this->showRejectModal = true;
        $this->dispatch('open-modal');
    }

    public function reject(WithdrawalService $service, NotificationService $discord): void
    {
        $this->validate([
            'rejectReason' => 'required|string|max:255',
        ]);

        if (!$this->rejectingRequest) {
            return;
        }

        $user = Auth::user();
        $provider = $user->authProviders()->where('provider', 'discord')->first();

        try {
            $service->reject(
                $this->rejectingRequest,
                $provider?->provider_id ?? (string) $user->id,
                $user->name,
                $this->rejectReason
            );

            $discord->sendDirectMessage(
                $this->rejectingRequest->discord_user_id,
                "❌ **Retiro rechazado**\nMotivo: {$this->rejectReason}"
            );

            $this->showRejectModal = false;
            $this->rejectingRequest = null;
            $this->rejectReason = '';
            $this->dispatch('close-modal');
            $this->dispatch('notify', ['message' => 'Solicitud rechazada.', 'type' => 'success']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('notify', [
                'message' => collect($e->errors())->flatten()->first(),
                'type'    => 'error',
            ]);
        }
    }

    public function cancelReject(): void
    {
        $this->showRejectModal = false;
        $this->rejectingRequest = null;
        $this->rejectReason = '';
        $this->dispatch('close-modal');
    }
}
