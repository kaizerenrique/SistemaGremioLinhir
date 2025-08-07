<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Traits\DiscordComan;

class UsuariosWeb extends Component
{
    use WithPagination; 
    use DiscordComan; 


    public $buscar;

    protected $queryString = [
        'buscar' => ['except' => '']
    ];


    public function render()
    {

        //listar los usuarios y consultar por nombre y correo
        $usuarios = User::with('authProviders')
                    ->where('name', 'like', '%'.$this->buscar . '%')
                    ->orWhere('email', 'like', '%'.$this->buscar . '%')
                    ->orderBy('id','desc')
                    ->paginate(6);
        
        $roles = Role::all();

        

        return view('livewire.usuarios-web',[
            'usuarios' => $usuarios, 
        ]);
    }

    //Actualizar tabla para corregir falla de busqueda
    public function updatingBuscar()
    {
        $this->resetPage();
    }
}
