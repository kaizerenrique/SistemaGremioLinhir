<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\DiscordComan;
use App\Traits\AlbionEconomia;
use App\Traits\Albion;
use Livewire\WithPagination;
use App\Models\GoldPrice;

class Panel extends Component
{
    use DiscordComan;
    use AlbionEconomia;
    use Albion;
    use WithPagination; 

    public $buscar;

    protected $queryString = [
        'buscar' => ['except' => '']
    ];

    public function render()
    {
        //revisar si el usuario esta en el servidor de discord
        $linhir_servidor = $this->checkDiscordMembership();
        //revisar el valor del oro
        $oro = GoldPrice::latest('id')->first();


        //comprobar perfiles
        $perfiles = auth()->user()->personajes()->where('Name', 'like', '%'.$this->buscar.'%')->paginate(6);
        //contar perfiles
        $perfilesno = auth()->user()->personajes;
        $num = count($perfilesno);  

        // Verificar si ALGÚN perfil tiene miembro = true (1)
        $algunoEsMiembro = $perfiles->contains('miembro', true);
        
        //dd($algunoEsMiembro);

        return view('livewire.panel',[
            'linhir_servidor' => $linhir_servidor, 
            'oro' => $oro, 
            'num' => $num, 
            'perfiles' => $perfiles, 
            'algunoEsMiembro' => $algunoEsMiembro,
        ]);
    }

    //Actualizar tabla para corregir falla de busqueda
    public function updatingBuscar()
    {
        $this->resetPage();
    }
}
