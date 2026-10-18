<div>
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
        <h3 class="font-semibold text-base text-content-light">Actividades (CTAs)</h3>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- NUEVO: Botón crear CTA (solo para quien tenga el permiso) --}}
            @can('Gestionar CTAs')
                <a href="{{ route('ctas.create') }}"
                    class="inline-flex items-center bg-primary hover:bg-hover-primary
                          text-white px-4 py-2 rounded text-sm font-semibold transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 3a1 1 0 00-1 1v5H4a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5V4a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Nueva CTA
                </a>
            @endcan

            <select wire:model.live="filter"
                class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm">
                <option value="scheduled">Programadas</option>
                <option value="past">Pasadas</option>
                <option value="all">Todas</option>
            </select>
            <x-input type="search" wire:model.live="buscar" placeholder="Buscar..." class="w-56" />
        </div>
    </div>

    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr
                        class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-3">Título</th>
                        <th class="px-4 py-3">Inicio (UTC)</th>
                        <th class="px-4 py-3">Composición</th>
                        <th class="px-4 py-3 text-center">Inscritos</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                    @forelse($ctas as $cta)
                        <tr class="hover:bg-base-200/50">
                            <td class="px-4 py-3 text-sm font-medium">{{ $cta->title }}</td>
                            <td class="px-4 py-3 text-sm">
                                <time datetime="{{ $cta->start_time->toIso8601String() }}">
                                    {{ $cta->start_time->format('d/m/Y H:i') }} UTC
                                </time>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $cta->composition?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ $cta->confirmed_count }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $colors = [
                                        'scheduled' => 'bg-info/20 text-info',
                                        'cancelled' => 'bg-error/20 text-error',
                                        'completed' => 'bg-neutro/20 text-neutro',
                                    ];
                                @endphp
                                <span
                                    class="{{ $colors[$cta->status] ?? 'bg-base-200' }} px-2 py-1 rounded-full text-xs">
                                    {{ ucfirst($cta->status) }}
                                </span>
                                @if ($cta->paid)
                                    <span
                                        class="ml-1 bg-success/20 text-success px-2 py-1 rounded-full text-xs">Pagada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <a href="{{ route('ctas.show', $cta->id) }}" class="text-primary hover:text-accent">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-content-light/70">
                                No hay actividades que coincidan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mx-4 px-4 py-3">
            {{ $ctas->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>
