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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
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
