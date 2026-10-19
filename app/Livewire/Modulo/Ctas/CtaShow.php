<?php

namespace App\Livewire\Modulo\Ctas;

use Livewire\Component;
use App\Models\Cta;
use App\Models\Personaje;
use App\Services\Discord\DiscordMessageService;
use App\Services\Guild\CtaEmbedBuilder;
use App\Services\Guild\CtaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CtaShow extends Component
{
    
    public int $ctaId;
    public ?Cta $cta = null;

    // Modales
    public bool $showEditBasicModal = false;
    public bool $showCancelModal    = false;
    public bool $showAssignModal    = false;

    // Edit basic fields
    public string $editTitle = '';
    public string $editLocation = '';
    public string $editStartTime = '';
    public string $editEndTime = '';
    public int $editReminderMinutes = 10;

    // Assign weapon
    public ?int $assignWeaponId = null;
    public string $searchPersonaje = '';

    // Feedback
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function mount(int $id): void
    {
        $this->ctaId = $id;
        $this->load();
    }

    public function load(): void
    {
        $this->cta = Cta::with([
            'composition.weapons',
            'participants.weapon',
            'participants.personaje',
        ])->find($this->ctaId);
    }

    public function render()
    {
        // Personajes para el modal de asignación
        $search = trim($this->searchPersonaje);
        $query  = Personaje::where('miembro', true)->whereNotNull('discord_user_id');

        if ($search !== '') {
            $query->where('Name', 'like', "%{$search}%");
        }

        $personajesFiltrados = $query->orderBy('Name')->get(['id', 'Name', 'discord_user_id']);

        return view('livewire.modulo.ctas.cta-show', [
            'cta'                 => $this->cta,
            'ocupacion'           => $this->cta?->weaponOccupancy() ?? [],
            'personajesFiltrados' => $personajesFiltrados,
        ]);
    }

    // ================================================================== //
    //                       EDITAR DATOS BÁSICOS                          //
    // ================================================================== //

    public function openEditBasicModal(): void
    {
        if (!$this->cta) return;

        $this->editTitle           = $this->cta->title;
        $this->editLocation        = $this->cta->location;
        $this->editStartTime       = Carbon::parse($this->cta->start_time)->format('Y-m-d\TH:i');
        $this->editEndTime         = Carbon::parse($this->cta->end_time)->format('Y-m-d\TH:i');
        $this->editReminderMinutes = $this->cta->reminder_minutes;

        $this->resetValidation();
        $this->errorMessage   = null;
        $this->successMessage = null;
        $this->showEditBasicModal = true;
    }

    public function updateBasicInfo(
        CtaService $service,
        DiscordMessageService $discord
    ): void {
        if (!$this->cta) return;

        $this->validate([
            'editTitle'           => 'required|string|max:120',
            'editLocation'        => 'required|string|max:120',
            'editStartTime'       => 'required|date',
            'editEndTime'         => 'required|date|after:editStartTime',
            'editReminderMinutes' => 'required|integer|min:1|max:120',
        ], [
            'editEndTime.after' => 'La hora de fin debe ser posterior al inicio.',
        ]);

        try {
            $this->cta = $service->update($this->cta, [
                'title'            => $this->editTitle,
                'location'         => $this->editLocation,
                'start_time'       => $this->editStartTime,
                'end_time'         => $this->editEndTime,
                'reminder_minutes' => (int) $this->editReminderMinutes,
            ]);

            $this->syncToDiscordSilently($discord);

            $this->showEditBasicModal = false;
            $this->successMessage = 'CTA actualizada correctamente.';
            $this->load();
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first();
        } catch (\Throwable $e) {
            Log::error('CtaShow::updateBasicInfo', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Error al actualizar: ' . $e->getMessage();
        }
    }

    // ================================================================== //
    //                          ASIGNAR ARMA                               //
    // ================================================================== //

    public function openAssignModal(int $weaponId): void
    {
        $this->assignWeaponId   = $weaponId;
        $this->searchPersonaje  = '';
        $this->errorMessage     = null;
        $this->successMessage   = null;
        $this->showAssignModal  = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assignWeaponId  = null;
        $this->searchPersonaje = '';
    }

    public function assignPersonaje(
        int $personajeId,
        CtaService $service,
        DiscordMessageService $discord
    ): void {
        if (!$this->cta || !$this->assignWeaponId) return;

        try {
            $service->assignWeapon(
                $this->cta,
                $this->assignWeaponId,
                $personajeId,
                Auth::user()->name,
            );

            $this->syncToDiscordSilently($discord);

            $this->successMessage = 'Arma asignada correctamente.';
            $this->closeAssignModal();
            $this->load();
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first();
        } catch (\Throwable $e) {
            Log::error('CtaShow::assignPersonaje', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Error al asignar: ' . $e->getMessage();
        }
    }

    public function unassignWeapon(int $weaponId, CtaService $service, DiscordMessageService $discord): void
    {
        if (!$this->cta) return;

        try {
            $service->unassignWeapon($this->cta, $weaponId);
            $this->syncToDiscordSilently($discord);

            $this->successMessage = 'Arma liberada.';
            $this->load();
        } catch (\Throwable $e) {
            Log::error('CtaShow::unassignWeapon', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Error al liberar: ' . $e->getMessage();
        }
    }

    // ================================================================== //
    //                          CANCELAR CTA                               //
    // ================================================================== //

    public function openCancelModal(): void
    {
        $this->errorMessage   = null;
        $this->successMessage = null;
        $this->showCancelModal = true;
    }

    public function confirmCancel(DiscordMessageService $discord): void
    {
        if (!$this->cta) return;

        if (!in_array($this->cta->status, ['scheduled'])) {
            $this->errorMessage = 'Esta CTA no se puede cancelar en su estado actual.';
            $this->showCancelModal = false;
            return;
        }

        try {
            $this->cta->update(['status' => 'cancelled']);
            $this->syncToDiscordSilently($discord);

            $this->successMessage = 'CTA cancelada.';
            $this->showCancelModal = false;
            $this->load();
        } catch (\Throwable $e) {
            Log::error('CtaShow::confirmCancel', ['error' => $e->getMessage()]);
            $this->errorMessage = 'Error al cancelar: ' . $e->getMessage();
        }
    }

    // ================================================================== //
    //                     SINCRONIZAR MANUALMENTE                         //
    // ================================================================== //

    public function syncToDiscord(DiscordMessageService $discord): void
    {
        $this->errorMessage   = null;
        $this->successMessage = null;

        if (!$this->cta) return;

        if ($this->syncToDiscordSilently($discord, true)) {
            $this->successMessage = 'CTA sincronizada con Discord.';
        } else {
            $this->errorMessage = 'No se pudo sincronizar con Discord. Revisa los logs.';
        }
        $this->load();
    }

    /**
     * Envía el estado actual de la CTA a Discord (edita el mensaje existente,
     * o lo crea si aún no se publicó).
     *
     * @return bool true si Discord quedó sincronizado
     */
    private function syncToDiscordSilently(DiscordMessageService $discord, bool $force = false): bool
    {
        if (!$this->cta) return false;

        $channelId = $this->cta->discord_channel_id ?: config('app.cta_announce_channel_id');
        if (empty($channelId)) {
            Log::warning('CtaShow: no hay canal para sincronizar');
            return false;
        }

        $this->cta->load('composition.weapons', 'participants.weapon');

        $embed     = CtaEmbedBuilder::buildEmbed($this->cta, $this->cta->image_url);
        $buttons   = CtaEmbedBuilder::buildButtons($this->cta);
        $payload   = ['embeds' => [$embed]];

        if (!empty($buttons)) {
            $payload['components'] = $buttons;
        } else {
            $payload['components'] = []; // limpiar botones existentes
        }

        $messageId = $this->cta->discord_message_id;

        if ($messageId) {
            $result = $discord->editMessage($channelId, $messageId, $payload);

            if ($result) return true;

            // Si falla la edición (mensaje borrado?), intentar crear uno nuevo
            Log::info('CtaShow: edición falló, intentando crear mensaje nuevo');
            $force = true;
        }

        if (!$messageId || $force) {
            $result = $discord->sendMessage($channelId, $payload);

            if ($result && !empty($result['id'])) {
                $this->cta->update([
                    'discord_message_id' => $result['id'],
                    'discord_channel_id' => $result['channel_id'] ?? $channelId,
                ]);
                return true;
            }
        }

        return false;
    }
}
