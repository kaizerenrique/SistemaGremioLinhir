<?php

namespace App\Livewire\Modulo\Ctas;

use Livewire\Component;
use App\Models\Composition;
use App\Models\CompositionWeapon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use App\Traits\DiscordComan;


/**
 * Gestión de composiciones de CTA.
 *
 * Permite crear, editar y desactivar composiciones, incluyendo
 * armas con iconos del servidor de Discord (custom emojis) o
 * emojis Unicode.
 *
 * Los iconos se guardan en formato `<:name:id>` o `<a:name:id>`
 * (custom) o como emoji Unicode directo (🛡️).
 */

class CompositionsList extends Component
{
    use WithPagination, DiscordComan;

    // ------------------------------------------------------------------ //
    //                          Propiedades públicas                      //
    // ------------------------------------------------------------------ //

    /** Filtro de búsqueda por nombre. */
    public $buscar = '';

    /** Control del modal crear/editar. */
    public $showModal = false;
    public $editing = false;
    public $compositionId = null;
    public $name = '';
    public $description = '';

    /**
     * Lista de armas en el formulario.
     * Cada item: ['name' => string, 'icon' => string]
     */
    public $weapons = [['name' => '', 'icon' => '']];

    // ------------------------------------------------------------------ //
    //                     Propiedades del emoji picker                   //
    // ------------------------------------------------------------------ //

    /** Emojis del servidor (cargados en mount, cacheados). */
    public $emojis = [];

    /** Control de visibilidad del modal picker. */
    public $showEmojiPicker = false;

    /** Índice del arma que está siendo editada por el picker. */
    public $emojiPickerIndex = null;

    // ------------------------------------------------------------------ //
    //                  Confirmación de desactivación                     //
    // ------------------------------------------------------------------ //

    public $confirmingDeactivate = false;
    public $compositionToDeactivate = null;

    // ------------------------------------------------------------------ //
    //                          Query string                              //
    // ------------------------------------------------------------------ //

    protected $queryString = [
        'buscar' => ['except' => ''],
    ];

    // ------------------------------------------------------------------ //
    //                          Reglas de validación                       //
    // ------------------------------------------------------------------ //

    protected $rules = [
        'name'           => 'required|string|max:120',
        'description'    => 'nullable|string|max:500',
        'weapons'        => 'required|array|min:1',
        'weapons.*.name' => 'required|string|max:120',
        'weapons.*.icon' => 'nullable|string|max:64',
    ];

    protected $messages = [
        'name.required'           => 'El nombre de la composición es obligatorio.',
        'weapons.required'        => 'Debes añadir al menos un arma.',
        'weapons.min'             => 'Debes añadir al menos un arma.',
        'weapons.*.name.required' => 'Cada arma debe tener un nombre.',
    ];

    // ------------------------------------------------------------------ //
    //                              Lifecycle                              //
    // ------------------------------------------------------------------ //

    /**
     * Carga los emojis del servidor de Discord al montar el componente.
     */
    public function mount(): void
    {
        $this->emojis = $this->getGuildEmojis();
    }

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    // ------------------------------------------------------------------ //
    //                              Render                                 //
    // ------------------------------------------------------------------ //

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

    // ================================================================== //
    //                        EMOJI PICKER                                 //
    // ================================================================== //

    /**
     * Abre el modal del picker de emojis para un arma concreta.
     */
    public function openEmojiPicker(int $index): void
    {
        $this->emojiPickerIndex = $index;
        $this->showEmojiPicker  = true;
    }

    /**
     * Aplica un emoji seleccionado al arma activa.
     *
     * @param string $markup  `<:name:id>`, `<a:name:id>` o emoji Unicode
     */
    public function selectEmoji(string $markup): void
    {
        if ($this->emojiPickerIndex === null) {
            return;
        }
        if (!isset($this->weapons[$this->emojiPickerIndex])) {
            return;
        }

        $this->weapons[$this->emojiPickerIndex]['icon'] = $markup;

        $this->showEmojiPicker  = false;
        $this->emojiPickerIndex = null;
    }

    /**
     * Quita el icono del arma activa.
     */
    public function clearEmoji(): void
    {
        if ($this->emojiPickerIndex === null) {
            return;
        }
        if (!isset($this->weapons[$this->emojiPickerIndex])) {
            return;
        }

        $this->weapons[$this->emojiPickerIndex]['icon'] = '';

        $this->showEmojiPicker  = false;
        $this->emojiPickerIndex = null;
    }

    /**
     * Cierra el modal del picker sin aplicar cambios.
     */
    public function cancelEmojiPicker(): void
    {
        $this->showEmojiPicker  = false;
        $this->emojiPickerIndex = null;
    }

    // ================================================================== //
    //                              CRUD                                   //
    // ================================================================== //

    /**
     * Abre el modal en modo "crear".
     */
    public function createComposition(): void
    {
        $this->resetForm();
        $this->editing   = false;
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    /**
     * Abre el modal en modo "editar" con los datos cargados.
     */
    public function editComposition(int $id): void
    {
        $comp = Composition::with('weapons')->findOrFail($id);

        $this->compositionId = $comp->id;
        $this->name          = $comp->name;
        $this->description   = $comp->description ?? '';
        $this->weapons       = $comp->weapons->map(fn($w) => [
            'name' => $w->weapon_name,
            'icon' => $w->weapon_icon ?? '',
        ])->toArray();

        $this->editing = true;
        $this->resetValidation();
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    /**
     * Añade una fila vacía al formulario.
     */
    public function addWeaponRow(): void
    {
        $this->weapons[] = ['name' => '', 'icon' => ''];
    }

    /**
     * Elimina una fila por índice. Si queda vacío, añade una en blanco.
     */
    public function removeWeaponRow(int $index): void
    {
        unset($this->weapons[$index]);
        $this->weapons = array_values($this->weapons);

        if (empty($this->weapons)) {
            $this->weapons = [['name' => '', 'icon' => '']];
        }
    }

    
    /**
     * Guarda (crea o actualiza) la composición.
     *
     * En modo edición, NO borra físicamente las armas: hace una
     * reconciliación por nombre para preservar los IDs (y por tanto
     * las FKs de `cta_participants`).
     *
     * Armas retiradas del form:
     *   - Si NO están referenciadas por ninguna CTA → delete físico.
     *   - Si están referenciadas → `active = false` (retirada lógica).
     */
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

                $this->syncWeapons($comp);
            } else {
                $comp = Composition::create([
                    'name'        => $this->name,
                    'description' => $this->description ?: null,
                    'active'      => true,
                ]);

                foreach ($this->weapons as $i => $w) {
                    CompositionWeapon::create([
                        'composition_id' => $comp->id,
                        'weapon_name'    => $w['name'],
                        'weapon_icon'    => $w['icon'] ?: null,
                        'sort_order'     => $i,
                        'active'         => true,
                    ]);
                }
            }
        });

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('close-modal');
        $this->dispatch('notify', [
            'message' => $this->editing
                ? 'Composición actualizada correctamente.'
                : 'Composición creada correctamente.',
            'type'    => 'success',
        ]);
    }

    /**
     * Sincroniza las armas del form con las de la BD, preservando IDs.
     *
     * Estrategia:
     *   1. Se indexan las armas ACTIVAS actuales por nombre (lowercased).
     *   2. Cada arma del form:
     *      - Si el nombre coincide con una existente → UPDATE in-place.
     *      - Si no coincide → CREATE nueva.
     *   3. Las armas activas que NO aparecieron en el form se retiran:
     *      - Sin referencias → DELETE.
     *      - Con referencias → active = false.
     */
    private function syncWeapons(Composition $comp): void
    {
        $existentes = $comp->allWeapons()
            ->where('active', true)
            ->get()
            ->keyBy(fn($w) => mb_strtolower(trim($w->weapon_name)));

        foreach ($this->weapons as $i => $w) {
            $nombre = trim($w['name']);
            $key    = mb_strtolower($nombre);

            if ($existentes->has($key)) {
                // Arma existente con el mismo nombre → actualizar en su lugar
                $arma = $existentes->get($key);
                $arma->update([
                    'weapon_name' => $nombre,
                    'weapon_icon' => $w['icon'] ?: null,
                    'sort_order'  => $i,
                ]);
                $existentes->forget($key);
            } else {
                // Arma nueva → crear
                CompositionWeapon::create([
                    'composition_id' => $comp->id,
                    'weapon_name'    => $nombre,
                    'weapon_icon'    => $w['icon'] ?: null,
                    'sort_order'     => $i,
                    'active'         => true,
                ]);
            }
        }

        // Las que quedan en $existentes fueron retiradas del form
        foreach ($existentes as $arma) {
            $estaReferenciada = DB::table('cta_participants')
                ->where('composition_weapon_id', $arma->id)
                ->exists();

            if ($estaReferenciada) {
                // Se retira lógicamente para no romper el histórico
                $arma->update(['active' => false]);
            } else {
                // Segura de eliminar
                $arma->delete();
            }
        }
    }

    // ================================================================== //
    //                        DESACTIVACIÓN                                //
    // ================================================================== //

    public function confirmDeactivate(int $id): void
    {
        $this->compositionToDeactivate = Composition::findOrFail($id);
        $this->confirmingDeactivate    = true;
    }

    public function deactivate(): void
    {
        if ($this->compositionToDeactivate) {
            $this->compositionToDeactivate->update(['active' => false]);
            $this->dispatch('notify', [
                'message' => 'Composición desactivada.',
                'type'    => 'success',
            ]);
        }
        $this->confirmingDeactivate    = false;
        $this->compositionToDeactivate = null;
    }

    // ================================================================== //
    //                             Helpers                                 //
    // ================================================================== //

    /**
     * Cancela el modal crear/editar y limpia el formulario.
     */
    public function cancel(): void
    {
        $this->resetForm();
        $this->showModal            = false;
        $this->confirmingDeactivate = false;
        $this->dispatch('close-modal');
    }

    /**
     * Limpia el estado del formulario.
     */
    private function resetForm(): void
    {
        $this->compositionId = null;
        $this->name          = '';
        $this->description   = '';
        $this->weapons       = [['name' => '', 'icon' => '']];
        $this->editing       = false;
        $this->resetValidation();
    }
}
