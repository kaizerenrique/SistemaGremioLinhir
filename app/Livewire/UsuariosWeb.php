<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Traits\DiscordComan;

class UsuariosWeb extends Component
{
    use WithPagination; 
    use DiscordComan; 

    public $buscar, $lim;
    public $modaleditar = false;
    public $modaleliminar = false;
    public $modalcrear = false;
    
    // Propiedades para edición
    public $usuario_id;
    public $name;
    public $email;
    public $password;
    public $roles_seleccionados = [];
    
    // Propiedades para creación
    public $nuevo_name;
    public $nuevo_email;
    public $nuevo_password;
    public $nuevo_roles = [];
    
    // Propiedades para eliminación
    public $usuario_a_eliminar;
    
    protected $queryString = [
        'buscar' => ['except' => '']
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'password' => 'nullable|min:8',
        'roles_seleccionados' => 'array',
        'roles_seleccionados.*' => 'exists:roles,id',
    ];

    public function render()
    {
        if ($this->lim == null) {
            $this->lim = 6;
        } 

        $usuarios = User::with('authProviders', 'roles')
                    ->where('name', 'like', '%'.$this->buscar . '%')
                    ->orWhere('email', 'like', '%'.$this->buscar . '%')
                    ->orderBy('id','desc')
                    ->paginate($this->lim);
        
        $roles = Role::all();

        return view('livewire.usuarios-web',[
            'usuarios' => $usuarios,
            'roles' => $roles,
        ]);
    }

    //Actualizar tabla para corregir falla de busqueda
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function editarrol($id)
    {
        $this->resetValidation();
        $usuario = User::with('roles')->findOrFail($id);
        
        $this->usuario_id = $usuario->id;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->roles_seleccionados = $usuario->roles->pluck('id')->toArray();
        
        $this->modaleditar = true;
    }

    public function actualizarUsuario()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->usuario_id)
            ],
            'password' => 'nullable|min:8',
            'roles_seleccionados' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();
            
            $usuario = User::findOrFail($this->usuario_id);
            $usuario->name = $this->name;
            $usuario->email = $this->email;
            
            if ($this->password) {
                $usuario->password = Hash::make($this->password);
            }
            
            $usuario->save();
            
            // Sincronizar roles
            $roles = Role::whereIn('id', $this->roles_seleccionados)->get();
            $usuario->syncRoles($roles);
            
            DB::commit();
            
            $this->modaleditar = false;
            $this->reset(['usuario_id', 'name', 'email', 'password', 'roles_seleccionados']);
            
            session()->flash('message', 'Usuario actualizado correctamente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    public function confirmarEliminacion($id)
    {
        $this->usuario_a_eliminar = User::findOrFail($id);
        $this->modaleliminar = true;
    }

    public function eliminarUsuario()
    {
        try {
            // Prevenir eliminación del usuario actual
            if ($this->usuario_a_eliminar->id === auth()->id()) {
                session()->flash('error', 'No puedes eliminar tu propio usuario.');
                $this->modaleliminar = false;
                return;
            }

            $this->usuario_a_eliminar->delete();
            
            $this->modaleliminar = false;
            $this->reset(['usuario_a_eliminar']);
            
            session()->flash('message', 'Usuario eliminado correctamente.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }

    public function crearUsuario()
    {
        $this->resetValidation();
        $this->reset(['nuevo_name', 'nuevo_email', 'nuevo_password', 'nuevo_roles']);
        $this->modalcrear = true;
    }

    public function guardarNuevoUsuario()
    {
        $this->validate([
            'nuevo_name' => 'required|string|max:255',
            'nuevo_email' => 'required|email|max:255|unique:users,email',
            'nuevo_password' => 'required|min:8',
            'nuevo_roles' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();
            
            $usuario = User::create([
                'name' => $this->nuevo_name,
                'email' => $this->nuevo_email,
                'password' => Hash::make($this->nuevo_password),
            ]);
            
            // Asignar roles
            $roles = Role::whereIn('id', $this->nuevo_roles)->get();
            $usuario->syncRoles($roles);
            
            DB::commit();
            
            $this->modalcrear = false;
            $this->reset(['nuevo_name', 'nuevo_email', 'nuevo_password', 'nuevo_roles']);
            
            session()->flash('message', 'Usuario creado correctamente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    public function cancelar()
    {
        $this->modaleditar = false;
        $this->modaleliminar = false;
        $this->modalcrear = false;
        $this->resetErrorBag();
        $this->resetValidation();
        $this->reset([
            'usuario_id', 'name', 'email', 'password', 'roles_seleccionados',
            'usuario_a_eliminar', 'nuevo_name', 'nuevo_email', 'nuevo_password', 'nuevo_roles'
        ]);
    }
}
