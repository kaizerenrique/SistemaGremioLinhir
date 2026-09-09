<div>
    <!-- Botón para crear nueva tarea -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-content-accent">Gestión de Tareas</h2>
        <button wire:click="createTask" class="bg-primary hover:bg-hover-primary text-white px-4 py-2 rounded-lg font-semibold transition-all hover:scale-105">
            + Nueva Tarea
        </button>
    </div>

    <!-- Tabla de tareas -->
    <div class="bg-base-300 rounded-lg overflow-hidden border border-base-300">
        <table class="w-full">
            <thead>
                <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Puntos</th>
                    <th class="px-4 py-3">Repetible</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    <tr class="border-b border-base-200 hover:bg-base-200/50 transition-colors">
                        <td class="px-4 py-3 text-content-light text-sm">{{ $task->id }}</td>
                        <td class="px-4 py-3 text-content-light text-sm">{{ $task->name }}</td>
                        <td class="px-4 py-3 text-content-light text-sm">{{ $task->points }}</td>
                        <td class="px-4 py-3 text-content-light text-sm">{{ $task->repeatable ? '✅ Sí' : '❌ No' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $task->active ? 'bg-success/20 text-success' : 'bg-error/20 text-error' }}">
                                {{ $task->active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <!-- Botón Editar (siempre visible) -->
                                <button wire:click="editTask({{ $task->id }})" class="text-info hover:text-info/80 transition" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>

                                <!-- Botón Activar/Desactivar según estado -->
                                @if($task->active)
                                    <button wire:click="confirmAction({{ $task->id }}, 'deactivate')" class="text-error hover:text-error/80 transition" title="Desactivar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @else
                                    <button wire:click="confirmAction({{ $task->id }}, 'activate')" class="text-success hover:text-success/80 transition" title="Activar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-content-light/70">
                            No hay tareas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $tasks->links('vendor.pagination.tailwind') }}
    </div>

    <!-- ============== MODAL: Crear/Editar Tarea ============== -->
    <x-dialog-modal wire:model="showModal" maxWidth="lg">
        <x-slot name="title">
            {{ $editing ? 'Editar Tarea' : 'Nueva Tarea' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="name" value="Nombre de la tarea" />
                    <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" placeholder="Ej: Recolectar 500 de Piedra" />
                    @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="points" value="Puntos" />
                    <x-input id="points" type="number" class="mt-1 block w-full" wire:model="points" placeholder="15" min="1" />
                    @error('points') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="description" value="Descripción (opcional)" />
                    <textarea id="description" rows="3" class="w-full border rounded-md shadow-sm bg-base-200 text-content-light placeholder:text-content-light/60 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 disabled:bg-base-300 disabled:opacity-70 transition-colors px-3 py-2" wire:model="description" placeholder="Detalles adicionales de la tarea..."></textarea>
                    @error('description') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="repeatable" wire:model="repeatable" class="rounded bg-base-200 border-base-100 text-primary shadow-sm focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-base-100">
                    <label for="repeatable" class="ml-2 text-content-light">Repetible (se puede completar varias veces)</label>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancel" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-button class="ml-2" wire:click="saveTask" wire:loading.attr="disabled">
                {{ $editing ? 'Actualizar' : 'Crear' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- ============== MODAL: Confirmar Acción ============== -->
    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">
            {{ $deleteAction === 'activate' ? 'Activar Tarea' : 'Desactivar Tarea' }}
        </x-slot>

        <x-slot name="content">
            @if($deleteAction === 'activate')
                ¿Estás seguro de que deseas <strong>activar</strong> la tarea <strong>{{ $taskToDeleteName }}</strong>?<br>
                Los miembros podrán volver a reportarla.
            @else
                ¿Estás seguro de que deseas <strong>desactivar</strong> la tarea <strong>{{ $taskToDeleteName }}</strong>?<br>
                Los miembros ya no podrán reportarla, pero los puntos ya otorgados se mantendrán.
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancel" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-button class="ml-2" wire:click="executeAction" wire:loading.attr="disabled" 
                :class="$deleteAction === 'activate' ? 'bg-success hover:bg-success/80' : 'bg-error hover:bg-error/80'">
                {{ $deleteAction === 'activate' ? 'Activar' : 'Desactivar' }}
            </x-button>
        </x-slot>
    </x-confirmation-modal>
</div>
