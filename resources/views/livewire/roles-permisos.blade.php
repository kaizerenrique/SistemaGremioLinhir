<div>
    <!-- Encabezado con búsqueda, botón nuevo rol y botón nuevo permiso -->
    <div class="flex flex-wrap items-center justify-between px-4 py-2 gap-2">
        <div class="flex items-center flex-1 min-w-[200px]">
            <h3 class="font-semibold text-base text-content-light leading-tight mr-4">
                Roles y Permisos
            </h3>
        </div>
        <div class="flex flex-1 items-center justify-end gap-2 flex-wrap">
            <!-- Botón Nuevo Permiso -->
            <x-button wire:click="createPermission" class="bg-secondary hover:bg-hover-secondary text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                </svg>
                Nuevo Permiso
            </x-button>

            <!-- Botón Nuevo Rol -->
            <x-button wire:click="createRole" class="bg-primary hover:bg-hover-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5H4a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5V4a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Nuevo Rol
            </x-button>
        </div>
    </div>

    <!-- Barra de búsqueda (debajo) -->
    <div class="px-4 pb-2">
        <x-input class="block w-full max-w-xs" type="search" wire:model.live="buscar" placeholder="Buscar rol por nombre..." />
    </div>

    <!-- Mensajes de notificación -->
    @if (session()->has('message'))
        <div class="mx-4 mb-4 p-3 bg-success/10 border border-success/30 text-success rounded-md">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mx-4 mb-4 p-3 bg-error/10 border border-error/30 text-error rounded-md">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabla de roles -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Permisos</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                    @foreach ($roles as $role)
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="px-4 py-3 text-sm">{{ $role->id }}</td>
                            <td class="px-4 py-3 text-sm">
                                {{ $role->name }}
                                @if($role->is_system)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary/20 text-primary">
                                        Sistema
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($role->permissions as $permission)
                                        <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full text-xs">
                                            {{ $permission->name }}
                                        </span>
                                    @empty
                                        <span class="text-content-light/50 text-xs">Sin permisos</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if(!$role->is_system)
                                        <button wire:click="editRole({{ $role->id }})" 
                                                class="text-info hover:text-info/80 transition" 
                                                title="Editar rol">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $role->id }})" 
                                                class="text-error hover:text-error/80 transition" 
                                                title="Eliminar rol">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-content-light/50 text-xs italic">Protegido</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="mx-4 px-4 py-3">
            {{ $roles->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    <!-- ============== MODAL: Crear/Editar Rol ============== -->
    <x-dialog-modal wire:model="showModal" maxWidth="lg">
        <x-slot name="title">
            {{ $editing ? 'Editar Rol' : 'Crear Nuevo Rol' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <!-- Nombre del rol -->
                <div>
                    <x-label for="roleName" value="Nombre del rol" />
                    <x-input id="roleName" type="text" class="mt-1 block w-full" 
                             wire:model="roleName" 
                             placeholder="Ej: Moderador" />
                    @error('roleName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Permisos disponibles -->
                <div>
                    <x-label value="Permisos" />
                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-2 bg-base-200/50 rounded border border-base-300">
                        @forelse($permisos as $perm)
                            <label class="flex items-center space-x-2 hover:bg-base-200/50 rounded px-2 py-1 transition">
                                <input type="checkbox" 
                                       wire:model="selectedPermissions" 
                                       value="{{ $perm->id }}"
                                       class="rounded border-base-300 text-primary shadow-sm focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-base-100">
                                <span class="text-content-light text-sm">{{ $perm->name }}</span>
                            </label>
                        @empty
                            <p class="text-content-light/50 text-sm col-span-2">No hay permisos disponibles. Crea uno primero.</p>
                        @endforelse
                    </div>
                    @error('selectedPermissions') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Información adicional -->
                <div class="bg-base-200/50 p-3 rounded border border-base-300 text-xs text-content-light/70">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Los permisos seleccionados se asignarán automáticamente al rol.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancel" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-button class="ml-2" wire:click="saveRole" wire:loading.attr="disabled">
                {{ $editing ? 'Actualizar' : 'Crear' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- ============== MODAL: Crear Nuevo Permiso ============== -->
    <x-dialog-modal wire:model="showPermissionModal" maxWidth="lg">
        <x-slot name="title">
            Crear Nuevo Permiso
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="permissionName" value="Nombre del permiso" />
                    <x-input id="permissionName" type="text" class="mt-1 block w-full" 
                             wire:model="permissionName" 
                             placeholder="Ej: manage_users" />
                    @error('permissionName') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                @if(isset($permissionGuard))
                    <div>
                        <x-label for="permissionGuard" value="Guard (opcional)" />
                        <x-input id="permissionGuard" type="text" class="mt-1 block w-full" 
                                 wire:model="permissionGuard" 
                                 placeholder="web" />
                        <p class="text-xs text-content-light/50 mt-1">Generalmente 'web' o 'sanctum'. Por defecto es 'web'.</p>
                    </div>
                @endif

                <div class="bg-primary/10 p-3 rounded border border-primary/30 text-xs text-content-light/80">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1 text-primary" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    El permiso se creará y estará disponible para asignar a roles inmediatamente.
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancel" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-button class="ml-2" wire:click="savePermission" wire:loading.attr="disabled">
                Crear Permiso
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- ============== MODAL: Confirmar Eliminación de Rol ============== -->
    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">
            Eliminar Rol
        </x-slot>

        <x-slot name="content">
            <div class="space-y-2">
                <p>¿Estás seguro de que deseas eliminar el rol <strong>{{ $roleToDeleteName }}</strong>?</p>
                <p class="text-sm text-content-light/70">
                    Esta acción no se puede deshacer. 
                    @if($roleToDeleteId && \Spatie\Permission\Models\Role::find($roleToDeleteId)?->users()->count() > 0)
                        <span class="text-error block mt-2">⚠️ Hay usuarios con este rol asignado. Debes reasignarlos primero.</span>
                    @endif
                </p>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancel" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-danger-button class="ml-2" wire:click="deleteRole" wire:loading.attr="disabled">
                Eliminar
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Script para notificaciones toast (opcional) -->
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('notify', (data) => {
                alert((data.type === 'success' ? '✅ ' : '❌ ') + data.message);
            });
        });
    </script>
    @endpush
</div>