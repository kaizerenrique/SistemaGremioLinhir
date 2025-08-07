<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\WithPagination;

class RolesPermisos extends Component
{
    use WithPagination; 

    public $buscar;

    protected $queryString = [
        'buscar' => ['except' => '']
    ];

    public function render()
    {
        $roles = Role::where('name', 'like', '%'.$this->buscar . '%')  //buscar por nombre
                      ->orderBy('id','desc') //ordenar de forma decendente
                      ->paginate(6); //paginacion

        $permisos = Permission::all();

        return view('livewire.roles-permisos',[
            'roles' => $roles,
        ]);
    }
}
