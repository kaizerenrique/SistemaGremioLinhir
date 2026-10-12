<div>
    <div class="flex items-center justify-between gap-3 px-4 py-3">
        <h3 class="font-semibold text-base text-content-light">Solicitudes de retiro</h3>
        <select wire:model.live="filter"
                class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm">
            <option value="pending">Pendientes</option>
            <option value="approved">Aprobadas</option>
            <option value="rejected">Rechazadas</option>
            <option value="all">Todas</option>
        </select>
    </div>

    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Miembro</th>
                        <th class="px-4 py-3 text-right">Monto</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                    @forelse($requests as $req)
                        <tr class="hover:bg-base-200/50">
                            <td class="px-4 py-3 text-xs">#{{ $req->id }}</td>
                            <td class="px-4 py-3 text-sm">
                                {{ $req->user_name }}
                                <div class="text-xs text-content-light/60">{{ $req->personaje?->Name }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-accent">
                                {{ number_format($req->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @php
                                    $badge = match($req->status) {
                                        'pending'  => 'bg-warning/20 text-warning',
                                        'approved' => 'bg-success/20 text-success',
                                        'rejected' => 'bg-error/20 text-error',
                                    };
                                @endphp
                                <span class="{{ $badge }} px-2 py-1 rounded-full">{{ ucfirst($req->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-content-light/70">
                                {{ $req->created_at->format('d/m/Y H:i') }}
                                @if($req->reviewed_at)
                                    <div class="text-content-light/50">
                                        Revisada: {{ $req->reviewed_at->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-right">
                                @if($req->status === 'pending')
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="approve({{ $req->id }})"
                                                class="text-success hover:text-success/80" title="Aprobar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                        <button wire:click="openRejectModal({{ $req->id }})"
                                                class="text-error hover:text-error/80" title="Rechazar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-content-light/70">
                                No hay solicitudes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mx-4 px-4 py-3">
            {{ $requests->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    <x-dialog-modal wire:model="showRejectModal">
        <x-slot name="title">Rechazar solicitud</x-slot>
        <x-slot name="content">
            <p class="text-sm text-content-light mb-3">
                Rechazando solicitud de
                <strong>{{ $rejectingRequest?->user_name }}</strong>
                por <strong>{{ $rejectingRequest?->amount }}</strong> silver.
            </p>
            <x-label for="rejectReason" value="Motivo del rechazo" />
            <x-input id="rejectReason" type="text" wire:model="rejectReason" class="mt-1 block w-full" />
            @error('rejectReason') <span class="text-error text-sm">{{ $message }}</span> @enderror
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="cancelReject">Cancelar</x-secondary-button>
            <x-danger-button class="ml-2" wire:click="reject">Rechazar</x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
