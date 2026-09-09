<div>
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-content-accent">🏆 Ranking de Puntos</h2>
        <div class="flex items-center gap-4">
            <x-input type="search" wire:model.live="search" placeholder="Buscar por nombre..." class="w-64" />
            <select wire:model.live="perPage" class="bg-base-200 border border-base-300 rounded px-3 py-2 pr-8 text-content-light min-w-[70px] focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 appearance-none">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="bg-base-300 rounded-lg overflow-hidden border border-base-300">
        <table class="w-full">
            <thead>
                <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                    <th class="px-4 py-3 text-center">#</th>
                    <th class="px-4 py-3">Personaje</th>
                    <th class="px-4 py-3 text-right">Puntos</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rankings as $index => $item)
                    <tr class="border-b border-base-200 hover:bg-base-200/50 transition-colors">
                        <td class="px-4 py-3 text-center text-content-light text-sm">
                            {{ $rankings->firstItem() + $index }}
                        </td>
                        <td class="px-4 py-3 text-content-light text-sm flex items-center gap-2">
                            <span>{{ $item->personaje_name }}</span>
                            @if($item->miembro)
                                <span class="bg-primary/10 text-primary text-xs px-2 py-0.5 rounded-full">Miembro</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-content-light text-sm font-semibold">
                            {{ number_format($item->total_points, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-content-light/70">
                            No hay puntos registrados aún.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rankings->links('vendor.pagination.tailwind') }}
    </div>
</div>
