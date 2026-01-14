<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Personaje;
use Carbon\Carbon;

class LinhirIntegrantes extends Component
{
    use WithPagination; 

    public $buscar, $lim;
    public $modaleditar = false;
    public $personaje_id, $birthdate, $nombre_personaje;

    protected $queryString = [
        'buscar' => ['except' => '']
    ];

    protected $rules = [
        'birthdate' => 'nullable|date',
    ];

    public function render()
    {
        $linhir_id = config('app.linhir_gremio_id');

        if ($this->lim == null) {
            $this->lim = 6;
        } 


        if (!empty($this->buscar)) {
            $miembros = Personaje::where('GuildId', $linhir_id)
                ->where('Name', 'like', '%'.$this->buscar.'%')
                ->with('lifetimeStatistics.gatheringStatistics')
                ->orderBy('id', 'desc')
                ->paginate($this->lim);
        } else {
            $miembros = Personaje::where('GuildId', $linhir_id)
                ->with('lifetimeStatistics.gatheringStatistics')
                ->orderBy('id', 'desc')
                ->paginate($this->lim);
        }

        //dd($miembros); 

        return view('livewire.linhir-integrantes',[
            'miembros' => $miembros,
        ]);
    }

    /**
     * Corrige la numeracion de la tabla al realizar 
     * una busqueda
     */
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function editarinfo($id)    
    {
        $personaje = Personaje::find($id);
        
        if ($personaje) {
            $this->personaje_id = $personaje->id;
            $this->nombre_personaje = $personaje->Name;
            $this->birthdate = $personaje->birthdate ? Carbon::parse($personaje->birthdate)->format('Y-m-d') : null;
            $this->modaleditar = true;
        }
    }

    public function guardarBirthdate()
    {
        $this->validate();

        $personaje = Personaje::find($this->personaje_id);
        
        if ($personaje) {
            $personaje->update([
                'birthdate' => $this->birthdate ? Carbon::parse($this->birthdate)->format('Y-m-d') : null
            ]);
            
            // Emitir evento para notificación
            session()->flash('message', 'Fecha de cumpleaños actualizada correctamente.');
            
            $this->reset(['birthdate', 'personaje_id', 'nombre_personaje']);
            $this->modaleditar = false;
        }
    }

    public function limpiarFecha()
    {
        $this->birthdate = null;
    }
}
