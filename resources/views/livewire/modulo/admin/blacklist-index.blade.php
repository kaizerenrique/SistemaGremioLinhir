<div>
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
        <div class="flex items-center gap-2">
            <h3 class="font-semibold text-base text-content-light">Lista Negra (KOS)</h3>
            <span class="bg-error/20 text-error text-xs px-2 py-0.5 rounded-full">Alto Riesgo</span>
        </div>
        
        <div class="flex gap-2 flex-wrap">
            <x-input type="search" wire:model.live="buscar" placeholder="Buscar por nombre o ID..." class="w-64" />
            <x-button wire:click="openModal" class="bg-error hover:bg-error/80">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                </svg>
                Banear Jugador
            </x-button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg shadow-xs">
        <table class="w-full">
            <thead>
                <tr class="text-xs uppercase text-content-light bg-base-300 border-b border-primary/30">
                    <th class="px-4 py-3 text-left">Personaje</th>
                    <th class="px-4 py-3 text-left">Discord ID</th>
                    <th class="px-4 py-3 text-left">Motivo</th>
                    <th class="px-4 py-3 text-left">Baneado por</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary/30">
                @forelse($players as $p)
                    <tr class="hover:bg-base-200/50">
                        <td class="px-4 py-3 text-sm">
                            <div class="font-bold text-error">{{ $p->albion_player_name }}</div>
                            <div class="text-xs text-content-light/50 font-mono">{{ $p->albion_player_id }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($p->discord_user_id)
                                <code class="text-xs bg-[#5865F2]/20 text-[#5865F2] px-1 rounded">{{ $p->discord_user_id }}</code>
                            @else
                                <span class="text-content-light/40 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-content-light/80">
                            {{ $p->reason }}
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <div class="text-content-light">{{ $p->banned_by_name }}</div>
                            <div class="text-content-light/50">{{ $p->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="confirmDelete({{ $p->id }})" class="text-success hover:text-success/80 text-xs" title="Perdonar / Remover">
                                Perdonar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-content-light/70">
                            La lista negra está vacía. Todo en orden.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $players->links('vendor.pagination.tailwind') }}</div>
    </div>

    <!-- Modal Crear -->
    <x-dialog-modal wire:model="showModal" maxWidth="lg">
        <x-slot name="title">Añadir a la Lista Negra</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label value="ID del personaje en Albion *" />
                    <div class="flex gap-2 mt-1">
                        <x-input type="text" wire:model="albion_player_id" class="flex-1" placeholder="Ej: XXXX..." />
                        <x-secondary-button wire:click="buscarEnAlbion" wire:loading.attr="disabled">
                            🔍 Validar
                        </x-secondary-button>
                    </div>
                    @error('albion_player_id') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label value="Nombre del personaje *" />
                    <x-input type="text" wire:model="albion_player_name" class="mt-1 w-full bg-base-200/50" readonly />
                    @error('albion_player_name') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label value="Discord ID (Opcional)" />
                    <x-input type="text" wire:model="discord_user_id" class="mt-1 w-full" placeholder="Ej: 123456789" />
                    <p class="text-xs text-content-light/60 mt-1">Útil para bloquear su cuenta en el bot.</p>
                    @error('discord_user_id') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label value="Motivo de la expulsión *" />
                    <textarea wire:model="reason" rows="3" class="mt-1 w-full border rounded-md bg-base-200 text-content-light px-3 py-2" placeholder="Ej: Robo de cofres en HO, toxicidad extrema..."></textarea>
                    @error('reason') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)">Cancelar</x-secondary-button>
            <x-danger-button class="ml-2" wire:click="save">Añadir a Lista Negra</x-danger-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Modal Confirmar Eliminación -->
    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">Remover de Lista Negra</x-slot>
        <x-slot name="content">
            ¿Estás seguro de que deseas perdonar a este jugador y permitirle volver a registrarse?
            Esta acción eliminará el registro de la base de datos.
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('confirmingDeletion', false)">Cancelar</x-secondary-button>
            <x-button class="ml-2 bg-success hover:bg-success/80" wire:click="delete">Sí, perdonar</x-button>
        </x-slot>
    </x-confirmation-modal>
</div>
