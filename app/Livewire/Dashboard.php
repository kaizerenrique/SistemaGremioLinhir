<?php

namespace App\Livewire;

use Livewire\Component;
use \App\Traits\Albion;

class Dashboard extends Component
{
    use Albion;

    public function render()
    {
        //$linhir = $this->integrantesdelgremiolinhir();
        //$datospersonaje = $this->datospersonaje();
        return view('livewire.dashboard');
    }
}
