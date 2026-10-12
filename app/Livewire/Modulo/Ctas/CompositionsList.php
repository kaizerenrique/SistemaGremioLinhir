<?php

namespace App\Livewire\Modulo\Ctas;

use Livewire\Component;
use App\Models\Composition;
use App\Models\CompositionWeapon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class CompositionsList extends Component
{
    use WithPagination;

    public $buscar = '';

    // Modal crear/editar
    public $showModal = false;
    public $editing = false;
    public $compositionId = null;
    public $name = '';
    public $description = '';

    // Armas dinámicas (array de ['name' => '', 'icon' => ''])
    public $weapons = [['name' => '', 'icon' => '']];

    // Confirmación de desactivación
    public $confirmingDeactivate = false;
    public $compositionToDeactivate = null;

    protected $queryString = ['buscar' => ['except' => '']];

    protected $rules = [
        'name'                  => 'required|string|max:120',
        'description'           => 'nullable|string|max:500',
        'weapons'               => 'required|array|min:1',
        'weapons.*.name'        => 'required|string|max:120',
        'weapons.*.icon'        => 'nullable|string|max:64',
    ];

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $compositions = Composition::with('weapons')
            ->where('name', 'like', "%{$this->buscar}%")
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.modulo.ctas.compositions-list', [
            'compositions' => $compositions,
        ]);
    }

    public function createComposition(): void
    {
        $this->resetForm();
        $this->editing = false;
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function editComposition(int $id): void
    {
        $comp = Composition::with('weapons')->findOrFail($id);
        $this->compositionId = $comp->id;
        $this->name = $comp->name;
        $this->description = $comp->description ?? '';
        $this->weapons = $comp->weapons->map(fn ($w) => [
            'name' => $w->weapon_name,
            'icon' => $w->weapon_icon ?? '',
        ])->toArray();
        $this->editing = true;
        $this->resetValidation();
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function addWeaponRow(): void
    {
        $this->weapons[] = ['name' => '', 'icon' => ''];
    }

    public function removeWeaponRow(int $index): void
    {
        unset($this->weapons[$index]);
        $this->weapons = array_values($this->weapons);
        if (empty($this->weapons)) {
            $this->weapons = [['name' => '', 'icon' => '']];
        }
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->editing) {
                $comp = Composition::findOrFail($this->compositionId);
                $comp->update([
                    'name'        => $this->name,
                    'description' => $this->description ?: null,
                ]);

                // Reemplazar armas (borrar y recrear)
                $comp->weapons()->delete();
            } else {
                $comp = Composition::create([
                    'name'        => $this->name,
                    'description' => $this->description ?: null,
                    'active'      => true,
                ]);
            }

            foreach ($this->weapons as $i => $w) {
                CompositionWeapon::create([
                    'composition_id' => $comp->id,
                    'weapon_name'    => $w['name'],
                    'weapon_icon'    => $w['icon'] ?: null,
                    'sort_order'     => $i,
                ]);
            }
        });

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('close-modal');
        $this->dispatch('notify', [
            'message' => $this->editing ? 'Composición actualizada.' : 'Composición creada.',
            'type'    => 'success',
        ]);
    }

    public function confirmDeactivate(int $id): void
    {
        $this->compositionToDeactivate = Composition::findOrFail($id);
        $this->confirmingDeactivate = true;
    }

    public function deactivate(): void
    {
        if ($this->compositionToDeactivate) {
            $this->compositionToDeactivate->update(['active' => false]);
            $this->dispatch('notify', ['message' => 'Composición desactivada.', 'type' => 'success']);
        }
        $this->confirmingDeactivate = false;
        $this->compositionToDeactivate = null;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showModal = false;
        $this->confirmingDeactivate = false;
        $this->dispatch('close-modal');
    }

    private function resetForm(): void
    {
        $this->compositionId = null;
        $this->name = '';
        $this->description = '';
        $this->weapons = [['name' => '', 'icon' => '']];
        $this->editing = false;
        $this->resetValidation();
    }
}
