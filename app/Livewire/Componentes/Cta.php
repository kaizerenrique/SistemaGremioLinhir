<?php

namespace App\Livewire\Componentes;

use Livewire\Component;
use \App\Traits\Albion;

class Cta extends Component
{
    use Albion;

    public function render()
    {
        $linhir_id = config('app.linhir_gremio_id');
        $linhir_datos = $this->consultargremio($linhir_id);

        $alianza = "HRL";

        $ho_gremiales = 2;

        //dd($alianza);

        return view('livewire.componentes.cta',[
            'linhir_datos' => $linhir_datos,
            'ho_gremiales' => $ho_gremiales,
            'alianza' => $alianza,
        ]);
    }
}
