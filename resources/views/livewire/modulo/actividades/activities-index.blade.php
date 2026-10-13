<div>
    <!-- Encabezado + filtros -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
        <h3 class="font-semibold text-base text-content-light">Actividades financieras</h3>
        <div class="flex items-center gap-2 flex-wrap">
            <x-input type="search" wire:model.live="buscar" placeholder="Buscar por nombre..." class="w-56" />
            <select wire:model.live="modo"
                    class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm">
                <option value="">Todos</option>
                <option value="normal">Normales</option>
                <option value="pro_guild">Pro-gremio</option>
            </select>
            <x-input type="date" wire:model.live="desde" class="w-40" />
            <x-input type="date" wire:model.live="hasta" class="w-40" />

            @can('Gestionar Actividades')
                <x-button wire:click="openForm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5H4a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5V4a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Nueva
                </x-button>
            @endcan
        </div>
    </div>

    <!-- Tabla -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Inicio</th>
                        <th class="px-4 py-3 text-right">Silver líq.</th>
                        <th class="px-4 py-3 text-right">Items</th>
                        <th class="px-4 py-3 text-right">Distribuido</th>
                        <th class="px-4 py-3 text-center">Particip.</th>
                        <th class="px-4 py-3">Modo</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                    @forelse($activities as $a)
                        <tr class="hover:bg-base-200/50">
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">{{ $a->name }}</div>
                                @if($a->cta)
                                    <div class="text-xs text-content-light/60">CTA: {{ $a->cta->title }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $a->start_time->format('d/m/Y H:i') }} UTC
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                {{ number_format($a->liquid_silver, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                {{ number_format($a->items_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-accent font-semibold">
                                {{ number_format($a->total_distributed, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">{{ $a->participants_count }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if($a->pro_guild)
                                    <span class="bg-accent/20 text-accent px-2 py-1 rounded-full">Pro-gremio</span>
                                @else
                                    <span class="bg-primary/20 text-primary px-2 py-1 rounded-full">Normal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="openDetail({{ $a->id }})"
                                        class="text-primary hover:text-accent text-sm">
                                    Ver detalle
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-content-light/70">
                                No hay actividades registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mx-4 px-4 py-3">
            {{ $activities->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL DETALLE                                                 -->
    <!-- ============================================================ -->
    <x-dialog-modal wire:model="showDetail" maxWidth="2xl">
        <x-slot name="title">Detalle de actividad</x-slot>
        <x-slot name="content">
            @if($detailActivity)
                <div class="space-y-4">
                    <div>
                        <div class="text-lg font-semibold text-content-accent">{{ $detailActivity->name }}</div>
                        <div class="text-sm text-content-light/70">
                            {{ $detailActivity->start_time->format('d/m/Y H:i') }} – {{ $detailActivity->end_time->format('H:i') }} UTC
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="bg-base-200 p-3 rounded">
                            <div class="text-xs text-content-light/60">Silver líquido</div>
                            <div class="font-semibold">{{ number_format($detailActivity->liquid_silver, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-base-200 p-3 rounded">
                            <div class="text-xs text-content-light/60">Valor items</div>
                            <div class="font-semibold">{{ number_format($detailActivity->items_value, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-base-200 p-3 rounded">
                            <div class="text-xs text-content-light/60">Tasa gremio ({{ $detailActivity->guild_tax_rate }}%)</div>
                            <div class="font-semibold">{{ number_format($detailActivity->guild_tax_amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-base-200 p-3 rounded">
                            <div class="text-xs text-content-light/60">Gestión ({{ $detailActivity->management_tax_rate }}%)</div>
                            <div class="font-semibold">{{ number_format($detailActivity->management_tax_amount, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    <div class="bg-primary/10 border border-primary/30 p-3 rounded text-center">
                        <div class="text-xs uppercase text-content-light/70">Total distribuido</div>
                        <div class="text-2xl font-bold text-accent">
                            {{ number_format($detailActivity->total_distributed, 0, ',', '.') }} silver
                        </div>
                    </div>

                    @if($detailActivity->pro_guild)
                        <div class="text-sm text-accent text-center">
                            🏛️ Actividad pro-gremio — el total fue a la cuenta común.
                        </div>
                    @endif

                    @if($detailActivity->participants->count())
                        <div>
                            <div class="text-sm font-semibold text-content-light mb-2">Participantes</div>
                            <div class="max-h-60 overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-xs text-content-light/60 uppercase">
                                        <tr>
                                            <th class="text-left py-1">Nombre</th>
                                            <th class="text-right py-1">%</th>
                                            <th class="text-right py-1">Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($detailActivity->participants as $p)
                                            <tr class="border-t border-base-200">
                                                <td class="py-1.5">{{ $p->user_name }}</td>
                                                <td class="py-1.5 text-right">{{ $p->participation_percent }}%</td>
                                                <td class="py-1.5 text-right text-accent font-semibold">
                                                    +{{ number_format($p->share, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-content-light/70">Cargando...</div>
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="closeDetail">Cerrar</x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    <!-- ============================================================ -->
    <!-- MODAL CREAR                                                   -->
    <!-- ============================================================ -->
    <x-dialog-modal wire:model="showForm" maxWidth="2xl">
        <x-slot name="title">Nueva actividad</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="name" value="Nombre de la actividad" />
                    <x-input id="name" type="text" wire:model="name" class="mt-1 block w-full" />
                    @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <x-label for="start_time" value="Inicio (UTC)" />
                        <x-input id="start_time" type="datetime-local" wire:model="start_time" class="mt-1 block w-full" />
                        @error('start_time') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label for="end_time" value="Fin (UTC)" />
                        <x-input id="end_time" type="datetime-local" wire:model="end_time" class="mt-1 block w-full" />
                        @error('end_time') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <x-label for="liquid_silver" value="Silver líquido" />
                        <x-input id="liquid_silver" type="number" wire:model="liquid_silver" min="0" class="mt-1 block w-full" />
                        @error('liquid_silver') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label for="items_value" value="Valor estimado de items" />
                        <x-input id="items_value" type="number" wire:model="items_value" min="0" class="mt-1 block w-full" />
                        @error('items_value') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 bg-base-200 rounded">
                    <input type="checkbox" id="pro_guild" wire:model.live="pro_guild"
                           class="rounded bg-base-100 border-base-100 text-primary focus:ring-primary">
                    <label for="pro_guild" class="text-content-light text-sm cursor-pointer">
                        <span class="font-semibold">Pro-gremio</span>
                        — Todo el botín va a la cuenta común, sin distribuir a participantes.
                    </label>
                </div>

                @if(!$pro_guild)
                    <div class="border-t border-base-300 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <x-label value="Participantes" />
                            <div class="flex gap-2 items-center">
                                <select wire:model="selectedPersonajeId"
                                        class="bg-base-200 border border-base-300 rounded px-2 py-1.5 text-content-light text-sm max-w-[240px]">
                                    <option value="">— Seleccionar personaje —</option>
                                    @foreach($personajesDisponibles as $p)
                                        <option value="{{ $p->id }}">{{ $p->Name }}</option>
                                    @endforeach
                                </select>
                                <x-secondary-button type="button" wire:click="addParticipant">
                                    + Añadir
                                </x-secondary-button>
                            </div>
                        </div>

                        @error('participants') <span class="text-error text-sm">{{ $message }}</span> @enderror

                        @if(count($participants))
                            <div class="space-y-2 mt-3 max-h-56 overflow-y-auto pr-1">
                                @foreach($participants as $index => $row)
                                    @php $p = $personajesDisponibles->firstWhere('id', $row['personaje_id']); @endphp
                                    <div class="flex items-center gap-2 bg-base-200/50 p-2 rounded" wire:key="p-{{ $index }}">
                                        <div class="flex-1 text-sm text-content-light truncate">
                                            {{ $p?->Name ?? 'Desconocido' }}
                                        </div>
                                        <x-input type="number" step="0.01" min="0"
                                                 wire:model="participants.{{ $index }}.percent"
                                                 class="w-24 text-right" />
                                        <span class="text-content-light/60 text-sm">%</span>
                                        <button type="button" wire:click="removeParticipant({{ $index }})"
                                                class="text-error hover:text-error/80">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-content-light/60 mt-2">
                                Añade al menos un participante para distribuir el botín.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="closeForm">Cancelar</x-secondary-button>
            <x-button class="ml-2" wire:click="save" wire:loading.attr="disabled">
                Registrar
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>