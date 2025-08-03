<?php

namespace App\Livewire;

use Livewire\Component;
use \App\Traits\Albion;
use \App\Traits\Estadodelservidor;

class Dashboard extends Component
{
    use Albion;
    use Estadodelservidor;

    public function render()
    {
        //$linhir = $this->guardar_estado();
        //dd($linhir);
        //$datospersonaje = $this->datospersonaje();
        return view('livewire.dashboard');
    }
}
