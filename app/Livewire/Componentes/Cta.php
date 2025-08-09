<?php

namespace App\Livewire\Componentes;

use Livewire\Component;
use \App\Traits\Albion;

class Cta extends Component
{
    use Albion;

    public function render()
    {
        $tops = $this->topFamaSemanal();

        //dd($tops );

        return view('livewire.componentes.cta',[
            'topPvE' => $tops['pve'],
            'topPvP' => $tops['pvp'],
            'semana' => $tops['semana']
        ]);
    }
}
