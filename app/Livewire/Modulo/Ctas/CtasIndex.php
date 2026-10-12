<?php

namespace App\Livewire\Modulo\Ctas;

use Livewire\Component;
use App\Models\Cta;
use Livewire\WithPagination;


class CtasIndex extends Component
{
    use WithPagination;

    public $filter = 'scheduled';   // scheduled | all | past
    public $buscar = '';

    protected $queryString = [
        'filter' => ['except' => 'scheduled'],
        'buscar' => ['except' => ''],
    ];

    public function updatingFilter(): void { $this->resetPage(); }
    public function updatingBuscar(): void { $this->resetPage(); }

    public function render()
    {
        $query = Cta::with('composition:id,name')
            ->withCount(['participants as confirmed_count' => fn ($q) => $q->where('status', 'confirmed')]);

        if ($this->filter === 'scheduled') {
            $query->where('status', 'scheduled')->where('start_time', '>', now());
        } elseif ($this->filter === 'past') {
            $query->where('start_time', '<=', now());
        }

        if ($this->buscar) {
            $query->where('title', 'like', "%{$this->buscar}%");
        }

        return view('livewire.modulo.ctas.ctas-index', [
            'ctas' => $query->orderBy('start_time', 'desc')->paginate(15),
        ]);
    }
}
