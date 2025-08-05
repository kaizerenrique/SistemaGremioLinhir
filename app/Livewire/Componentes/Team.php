<?php

namespace App\Livewire\Componentes;

use Livewire\Component;
use \App\Traits\Albion;

class Team extends Component
{
    use Albion;
    
    public function render()
    {
        
        $specialists = $this->lideresEspecialidades();

        
        return view('livewire.componentes.team',[
            'specialists' => $specialists,
        ]);
    }
}
