<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RolesPermisos extends Component
{
    use WithPagination;

    // Búsqueda de roles
    public $buscar;

    // Propiedades para el modal de creación/edición de roles
    public $showModal = false;
    public $editing = false;
    public $roleId = null;
    public $roleName = '';
    public $selectedPermissions = [];

    // Propiedades para el modal de confirmación de eliminación de roles
    public $confirmingDeletion = false;
    public $roleToDeleteId = null;
    public $roleToDeleteName = '';

    // Propiedades para el modal de creación de permisos
    public $showPermissionModal = false;
    public $permissionName = '';
    public $permissionGuard = 'web'; // o 'sanctum', según tu configuración

    // Lista de todos los permisos disponibles
    public $allPermissions = [];

    protected $rules = [
        'roleName' => 'required|string|max:255',
        'selectedPermissions' => 'array',
    ];

    protected $messages = [
        'roleName.required' => 'El nombre del rol es obligatorio.',
        'roleName.unique' => 'Ya existe un rol con este nombre.',
        'permissionName.required' => 'El nombre del permiso es obligatorio.',
        'permissionName.unique' => 'Ya existe un permiso con este nombre.',
    ];

    protected $queryString = [
        'buscar' => ['except' => '']
    ];

    public function mount()
    {
        $this->allPermissions = Permission::all();
    }

    public function render()
    {
        $roles = Role::where('name', 'like', '%' . $this->buscar . '%')
            ->orderBy('id', 'desc')
            ->paginate(6);

        return view('livewire.roles-permisos', [
            'roles' => $roles,
            'permisos' => $this->allPermissions,
        ]);
    }

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    // ============== MÉTODOS PARA ROLES ==============

    public function createRole()
    {
        $this->reset(['roleId', 'roleName', 'selectedPermissions', 'editing']);
        $this->resetValidation();
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function editRole($id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            $this->dispatch('notify', [
                'message' => 'Este rol es del sistema y no puede ser editado manualmente.',
                'type' => 'error'
            ]);
            return;
        }

        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->editing = true;
        $this->resetValidation();
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    public function saveRole()
    {
        $this->validate([
            'roleName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->roleId),
            ],
            'selectedPermissions' => 'array',
        ]);

        try {
            DB::transaction(function () {
                if ($this->editing) {
                    $role = Role::findOrFail($this->roleId);
                    $role->update(['name' => $this->roleName]);
                    $message = 'Rol actualizado correctamente.';
                } else {
                    $role = Role::create(['name' => $this->roleName]);
                    $message = 'Rol creado correctamente.';
                }

                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $role->syncPermissions($permissions);

                app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

                $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
            });

            $this->showModal = false;
            $this->reset(['roleId', 'roleName', 'selectedPermissions', 'editing']);
            $this->resetValidation();
            $this->dispatch('close-modal');
            $this->dispatch('roleSaved');

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function confirmDelete($id)
    {
        $role = Role::findOrFail($id);
        if ($role->is_system) {
            $this->dispatch('notify', [
                'message' => 'No se puede eliminar un rol del sistema.',
                'type' => 'error'
            ]);
            return;
        }
        $this->roleToDeleteId = $role->id;
        $this->roleToDeleteName = $role->name;
        $this->confirmingDeletion = true;
    }

    public function deleteRole()
    {
        try {
            DB::transaction(function () {
                $role = Role::findOrFail($this->roleToDeleteId);
                if ($role->is_system) {
                    throw new \Exception('No se puede eliminar un rol del sistema.');
                }
                if ($role->users()->count() > 0) {
                    throw new \Exception('No se puede eliminar el rol porque tiene usuarios asignados.');
                }
                $role->delete();
                app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
                $this->dispatch('notify', ['message' => 'Rol eliminado correctamente.', 'type' => 'success']);
            });

            $this->confirmingDeletion = false;
            $this->roleToDeleteId = null;
            $this->roleToDeleteName = '';
            $this->dispatch('roleSaved');

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error']);
            $this->confirmingDeletion = false;
        }
    }

    // ============== MÉTODOS PARA PERMISOS ==============

    public function createPermission()
    {
        $this->reset(['permissionName']);
        $this->resetValidation();
        $this->showPermissionModal = true;
        $this->dispatch('open-modal');
    }

    public function savePermission()
    {
        $this->validate([
            'permissionName' => 'required|string|max:255|unique:permissions,name',
        ]);

        try {
            Permission::create([
                'name' => $this->permissionName,
                'guard_name' => $this->permissionGuard,
            ]);

            // Actualizar la lista de permisos
            $this->allPermissions = Permission::all();

            $this->dispatch('notify', ['message' => 'Permiso creado correctamente.', 'type' => 'success']);
            $this->showPermissionModal = false;
            $this->reset(['permissionName']);
            $this->dispatch('close-modal');

            // Limpiar caché
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->showPermissionModal = false;
        $this->confirmingDeletion = false;
        $this->reset(['roleId', 'roleName', 'selectedPermissions', 'permissionName']);
        $this->resetValidation();
        $this->dispatch('close-modal');
    }
}