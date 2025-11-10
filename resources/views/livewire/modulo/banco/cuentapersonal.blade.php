<div>
    <div class="bg-base-300 sm:rounded-lg">
        <div class="flex flex-wrap items-center px-4 py-2">
            <div class="relative w-full max-w-full flex-grow flex-1">
                <h3 class="font-semibold text-base text-content-light leading-tight">
                    Cuenta Personal - {{ $personaje->Name ?? 'No encontrado' }}
                </h3>
            </div>
            <div class="flex flex-col items-center w-full max-w-xl">
                <x-input class="block mt-1 w-100" type="search" wire:model.live="search" placeholder="Buscar en transacciones..." />
            </div>
            <div class="relative w-full max-w-full flex-grow flex-1 text-center mt-1 mx-5">
                <!-- Información de saldo -->
                @if($personaje)
                    <div class="inline-flex items-center bg-primary/10 px-3 py-1 rounded-full">
                        <span class="text-xs font-semibold text-primary">Saldo: {{ number_format($saldo, 2) }} Plata</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Filtros adicionales -->
        <div class="flex flex-wrap items-center px-4 py-2 gap-4 border-t border-primary/30">
            <div class="flex items-center space-x-4">
                <label class="text-xs font-semibold text-content-light">Filtrar por:</label>
                <select wire:model.live="tipoFilter" class="text-xs bg-base-200 border border-primary/20 rounded px-2 py-1 text-content-light">
                    <option value="">Todos los movimientos</option>
                    <option value="ingreso">Solo ingresos</option>
                    <option value="egreso">Solo egresos</option>
                </select>
                <select wire:model.live="perPage" class="text-xs bg-base-200 border border-primary/20 rounded px-2 py-1 text-content-light">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>
        </div>

        @if(!$personaje)
            <div class="px-4 py-6 text-center">
                <div class="bg-warning/10 border border-warning/20 rounded-lg p-4 max-w-md mx-auto">
                    <p class="text-warning">No tienes un personaje asociado a tu cuenta.</p>
                </div>
            </div>
        @else
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
                <div class="w-full overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                                <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('created_at')">
                                    <div class="flex items-center">
                                        Fecha
                                        @if($sortField === 'created_at')
                                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('tipo')">
                                    <div class="flex items-center">
                                        Tipo
                                        @if($sortField === 'tipo')
                                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3">Concepto</th>
                                <th class="px-4 py-3 cursor-pointer hover:bg-base-200" wire:click="sortBy('monto')">
                                    <div class="flex items-center">
                                        Monto
                                        @if($sortField === 'monto')
                                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3">Referencia</th>
                                <th class="px-4 py-3">Autorizado Por</th>
                            </tr>
                        </thead>
                        <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                            @forelse($transacciones as $transaccion)
                                <tr class="text-content-light hover:bg-base-200/50">
                                    <td class="px-4 py-3 text-xs whitespace-nowrap">
                                        {{ $transaccion->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $transaccion->tipo === 'ingreso' 
                                                ? 'bg-success/10 text-success border border-success/20' 
                                                : 'bg-error/10 text-error border border-error/20' }}">
                                            {{ $transaccion->tipo === 'ingreso' ? 'INGRESO' : 'EGRESO' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $transaccion->concepto }}
                                    </td>
                                    <td class="px-4 py-3 text-xs font-semibold 
                                        {{ $transaccion->tipo === 'ingreso' ? 'text-success' : 'text-error' }}">
                                        {{ $transaccion->tipo === 'ingreso' ? '+' : '-' }}
                                        {{ number_format($transaccion->monto, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if($transaccion->referencia)
                                            <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">
                                                {{ $transaccion->referencia }}
                                            </span>
                                        @else
                                            <span class="text-content-light/50">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $transaccion->autorizado_por ?? 'Sistema' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-content-light/70">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-content-light/30 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-sm">No se encontraron transacciones</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                @if($transacciones->hasPages())
                    <div class="mx-4 border-t border-primary/30">
                        <div class="px-4 py-3">
                            {{ $transacciones->links('vendor.pagination.tailwind') }}
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
