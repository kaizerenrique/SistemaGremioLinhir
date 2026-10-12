<div>
    <!-- Tarjeta de saldo -->
    <div class="bg-base-300 border border-primary/30 rounded-lg p-6 mb-6 flex items-center justify-between">
        <div>
            <div class="text-xs uppercase text-content-light/60">Saldo actual del gremio</div>
            <div class="text-3xl font-bold text-accent mt-1">
                {{ number_format($saldo, 0, ',', '.') }} silver
            </div>
        </div>
        <div class="flex gap-2">
            <x-button wire:click="openModal('ingreso')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                </svg>
                Ingreso
            </x-button>
            <x-danger-button wire:click="openModal('egreso')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                </svg>
                Egreso
            </x-danger-button>
        </div>
    </div>

    <!-- Filtro -->
    <div class="flex items-center gap-2 px-4 py-2">
        <select wire:model.live="filter"
                class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm">
            <option value="">Todos los movimientos</option>
            <option value="inicial">Inicial</option>
            <option value="ingreso">Ingresos</option>
            <option value="egreso">Egresos</option>
        </select>
    </div>

    <!-- Tabla -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Motivo</th>
                        <th class="px-4 py-3 text-right">Monto</th>
                        <th class="px-4 py-3">Registrado por</th>
                    </tr>
                </thead>
                <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-base-200/50">
                            <td class="px-4 py-3 text-xs">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-xs">
                                @php
                                    $badge = match($tx->type) {
                                        'inicial' => 'bg-neutro/20 text-neutro',
                                        'ingreso' => 'bg-success/20 text-success',
                                        'egreso'  => 'bg-error/20 text-error',
                                    };
                                @endphp
                                <span class="{{ $badge }} px-2 py-1 rounded-full">{{ ucfirst($tx->type) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $tx->reason }}
                                @if($tx->image_url)
                                    <a href="{{ $tx->image_url }}" target="_blank"
                                       class="ml-1 text-primary hover:text-accent">[ver]</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-right {{ $tx->type === 'egreso' ? 'text-error' : 'text-success' }}">
                                {{ $tx->type === 'egreso' ? '-' : '+' }}
                                {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-content-light/70">{{ $tx->performed_by_name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-content-light/70">
                                No hay movimientos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mx-4 px-4 py-3">
            {{ $transactions->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    <!-- MODAL registrar movimiento -->
    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ $type === 'egreso' ? 'Registrar egreso' : 'Registrar ingreso' }}
        </x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="amount" value="Monto (silver)" />
                    <x-input id="amount" type="number" wire:model="amount" min="1" class="mt-1 block w-full" />
                    @error('amount') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-label for="reason" value="Motivo" />
                    <x-input id="reason" type="text" wire:model="reason" class="mt-1 block w-full" />
                    @error('reason') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-label for="imageUrl" value="URL de comprobante (opcional)" />
                    <x-input id="imageUrl" type="url" wire:model="imageUrl" class="mt-1 block w-full" />
                    @error('imageUrl') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="cancel">Cancelar</x-secondary-button>
            <x-button class="ml-2" wire:click="save">Registrar</x-button>
        </x-slot>
    </x-dialog-modal>
</div>
