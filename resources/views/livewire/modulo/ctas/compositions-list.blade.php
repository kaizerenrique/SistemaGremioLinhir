<div>
    <!-- Encabezado -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
        <h3 class="font-semibold text-base text-content-light">Composiciones</h3>
        <div class="flex items-center gap-2">
            <x-input type="search" wire:model.live="buscar" placeholder="Buscar..." class="w-56" />
            <x-button wire:click="createComposition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5H4a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5V4a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Nueva
            </x-button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Armas</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                    @forelse($compositions as $comp)
                        <tr class="hover:bg-base-200/50">
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">{{ $comp->name }}</div>
                                @if($comp->description)
                                    <div class="text-xs text-content-light/60">{{ $comp->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($comp->weapons as $w)
                                        <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full text-xs">
                                            {{ $w->weapon_icon }} {{ $w->weapon_name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($comp->active)
                                    <span class="bg-success/20 text-success px-2 py-1 rounded-full text-xs">Activa</span>
                                @else
                                    <span class="bg-error/20 text-error px-2 py-1 rounded-full text-xs">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="editComposition({{ $comp->id }})"
                                            class="text-info hover:text-info/80" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>
                                    @if($comp->active)
                                        <button wire:click="confirmDeactivate({{ $comp->id }})"
                                                class="text-error hover:text-error/80" title="Desactivar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-content-light/70">
                                No hay composiciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mx-4 px-4 py-3">
            {{ $compositions->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    <!-- MODAL Crear/Editar -->
    <x-dialog-modal wire:model="showModal" maxWidth="2xl">
        <x-slot name="title">
            {{ $editing ? 'Editar Composición' : 'Nueva Composición' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="name" value="Nombre" />
                    <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                    @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="description" value="Descripción (opcional)" />
                    <textarea id="description" rows="2" wire:model="description"
                              class="w-full border rounded-md shadow-sm bg-base-200 text-content-light px-3 py-2"></textarea>
                    @error('description') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <x-label value="Armas" />
                        <x-secondary-button type="button" wire:click="addWeaponRow">
                            + Añadir arma
                        </x-secondary-button>
                    </div>

                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        @foreach($weapons as $index => $weapon)
                            <div class="flex gap-2 items-center" wire:key="weapon-{{ $index }}">
                                <x-input type="text" wire:model="weapons.{{ $index }}.icon"
                                         placeholder="🛡️" class="w-16 text-center" maxlength="4"/>
                                <x-input type="text" wire:model="weapons.{{ $index }}.name"
                                         placeholder="Nombre del arma" class="flex-1"/>
                                <button type="button" wire:click="removeWeaponRow({{ $index }})"
                                        class="text-error hover:text-error/80">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                            @error("weapons.{$index}.name")
                                <span class="text-error text-xs">{{ $message }}</span>
                            @enderror
                        @endforeach
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="cancel">Cancelar</x-secondary-button>
            <x-button class="ml-2" wire:click="save">
                {{ $editing ? 'Actualizar' : 'Crear' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- MODAL Confirmar desactivación -->
    <x-confirmation-modal wire:model="confirmingDeactivate">
        <x-slot name="title">Desactivar composición</x-slot>
        <x-slot name="content">
            ¿Seguro que deseas desactivar
            <strong>{{ $compositionToDeactivate?->name }}</strong>?
            Las CTAs existentes seguirán funcionando, pero no podrás crear nuevas con esta composición.
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="cancel">Cancelar</x-secondary-button>
            <x-danger-button class="ml-2" wire:click="deactivate">Desactivar</x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
