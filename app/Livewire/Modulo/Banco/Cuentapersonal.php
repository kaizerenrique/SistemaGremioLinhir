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
                'saldo' => 0
            ]);
        }

        $query = $this->personaje->movimientosBancarios()
            ->when($this->search, function ($query) {
                $query->where('concepto', 'like', '%' . $this->search . '%')
                      ->orWhere('referencia', 'like', '%' . $this->search . '%')
                      ->orWhere('autorizado_por', 'like', '%' . $this->search . '%');
            })
            ->when($this->tipoFilter, function ($query) {
                $query->where('tipo', $this->tipoFilter);
            });

        $transacciones = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Actualizar saldo
        $this->saldo = $this->personaje->saldoActual();

        return view('livewire.modulo.banco.cuentapersonal', [
            'transacciones' => $transacciones,
            'saldo' => $this->saldo
        ]);
    }
}
