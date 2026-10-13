<?php

namespace App\Livewire\Modulo\Banco;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Personaje;

class Cuentapersonal extends Component
{
    use WithPagination;

    /** @var \Illuminate\Support\Collection Lista de personajes del usuario. */
    public $personajes;

    /** @var int|null Personaje actualmente seleccionado. */
    public ?int $personajeActivoId = null;

    /** @var Personaje|null Personaje cargado. */
    public $personaje;

    public $saldo = 0;
    public $ingresos = 0;
    public $egresos = 0;

    // Filtros y paginación
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $tipoFilter = '';

    protected $queryString = [
        'search'      => ['except' => ''],
        'perPage'     => ['except' => 10],
        'tipoFilter'  => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->personajes = Personaje::where('user_id', Auth::id())
            ->orderByDesc('miembro')     // miembros del gremio primero
            ->orderBy('Name')
            ->get(['id', 'Name', 'miembro']);

        if ($this->personajes->isNotEmpty()) {
            $this->personajeActivoId = $this->personajes->first()->id;
            $this->loadPersonaje();
        }
    }

    public function updatedPersonajeActivoId(): void
    {
        $this->loadPersonaje();
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingTipoFilter(): void { $this->resetPage(); }
    public function updatedPerPage(): void     { $this->resetPage(); }

    private function loadPersonaje(): void
    {
        if (!$this->personajeActivoId) {
            $this->personaje = null;
            $this->saldo = 0;
            $this->ingresos = 0;
            $this->egresos = 0;
            return;
        }

        $this->personaje = Personaje::where('id', $this->personajeActivoId)
            ->where('user_id', Auth::id())  // defensa: no permitir consultar otros
            ->withSum(
                ['movimientosBancarios as ingresos_sum' => fn ($q) => $q->where('tipo', 'ingreso')],
                'monto'
            )
            ->withSum(
                ['movimientosBancarios as egresos_sum' => fn ($q) => $q->where('tipo', 'egreso')],
                'monto'
            )
            ->first();

        if ($this->personaje) {
            $this->ingresos = (int) ($this->personaje->ingresos_sum ?? 0);
            $this->egresos  = (int) ($this->personaje->egresos_sum ?? 0);
            $this->saldo    = $this->ingresos - $this->egresos;
        } else {
            $this->saldo = 0;
            $this->ingresos = 0;
            $this->egresos = 0;
        }
    }

    public function render()
    {
        if (!$this->personaje) {
            return view('livewire.modulo.banco.cuentapersonal', [
                'transacciones' => null,
            ]);
        }

        // Whitelist de campos ordenables
        $allowedSortFields = ['created_at', 'tipo', 'monto', 'concepto'];
        $sortField = in_array($this->sortField, $allowedSortFields, true)
            ? $this->sortField
            : 'created_at';
        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $query = $this->personaje->movimientosBancarios()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('concepto', 'like', $term)
                        ->orWhere('referencia', 'like', $term)
                        ->orWhere('autorizado_por', 'like', $term);
                });
            })
            ->when($this->tipoFilter, fn ($q) => $q->where('tipo', $this->tipoFilter));

        $transacciones = $query
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);

        return view('livewire.modulo.banco.cuentapersonal', [
            'transacciones' => $transacciones,
        ]);
    }
}
