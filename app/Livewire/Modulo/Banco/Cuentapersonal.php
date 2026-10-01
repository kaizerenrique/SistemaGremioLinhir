<?php

namespace App\Livewire\Modulo\Banco;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Personaje;

class Cuentapersonal extends Component
{
    use WithPagination;

    public $personaje;
    public $saldo;
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $tipoFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'tipoFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Obtener el personaje del usuario autenticado
        $this->personaje = Personaje::where('user_id', Auth::id())->first();
        
        if ($this->personaje) {
            $this->saldo = $this->personaje->saldoActual();
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipoFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (!$this->personaje) {
            return view('livewire.modulo.banco.cuentapersonal', [
                'transacciones' => [],
                'saldo' => 0,
            ]);
        }

        // Whitelist de campos ordenables (defensa en profundidad)
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
            ->when($this->tipoFilter, function ($q) {
                $q->where('tipo', $this->tipoFilter);
            });

        $transacciones = $query
            ->orderBy($sortField, $sortDirection)
            ->paginate($this->perPage);

        // Actualizar saldo (siempre sobre el total, no sobre el filtro)
        $this->saldo = $this->personaje->saldoActual();

        return view('livewire.modulo.banco.cuentapersonal', [
            'transacciones' => $transacciones,
            'saldo'         => $this->saldo,
        ]);
    }
}
