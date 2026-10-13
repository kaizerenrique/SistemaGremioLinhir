<?php

namespace App\Livewire\Modulo\Actividades;

use Livewire\Component;
use App\Models\Activity;
use App\Models\Personaje;
use App\Services\Guild\ActivityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;

class ActivitiesIndex extends Component
{
    use WithPagination;

    // -------- Filtros de listado --------
    public $buscar = '';
    public $modo = '';
    public $desde = '';
    public $hasta = '';

    protected $queryString = [
        'buscar' => ['except' => ''],
        'modo'   => ['except' => ''],
        'desde'  => ['except' => ''],
        'hasta'  => ['except' => ''],
    ];

    // -------- Modal detalle --------
    public $showDetail = false;
    public ?int $detailId = null;
    /** @var Activity|null Actividad precargada al abrir el modal de detalle. */
    public ?Activity $detailActivity = null;

    // -------- Modal crear --------
    public $showForm = false;

    public string $name = '';
    public string $start_time = '';
    public string $end_time = '';
    public int $liquid_silver = 0;
    public int $items_value = 0;
    public bool $pro_guild = false;

    public array $participants = [];
    public ?int $selectedPersonajeId = null;

    public $personajesDisponibles = [];

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:120',
            'start_time'       => 'required|date',
            'end_time'         => 'required|date|after:start_time',
            'liquid_silver'    => 'required|integer|min:0',
            'items_value'      => 'required|integer|min:0',
            'pro_guild'        => 'boolean',
            'participants'     => $this->pro_guild ? 'array' : 'required|array|min:1',
            'participants.*.percent' => 'nullable|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'participants.required' => 'Debes añadir al menos un participante.',
            'participants.min'      => 'Debes añadir al menos un participante.',
        ];
    }

    public function mount(): void
    {
        $this->personajesDisponibles = Personaje::where('miembro', true)
            ->whereNotNull('discord_user_id')
            ->orderBy('Name')
            ->get(['id', 'Name', 'discord_user_id']);
    }

    public function updatingBuscar(): void { $this->resetPage(); }
    public function updatingModo(): void   { $this->resetPage(); }
    public function updatingDesde(): void  { $this->resetPage(); }
    public function updatingHasta(): void  { $this->resetPage(); }

    public function render()
    {
        $query = Activity::with('cta:id,title')
            ->withCount('participants');

        if ($this->buscar) {
            $query->where('name', 'like', "%{$this->buscar}%");
        }
        if ($this->modo === 'pro_guild') {
            $query->where('pro_guild', true);
        } elseif ($this->modo === 'normal') {
            $query->where('pro_guild', false);
        }
        if ($this->desde) {
            $query->where('start_time', '>=', $this->desde . ' 00:00:00');
        }
        if ($this->hasta) {
            $query->where('start_time', '<=', $this->hasta . ' 23:59:59');
        }

        return view('livewire.modulo.actividades.activities-index', [
            'activities' => $query->orderBy('start_time', 'desc')->paginate(15),
        ]);
    }

    // ============================================================
    //                       MODAL DETALLE
    // ============================================================
    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        $this->detailActivity = Activity::with('participants')->find($id);
        $this->showDetail = true;
        $this->dispatch('open-modal');
    }

    public function closeDetail(): void
    {
        $this->showDetail = false;
        $this->detailId = null;
        $this->detailActivity = null;
        $this->dispatch('close-modal');
    }

    // ============================================================
    //                       MODAL CREAR
    // ============================================================
    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->dispatch('open-modal');
    }

    public function closeForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('close-modal');
    }

    public function addParticipant(): void
    {
        if (!$this->selectedPersonajeId) {
            return;
        }

        if (collect($this->participants)->pluck('personaje_id')->contains($this->selectedPersonajeId)) {
            $this->dispatch('notify', [
                'message' => 'Ese personaje ya está añadido.',
                'type'    => 'error',
            ]);
            return;
        }

        $this->participants[] = [
            'personaje_id' => $this->selectedPersonajeId,
            'percent'      => 100,
        ];
        $this->selectedPersonajeId = null;
    }

    public function removeParticipant(int $index): void
    {
        unset($this->participants[$index]);
        $this->participants = array_values($this->participants);
    }

    public function save(ActivityService $service): void
    {
        $this->validate();

        $user = Auth::user();
        $discordProvider = $user->authProviders()->where('provider', 'discord')->first();

        $participants = [];
        foreach ($this->participants as $row) {
            $personaje = $this->personajesDisponibles->firstWhere('id', $row['personaje_id']);
            if (!$personaje) {
                continue;
            }
            $participants[] = [
                'discord_user_id' => $personaje->discord_user_id,
                'name'            => $personaje->Name,
                'percent'         => (float) $row['percent'],
            ];
        }

        try {
            $service->create([
                'name'                     => $this->name,
                'start_time'               => $this->start_time,
                'end_time'                 => $this->end_time,
                'liquid_silver'            => $this->liquid_silver,
                'items_value'              => $this->items_value,
                'pro_guild'                => $this->pro_guild,
                'participants'             => $participants,
                'registered_by_discord_id' => $discordProvider?->provider_id ?? (string) $user->id,
                'registered_by_name'       => $user->name,
            ]);

            $this->closeForm();
            $this->dispatch('notify', ['message' => 'Actividad registrada.', 'type' => 'success']);
        } catch (ValidationException $e) {
            $this->dispatch('notify', [
                'message' => collect($e->errors())->flatten()->first(),
                'type'    => 'error',
            ]);
        }
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->liquid_silver = 0;
        $this->items_value = 0;
        $this->pro_guild = false;
        $this->participants = [];
        $this->selectedPersonajeId = null;
        $this->resetValidation();
    }
}
