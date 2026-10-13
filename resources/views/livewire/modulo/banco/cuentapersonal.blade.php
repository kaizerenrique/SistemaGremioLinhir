<div class="space-y-6">

    @if($personajes->isEmpty())
        {{-- Sin personajes: invitar a registrarse --}}
        <div class="bg-base-300 sm:rounded-lg p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-primary/40 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-xl font-semibold text-content-accent mb-2">Cuenta personal</h3>
            <p class="text-content-light/70 mb-6">
                No tienes un personaje vinculado a tu cuenta todavía.
                Registra uno para comenzar a recibir pagos y ver tu saldo.
            </p>
            <a href="{{ route('personajesregistro') }}"
               class="inline-block bg-primary hover:bg-hover-primary text-white px-6 py-2 rounded-lg font-semibold transition">
                Registrar personaje
            </a>
        </div>
    @else
        {{-- Cabecera con selector de personaje --}}
        <div class="bg-base-300 sm:rounded-lg p-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs text-content-light/60 uppercase mb-1">Personaje</label>
                    <select wire:model.live="personajeActivoId"
                            class="w-full bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light
                                   focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30">
                        @foreach($personajes as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->Name }}{{ $p->miembro ? ' · Miembro' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="text-xs text-content-light/60">
                    {{ $personajes->count() }} {{ $personajes->count() === 1 ? 'personaje' : 'personajes' }}
                </div>
            </div>
        </div>

        {{-- Cards de saldo --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-base-300 rounded-lg border border-primary/30 p-6">
                <div class="text-xs uppercase text-content-light/60">Saldo actual</div>
                <div class="text-3xl font-bold text-accent mt-1">
                    {{ number_format($saldo, 0, ',', '.') }}
                </div>
                <div class="text-xs text-content-light/60 mt-1">plata</div>
            </div>

            <div class="bg-base-300 rounded-lg border border-success/30 p-6">
                <div class="text-xs uppercase text-content-light/60">Total ingresos</div>
                <div class="text-2xl font-bold text-success mt-1">
                    +{{ number_format($ingresos, 0, ',', '.') }}
                </div>
                <div class="text-xs text-content-light/60 mt-1">plata acumulada</div>
            </div>

            <div class="bg-base-300 rounded-lg border border-error/30 p-6">
                <div class="text-xs uppercase text-content-light/60">Total egresos</div>
                <div class="text-2xl font-bold text-error mt-1">
                    -{{ number_format($egresos, 0, ',', '.') }}
                </div>
                <div class="text-xs text-content-light/60 mt-1">plata retirada</div>
            </div>
        </div>

        {{-- Filtros y tabla de transacciones --}}
        <div class="bg-base-300 sm:rounded-lg">
            <div class="flex flex-wrap items-center gap-3 px-4 py-3 border-b border-primary/20">
                <div class="flex-1 min-w-[200px]">
                    <h3 class="font-semibold text-base text-content-light">Movimientos</h3>
                </div>
                <x-input type="search" wire:model.live="search"
                         placeholder="Buscar concepto, referencia..." class="w-64" />
                <select wire:model.live="tipoFilter"
                        class="text-sm bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light">
                    <option value="">Todos</option>
                    <option value="ingreso">Solo ingresos</option>
                    <option value="egreso">Solo egresos</option>
                </select>
                <select wire:model.live="perPage"
                        class="text-sm bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                            <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('created_at')">
                                <div class="flex items-center">
                                    Fecha
                                    @if($sortField === 'created_at') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>
                            <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('tipo')">
                                <div class="flex items-center">
                                    Tipo
                                    @if($sortField === 'tipo') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>
                            <th class="px-4 py-3">Concepto</th>
                            <th class="px-4 py-3 text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('monto')">
                                <div class="flex items-center justify-end">
                                    Monto
                                    @if($sortField === 'monto') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>
                            <th class="px-4 py-3">Referencia</th>
                            <th class="px-4 py-3">Autorizado por</th>
                        </tr>
                    </thead>
                    <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                        @forelse($transacciones as $t)
                            <tr class="hover:bg-base-200/50">
                                <td class="px-4 py-3 text-xs whitespace-nowrap">
                                    {{ $t->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $t->tipo === 'ingreso'
                                            ? 'bg-success/10 text-success border border-success/20'
                                            : 'bg-error/10 text-error border border-error/20' }}">
                                        {{ $t->tipo === 'ingreso' ? 'INGRESO' : 'EGRESO' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs">{{ $t->concepto }}</td>
                                <td class="px-4 py-3 text-xs font-semibold text-right
                                    {{ $t->tipo === 'ingreso' ? 'text-success' : 'text-error' }}">
                                    {{ $t->tipo === 'ingreso' ? '+' : '-' }}{{ number_format($t->monto, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if($t->referencia)
                                        <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">
                                            {{ $t->referencia }}
                                        </span>
                                    @else
                                        <span class="text-content-light/50">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $t->autorizado_por ?? 'Sistema' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-content-light/70">
                                    <div class="flex flex-col items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-content-light/30 mb-2"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm">
                                            @if($search || $tipoFilter)
                                                No hay movimientos que coincidan con el filtro.
                                            @else
                                                Aún no tienes movimientos. Los pagos por actividades aparecerán aquí.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transacciones->hasPages())
                <div class="mx-4 px-4 py-3 border-t border-primary/20">
                    {{ $transacciones->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    @endif

</div>