<div>
    {{-- Aviso si intenta ir a Multi con <2 batallas --}}
    @if (session('warning'))
        <div class="mx-4 mt-2 bg-warning/10 border border-warning/30 text-warning rounded p-3 text-sm">
            {{ session('warning') }}
        </div>
    @endif

    <div class="bg-base-300 sm:rounded-lg">
        <div class="flex flex-wrap items-center px-4 py-2">
            <div class="relative w-full max-w-full flex-grow flex-1">
                <h3 class="font-semibold text-base text-content-light leading-tight">
                    Batallas
                </h3>
            </div>
            <div class="flex flex-col items-center w-full max-w-xl">
                <x-input class="block mt-1 w-100" type="search" wire:model.live="search" placeholder="Buscar por ID..." />
            </div>
            <div class="relative w-full max-w-full flex-grow flex-1 text-center mt-1 mx-5">
                <select wire:model.live="perPage" class="w-32 border rounded-md shadow-sm bg-base-200 text-content-light px-3 py-2">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">

                            {{-- ▼▼▼ Checkbox "seleccionar página" ▼▼▼ --}}
                            <th class="px-2 py-3 w-8">
                                @php
                                    $pageIds = $battles->pluck('id')->map(fn($id) => (string) $id)->toArray();
                                    $allPageSelected = !empty($pageIds)
                                        && empty(array_diff($pageIds, $selectedBattles));
                                @endphp
                                <input type="checkbox"
                                       wire:click="toggleSelectAll({{ json_encode($pageIds) }})"
                                       @checked($allPageSelected)
                                       class="rounded bg-base-200 border-base-100 text-primary focus:ring-primary cursor-pointer"
                                       title="Seleccionar todos los de esta página">
                            </th>

                            <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('id')">
                                ID
                                @if($sortField === 'id') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('start_time')">
                                Fecha (UTC)
                                @if($sortField === 'start_time') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('total_kills')">
                                Kills
                                @if($sortField === 'total_kills') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('total_fame')">
                                Fama Total
                                @if($sortField === 'total_fame') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th class="px-4 py-3 text-center">
                                Participantes Linhir
                            </th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                        @forelse($battles as $battle)
                            @php $isSelected = in_array((string) $battle->id, $selectedBattles, true); @endphp
                            <tr class="hover:bg-base-200/50 {{ $isSelected ? 'bg-primary/5' : '' }}"
                                wire:key="battle-row-{{ $battle->id }}">

                                {{-- ▼▼▼ Checkbox por fila ▼▼▼ --}}
                                <td class="px-2 py-3 w-8">
                                    <input type="checkbox"
                                           wire:model.live="selectedBattles"
                                           value="{{ $battle->id }}"
                                           class="rounded bg-base-200 border-base-100 text-primary focus:ring-primary cursor-pointer">
                                </td>

                                <td class="px-4 py-3 text-sm">{{ $battle->id }}</td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $battle->start_time ? $battle->start_time->format('d/m/Y H:i') . ' UTC' : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm">{{ number_format($battle->total_kills) }}</td>
                                <td class="px-4 py-3 text-sm">{{ number_format($battle->total_fame, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-primary/20 text-primary">
                                        {{ $battle->linhir_participants ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('battles.show', $battle->id) }}" class="text-primary hover:text-accent">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-content-light/70">
                                    No se encontraron batallas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mx-4 px-4 py-3">
                {{ $battles->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- BARRA FLOTANTE DE CONCATENACIÓN                           --}}
    {{-- ========================================================= --}}
    @if(count($selectedBattles) > 0)
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 animate-fade-in">
            <div class="bg-base-200 border-2 border-accent rounded-2xl shadow-2xl px-4 py-3 flex items-center gap-4">

                <div class="flex items-center gap-2 text-content-light text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-accent" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span>
                        <strong class="text-accent text-lg">{{ count($selectedBattles) }}</strong>
                        <span class="text-content-light/70">
                            {{ count($selectedBattles) === 1 ? 'reporte seleccionado' : 'reportes seleccionados' }}
                        </span>
                    </span>
                </div>

                <button wire:click="clearSelection"
                        class="text-content-light/60 hover:text-error text-xs underline transition">
                    Limpiar
                </button>

                <button wire:click="goToMulti"
                        wire:loading.attr="disabled"
                        wire:target="goToMulti"
                        @if(count($selectedBattles) < 2) disabled @endif
                        class="bg-accent hover:bg-hover-accent text-base-100 px-5 py-2 rounded-lg
                               font-bold flex items-center gap-2 transition
                               disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                    <span wire:loading.remove wire:target="goToMulti">Concatenar</span>
                    <span wire:loading wire:target="goToMulti">Cargando…</span>
                </button>

            </div>
        </div>
    @endif
</div>