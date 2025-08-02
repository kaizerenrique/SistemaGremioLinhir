<?php

namespace App\Livewire\Componentes;

use Livewire\Component;

class Team extends Component
{
        public $specialists = [
        [
            'name' => 'Peletero',
            'character' => 'Eirik Skullsplitter',
            'fame' => 1584230,
            'image' => 'hera/peletero.png'
        ],
        [
            'name' => 'Pescador',
            'character' => 'Bjorn Ironhook',
            'fame' => 1423567,
            'image' => 'hera/pescador.png'
        ],
        [
            'name' => 'Cantero',
            'character' => 'Thrain Stonefist',
            'fame' => 1320456,
            'image' => 'hera/cantero.png'
        ],
        [
            'name' => 'Minero',
            'character' => 'Gunnar Deepvein',
            'fame' => 1723891,
            'image' => 'hera/minero.png'
        ],
        [
            'name' => 'Cosechador',
            'character' => 'Freya Greenhand',
            'fame' => 1267450,
            'image' => 'hera/cosechador.png'
        ],
        [
            'name' => 'Leñador',
            'character' => 'Ragnar Oakenshield',
            'fame' => 1389245,
            'image' => 'hera/lenador.png'
        ],
        [
            'name' => 'Crafters',
            'character' => 'Astrid Silverforge',
            'fame' => 2145789,
            'image' => 'hera/crafters.png'
        ],
        [
            'name' => 'Agricultor',
            'character' => 'Ingrid Earthtender',
            'fame' => 1203678,
            'image' => 'hera/agricultor.png'
        ]
    ];
    
    public function render()
    {
        return view('livewire.componentes.team');
    }
}
