<?php

namespace App\Livewire\Componentes\Alianza;

use Livewire\Component;

class Ingresos extends Component
{
    // Enlace de Discord de la alianza
    public $discordUrl = 'https://discord.gg/tualianza';

    // Requisitos para nuevos gremios
    public $requisitos = [
        [
            'titulo' => 'Gremio en Crecimiento',
            'descripcion' => 'Ser un gremio relativamente nuevo con ganas de desarrollarse',
            'icono' => '🌱'
        ],
        [
            'titulo' => 'Contenido Propio',
            'descripcion' => 'Tener actividades y contenido organizado regularmente',
            'icono' => '⚔️'
        ],
        [
            'titulo' => 'Respeto Mutuo',
            'descripcion' => 'Aceptar y respetar las normas establecidas por la alianza',
            'icono' => '🤝'
        ],
        [
            'titulo' => 'Base Sólida',
            'descripcion' => 'Contar con más de 20 miembros activos en el gremio',
            'icono' => '👥'
        ]
    ];

    // Pasos del procedimiento
    public $procedimiento = [
        [
            'paso' => 1,
            'titulo' => 'Unirse al Discord',
            'descripcion' => 'Accede a nuestro servidor de Discord oficial de la alianza',
            'icono' => '📱'
        ],
        [
            'paso' => 2,
            'titulo' => 'Solicitar Reunión',
            'descripcion' => 'Contacta con los líderes para coordinar una reunión inicial',
            'icono' => '🗓️'
        ],
        [
            'paso' => 3,
            'titulo' => 'Evaluación Mutua',
            'descripcion' => 'Conoce nuestra estructura y presenten su gremio',
            'icono' => '✅'
        ],
        [
            'paso' => 4,
            'titulo' => 'Integración',
            'descripcion' => 'Una vez aceptados, comienza el proceso de integración',
            'icono' => '🚀'
        ]
    ];

    
    public function render()
    {
        return view('livewire.componentes.alianza.ingresos');
    }
}
