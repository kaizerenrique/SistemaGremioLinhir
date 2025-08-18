<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\DiscordComan;
use App\Traits\AlbionEconomia;

class Panel extends Component
{
    use DiscordComan;
    use AlbionEconomia;

    public function render()
    {
        $linhir_servidor = $this->checkDiscordMembership();
        $oro = $this->consultarvalordeloro();

        return view('livewire.panel',[
            'linhir_servidor' => $linhir_servidor, 
            'oro' => $oro, 
        ]);
    }
}
