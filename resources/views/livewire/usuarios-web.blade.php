<div class="bg-base-300 sm:rounded-lg">
    <div class="flex flex-wrap items-center px-4 py-2">
        <div class="relative w-full max-w-full flex-grow flex-1">
            <h3 class="font-semibold text-base text-content-light leading-tight">
                Usuarios
            </h3>
        </div>
        
        <!-- Botón para crear nuevo usuario -->
        <div class="relative w-full max-w-full flex-grow flex-1 text-right mx-5">
            <x-button wire:click="crearUsuario" class="bg-primary hover:bg-hover-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5H4a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5V4a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Nuevo
            </x-button>
        </div>
        
        <div class="flex flex-col items-center w-full max-w-xl">
            <x-input class="block mt-1 w-100" type="search" wire:model.live="buscar" placeholder="Buscar" />
        </div>
        <div class="relative w-full max-w-full flex-grow flex-1 text-center mt-1 mx-5">
            <select wire:model.live="lim"
                class="w-32 border rounded-md shadow-sm bg-base-200 text-content-light placeholder:text-content-light/60 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 disabled:bg-base-300 disabled:opacity-70 transition-colors px-3 py-2">
                <option value="6" selected>6</option>
                <option value="12">12</option>
                <option value="24">24</option>
                <option value="36">36</option>
                <option value="48">48</option>
            </select>            
        </div>
    </div>
    
    <!-- Mensajes de estado -->
    @if (session()->has('message'))
        <div class="mx-4 mb-4 p-4 bg-success/20 border border-success text-success rounded">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mx-4 mb-4 p-4 bg-error/20 border border-error text-error rounded">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Registro</th>
                        <th class="px-4 py-3">Roles</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                    @foreach ($usuarios as $usuario)
                        <tr class="text-content-light">
                            <td class="px-4 py-3 text-sm">
                                {{ $usuario->name }}
                            </td>                                                     
                            <td class="px-4 py-3 text-sm">
                                {{ $usuario->email }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($usuario->is_socialite)
                                    <span class="bg-neutro text-content-light px-2 py-1 rounded-full text-xs">
                                        Socialite
                                    </span>
                                @else
                                    <span class="bg-primary text-content-light px-2 py-1 rounded-full text-xs">
                                        Correo
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($usuario->roles as $role)
                                        <span class="bg-secondary text-content-light px-2 py-1 rounded-full text-xs">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                    @if($usuario->roles->isEmpty())
                                        <span class="text-content-light/60 text-xs">Sin rol</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex space-x-2">
                                    <button wire:click="editarrol({{ $usuario->id }})" 
                                            class="text-info hover:text-info/80 transition-colors"
                                            title="Editar usuario">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </button>
                                    
                                    @if($usuario->id !== auth()->id())
                                        <button wire:click="confirmarEliminacion({{ $usuario->id }})" 
                                                class="text-error hover:text-error/80 transition-colors"
                                                title="Eliminar usuario">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mx-4">
            <span class="px-4 py-3">
                {{ $usuarios->links('vendor.pagination.tailwind') }}
            </span>
        </div>
    </div>

    <!-- Modal para editar usuario -->
    <x-dialog-modal wire:model="modaleditar" maxWidth="lg">
        <x-slot name="title">
            Editar Usuario
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="name" value="Nombre" />
                    <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                    @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="email" value="Email" />
                    <x-input id="email" type="email" class="mt-1 block w-full" wire:model="email" />
                    @error('email') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="password" value="Nueva Contraseña (dejar en blanco para no cambiar)" />
                    <x-input id="password" type="password" class="mt-1 block w-full" wire:model="password" />
                    @error('password') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label value="Roles" />
                    <div class="mt-2 space-y-2">
                        @foreach($roles as $role)
                            <label class="inline-flex items-center mr-4">
                                <input type="checkbox" 
                                       wire:model="roles_seleccionados" 
                                       value="{{ $role->id }}"
                                       class="rounded border-base-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                                <span class="ml-2 text-content-light">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles_seleccionados') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancelar" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-button class="ml-2" wire:click="actualizarUsuario" wire:loading.attr="disabled">
                Guardar Cambios
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Modal para eliminar usuario -->
    <x-confirmation-modal wire:model="modaleliminar">
        <x-slot name="title">
            Confirmar Eliminación
        </x-slot>

        <x-slot name="content">
            ¿Estás seguro de que deseas eliminar al usuario 
            <strong>{{ $usuario_a_eliminar->name ?? '' }}</strong>? 
            Esta acción no se puede deshacer.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancelar" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-danger-button class="ml-2" wire:click="eliminarUsuario" wire:loading.attr="disabled">
                Eliminar Usuario
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    <!-- Modal para crear usuario -->
    <x-dialog-modal wire:model="modalcrear" maxWidth="lg">
        <x-slot name="title">
            Crear Nuevo Usuario
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="nuevo_name" value="Nombre" />
                    <x-input id="nuevo_name" type="text" class="mt-1 block w-full" wire:model="nuevo_name" />
                    @error('nuevo_name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="nuevo_email" value="Email" />
                    <x-input id="nuevo_email" type="email" class="mt-1 block w-full" wire:model="nuevo_email" />
                    @error('nuevo_email') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="nuevo_password" value="Contraseña" />
                    <x-input id="nuevo_password" type="password" class="mt-1 block w-full" wire:model="nuevo_password" />
                    @error('nuevo_password') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label value="Roles" />
                    <div class="mt-2 space-y-2">
                        @foreach($roles as $role)
                            <label class="inline-flex items-center mr-4">
                                <input type="checkbox" 
                                       wire:model="nuevo_roles" 
                                       value="{{ $role->id }}"
                                       class="rounded border-base-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                                <span class="ml-2 text-content-light">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('nuevo_roles') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancelar" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-button class="ml-2" wire:click="guardarNuevoUsuario" wire:loading.attr="disabled">
                Crear Usuario
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>