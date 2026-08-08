<div>
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
                            <tr class="hover:bg-base-200/50">
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
                                <td colspan="6" class="px-4 py-6 text-center text-content-light/70">
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
</div>
