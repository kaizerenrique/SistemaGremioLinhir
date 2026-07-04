<?php

namespace App\Livewire\Componentes;

use Livewire\Component;
use \App\Traits\Albion;
use Carbon\Carbon;

class Team extends Component
{
    use Albion;
    
    public function render()
    {
        
        $specialists = $this->lideresEspecialidades();
        
        // Obtenemos la fecha de inicio de la semana actual (Lunes)
        $semanaInicio = Carbon::now()->startOfWeek()->format('d/m/Y');
        $semanaFin = Carbon::now()->endOfWeek()->format('d/m/Y');

        return view('livewire.componentes.team', [
            'specialists' => $specialists,
            'semanaInicio' => $semanaInicio,
            'semanaFin' => $semanaFin,
        ]);
    }
}
