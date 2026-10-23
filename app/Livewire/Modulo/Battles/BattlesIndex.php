<?php

namespace App\Livewire\Modulo\Battles;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Battle;

class BattlesIndex extends Component
{
    
    use WithPagination;

    public $search = '';
    public $sortField = 'start_time';
    public $sortDirection = 'desc';
    public $perPage = 10;

    /** @var string[] IDs (como string) de batallas seleccionadas para multi. */
    public array $selectedBattles = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'start_time'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
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

    /**
     * Selecciona / deselecciona todos los IDs de la página actual.
     * El array $pageIds viene desde la vista (evita duplicar la query).
     */
    public function toggleSelectAll(array $pageIds): void
    {
        if (empty($pageIds)) {
            return;
        }

        $allSelected = empty(array_diff($pageIds, $this->selectedBattles));

        if ($allSelected) {
            $this->selectedBattles = array_values(
                array_diff($this->selectedBattles, $pageIds)
            );
        } else {
            $this->selectedBattles = array_values(
                array_unique(array_merge($this->selectedBattles, $pageIds))
            );
        }
    }

    public function clearSelection(): void
    {
        $this->selectedBattles = [];
    }

    /**
     * Redirige a la vista de concatenación con los IDs seleccionados.
     */
    public function goToMulti()
    {
        if (count($this->selectedBattles) < 2) {
            session()->flash('warning', 'Selecciona al menos 2 reportes para concatenar.');
            return;
        }

        $ids = implode(',', $this->selectedBattles);

        return $this->redirectRoute('battles.multi', ['ids' => $ids]);
    }

    public function render()
    {
        $linhirGuildId = config('app.linhir_gremio_id');

        $battles = Battle::query()
            ->withCount(['participants as linhir_participants' => function ($query) use ($linhirGuildId) {
                $query->where('guild_id', $linhirGuildId);
            }])
            ->when($this->search, function ($query) {
                $query->where('id', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.modulo.battles.battles-index', [
            'battles' => $battles,
            'linhirGuildId' => $linhirGuildId,
        ]);
    }
}
