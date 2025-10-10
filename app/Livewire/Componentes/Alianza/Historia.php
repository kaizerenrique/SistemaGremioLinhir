<?php

namespace App\Livewire\Componentes\Alianza;

use Livewire\Component;

class Historia extends Component
{
    public $eventos = [
        [
            'año' => '30-04-2025',
            'titulo' => 'Fundación de la Alianza',
            'descripcion' => 'Linhir creó la alianza Hispanic Republic of Linhir, como respuesta a una agresión sufrida el dia previo en Timberslope Dell.',
            'icono' => '🏛️'
        ],
        [
            'año' => '01-05-2025',
            'titulo' => 'Ingresa a la The New Orden',
            'descripcion' => 'Ingresa The New Orden a HRL siendo el primer gremio y el aliado más antiguo en actividad. Y el primero en plantar HO/HQ con el emblema de la alianza',
            'icono' => '🌍'
        ],
        [
            'año' => '02-05-2025',
            'titulo' => 'Dagor Nirnaeth Arnoediad',
            'descripcion' => 'Esta sería la primera batalla de la alianza y fue una victoria total. El nombre que se traduce como la batalla de las lágrimas innumerables, debido al enorme número de bajas sufridas por el enemigo.',
            'icono' => '🏆'
        ],
        [
            'año' => '2025',
            'titulo' => 'La expansión',
            'descripcion' => 'La alianza abre sus puertas a nuevos gremios y se trazan los planes de ayudar a gremios en crecimiento ',            
            'icono' => '🤝'
        ],
        [
            'año' => '01-10-2025',
            'titulo' => 'Alianza Estratégica',
            'descripcion' => 'Una vez más ante una potencial amenaza y dando solución a la necesidad de los gremios aliados, se ejecuta una operación exitosa en la que se plantan 2 HOs simultáneamente en un mapa de avalon y se integra un nuevo aliado consiguiendo así asegurar un mapa de caminos para la alianza. .',            
            'icono' => '🏛️'
        ],
        [
            'año' => '2025',
            'titulo' => 'Visión',
            'descripcion' => 'Actualmente los objetivos de la alianza se centra en el crecimiento de los gremios que la integran, en reclutar nuevos jugadores y crear un buen ambiente para todos.',            
            'icono' => '🚀'
        ]
    ];

    public function render()
    {
        return view('livewire.componentes.alianza.historia');
    }
}
