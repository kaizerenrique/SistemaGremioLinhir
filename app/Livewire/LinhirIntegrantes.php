<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Personaje;

class LinhirIntegrantes extends Component
{
    use WithPagination; 

    public $buscar, $lim;

    protected $queryString = [
        'buscar' => ['except' => '']
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
}
