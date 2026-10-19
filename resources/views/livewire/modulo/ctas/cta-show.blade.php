<div class="space-y-6">
    @if(!$cta)
        <div class="bg-error/10 border border-error/30 rounded-lg p-4 text-error">
            CTA no encontrada.
        </div>
    @else

        {{-- ============================================================ --}}
        {{-- MENSAJES DE FEEDBACK                                        --}}
        {{-- ============================================================ --}}
        @if($successMessage)
            <div class="bg-success/10 border border-success/30 rounded-lg p-3 text-success text-sm flex items-center justify-between">
                <span>{{ $successMessage }}</span>
                <button wire:click="$set('successMessage', null)" class="text-success/60 hover:text-success">✕</button>
            </div>
        @endif
        @if($errorMessage)
            <div class="bg-error/10 border border-error/30 rounded-lg p-3 text-error text-sm flex items-center justify-between">
                <span>{{ $errorMessage }}</span>
                <button wire:click="$set('errorMessage', null)" class="text-error/60 hover:text-error">✕</button>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- CABECERA CON ACCIONES                                        --}}
        {{-- ============================================================ --}}
        <div class="bg-base-300 rounded-lg border border-base-300 p-6">
            <div class="flex justify-between items-start mb-4 gap-3 flex-wrap">
                <div>
                    <h1 class="text-2xl font-bold text-content-accent">{{ $cta->title }}</h1>
                    <p class="text-content-light/80 text-sm">📍 {{ $cta->location }}</p>
                </div>

                @php
                    $badgeColors = [
                        'scheduled' => 'bg-info/20 text-info',
                        'cancelled' => 'bg-error/20 text-error',
                        'completed' => 'bg-neutro/20 text-neutro',
                    ];
                @endphp
                <span class="{{ $badgeColors[$cta->status] ?? 'bg-base-200' }} px-3 py-1 rounded-full text-xs uppercase">
                    {{ $cta->status }}
                </span>
            </div>

            {{-- Acciones de staff --}}
            @can('Gestionar CTAs')
                <div class="flex flex-wrap gap-2 pb-4 border-b border-base-200">
                    @if($cta->status === 'scheduled')
                        <button wire:click="openEditBasicModal"
                                class="inline-flex items-center bg-info hover:bg-info/80 text-white px-3 py-1.5 rounded text-sm font-semibold transition">
                            ✏️ Editar info
                        </button>
                        <button wire:click="openCancelModal"
                                class="inline-flex items-center bg-error hover:bg-error/80 text-white px-3 py-1.5 rounded text-sm font-semibold transition">
                            ❌ Cancelar CTA
                        </button>
                    @endif
                    <button wire:click="syncToDiscord"
                            wire:loading.attr="disabled"
                            wire:target="syncToDiscord"
                            class="inline-flex items-center bg-primary hover:bg-hover-primary text-white px-3 py-1.5 rounded text-sm font-semibold transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="syncToDiscord">🔄 Sincronizar Discord</span>
                        <span wire:loading wire:target="syncToDiscord">Sincronizando…</span>
                    </button>
                </div>
            @endcan

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <div class="text-xs text-content-light/60 uppercase">Inicio</div>
                    <div class="text-content-light">{{ $cta->start_time->format('d/m/Y H:i') }} UTC</div>
                    <div class="text-xs text-content-light/60">({{ $cta->start_time->diffForHumans() }})</div>
                </div>
                <div>
                    <div class="text-xs text-content-light/60 uppercase">Fin</div>
                    <div class="text-content-light">{{ $cta->end_time->format('d/m/Y H:i') }} UTC</div>
                </div>
                <div>
                    <div class="text-xs text-content-light/60 uppercase">Recordatorio</div>
                    <div class="text-content-light">
                        {{ $cta->reminder_minutes }} min antes
                        @if($cta->reminder_sent)
                            <span class="ml-1 text-success text-xs">✓ enviado</span>
                        @else
                            <span class="ml-1 text-content-light/50 text-xs">pendiente</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-base-300">
                <div class="text-xs text-content-light/60 uppercase mb-1">Composición</div>
                <div class="text-content-light font-medium">{{ $cta->composition->name }}</div>
                @if($cta->discord_message_id)
                    <div class="text-xs text-content-light/50 mt-1">
                        Publicada en Discord · msg ID: <code>{{ $cta->discord_message_id }}</code>
                    </div>
                @else
                    <div class="text-xs text-warning mt-1">
                        ⚠️ No publicada en Discord. Usa "Sincronizar Discord".
                    </div>
                @endif
            </div>

            @if($cta->image_url)
                <div class="mt-4">
                    <img src="{{ $cta->image_url }}" alt="Imagen de la CTA"
                         class="w-full max-w-md h-auto rounded-lg border border-base-300">
                </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- OCUPACIÓN DE ARMAS (con acciones)                            --}}
        {{-- ============================================================ --}}
        <div class="bg-base-300 rounded-lg border border-base-300 p-6">
            <h3 class="font-semibold text-content-accent mb-4">🎭 Ocupación de armas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($cta->composition->weapons as $weapon)
                    @php $asignado = $ocupacion[$weapon->id] ?? null; @endphp
                    <div class="flex items-center gap-2 p-2 rounded border
                                {{ $asignado ? 'border-primary/40 bg-primary/5' : 'border-base-200' }}">
                        <div class="shrink-0">{!! $weapon->icon_html !!}</div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-content-light truncate">{{ $weapon->weapon_name }}</div>
                            <div class="text-xs {{ $asignado ? 'text-primary' : 'text-content-light/50' }}">
                                {{ $asignado ?? '*libre*' }}
                            </div>
                        </div>

                        @can('Gestionar CTAs')
                            @if($cta->status === 'scheduled')
                                <div class="flex gap-1 shrink-0">
                                    <button wire:click="openAssignModal({{ $weapon->id }})"
                                            title="{{ $asignado ? 'Cambiar' : 'Asignar' }}"
                                            class="text-info hover:text-info/80 p-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            @if($asignado)
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                            @else
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5H4a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5V4a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            @endif
                                        </svg>
                                    </button>
                                    @if($asignado)
                                        <button wire:click="unassignWeapon({{ $weapon->id }})"
                                                wire:confirm="¿Liberar esta arma?"
                                                title="Liberar"
                                                class="text-error hover:text-error/80 p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        @endcan
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- PARTICIPANTES                                                --}}
        {{-- ============================================================ --}}
        <div class="bg-base-300 rounded-lg border border-base-300">
            <div class="px-6 py-4 border-b border-base-300">
                <h3 class="font-semibold text-content-accent">👥 Participantes ({{ $cta->participants->count() }})</h3>
            </div>
            <div class="w-full overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Personaje</th>
                            <th class="px-4 py-3">Arma</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-primary/30">
                        @forelse($cta->participants as $p)
                            <tr class="hover:bg-base-200/50">
                                <td class="px-4 py-3 text-sm">{{ $p->user_name }}</td>
                                <td class="px-4 py-3 text-sm text-content-light/80">
                                    {{ $p->personaje?->Name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($p->weapon)
                                        <span class="inline-flex items-center gap-1.5">
                                            {!! $p->weapon->icon_html !!}
                                            <span>{{ $p->weapon->weapon_name }}</span>
                                        </span>
                                    @else
                                        <span class="text-content-light/50">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $estados = [
                                            'confirmed' => ['✅', 'text-success'],
                                            'absent'    => ['⏰', 'text-warning'],
                                            'declined'  => ['❌', 'text-error'],
                                        ];
                                        [$emoji, $color] = $estados[$p->status] ?? ['❓', 'text-neutro'];
                                    @endphp
                                    <span class="{{ $color }}">{{ $emoji }} {{ ucfirst($p->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right">{{ $p->participation_percent }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-content-light/70">
                                    Nadie se ha inscrito aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- MODAL: EDITAR DATOS BÁSICOS                                  --}}
        {{-- ============================================================ --}}
        <x-dialog-modal wire:model="showEditBasicModal" maxWidth="lg">
            <x-slot name="title">Editar CTA</x-slot>
            <x-slot name="content">
                <div class="space-y-3">
                    <div>
                        <x-label for="editTitle" value="Título *" />
                        <x-input id="editTitle" type="text" wire:model="editTitle" class="mt-1 block w-full" maxlength="120"/>
                        @error('editTitle') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-label for="editLocation" value="Ubicación *" />
                        <x-input id="editLocation" type="text" wire:model="editLocation" class="mt-1 block w-full" maxlength="120"/>
                        @error('editLocation') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <x-label for="editStartTime" value="Inicio (UTC) *" />
                            <x-input id="editStartTime" type="datetime-local" wire:model="editStartTime" class="mt-1 block w-full"/>
                            @error('editStartTime') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <x-label for="editEndTime" value="Fin (UTC) *" />
                            <x-input id="editEndTime" type="datetime-local" wire:model="editEndTime" class="mt-1 block w-full"/>
                            @error('editEndTime') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <x-label for="editReminderMinutes" value="Recordatorio (minutos antes) *" />
                        <x-input id="editReminderMinutes" type="number" wire:model="editReminderMinutes" min="1" max="120" class="mt-1 block w-full"/>
                        @error('editReminderMinutes') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-secondary-button wire:click="$set('showEditBasicModal', false)">Cancelar</x-secondary-button>
                <x-button class="ml-2" wire:click="updateBasicInfo" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="updateBasicInfo">Guardar</span>
                    <span wire:loading wire:target="updateBasicInfo">Guardando…</span>
                </x-button>
            </x-slot>
        </x-dialog-modal>

        {{-- ============================================================ --}}
        {{-- MODAL: ASIGNAR PERSONAJE A UN ARMA                          --}}
        {{-- ============================================================ --}}
        <x-dialog-modal wire:model="showAssignModal" maxWidth="lg">
            <x-slot name="title">
                @php
                    $weaponAsig = $assignWeaponId
                        ? $cta->composition->weapons->firstWhere('id', $assignWeaponId)
                        : null;
                @endphp
                Asignar personaje {{ $weaponAsig ? '→ ' . $weaponAsig->weapon_name : '' }}
            </x-slot>
            <x-slot name="content">
                @if($weaponAsig)
                    <div class="flex items-center gap-2 mb-3 p-2 bg-base-200 rounded">
                        <div>{!! $weaponAsig->icon_html !!}</div>
                        <span class="text-content-light text-sm">{{ $weaponAsig->weapon_name }}</span>
                        @if(isset($ocupacion[$weaponAsig->id]))
                            <span class="ml-auto text-xs text-content-light/60">
                                Ocupada por <strong>{{ $ocupacion[$weaponAsig->id] }}</strong>
                            </span>
                        @endif
                    </div>
                @endif

                <x-input type="search" wire:model.live.debounce.300ms="searchPersonaje"
                         placeholder="Buscar personaje…" class="w-full mb-2"/>

                <div class="max-h-64 overflow-y-auto border border-base-300 rounded">
                    @forelse($personajesFiltrados as $p)
                        <button type="button"
                                wire:click="assignPersonaje({{ $p->id }})"
                                wire:loading.attr="disabled"
                                wire:target="assignPersonaje"
                                class="w-full text-left px-3 py-2 hover:bg-primary/10 transition border-b border-base-200 last:border-0 disabled:opacity-50">
                            <span class="text-content-light text-sm">{{ $p->Name }}</span>
                        </button>
                    @empty
                        <p class="p-4 text-center text-content-light/60 text-sm">
                            Sin personajes disponibles con ese filtro.
                        </p>
                    @endforelse
                </div>
            </x-slot>
            <x-slot name="footer">
                <x-secondary-button wire:click="closeAssignModal">Cancelar</x-secondary-button>
            </x-slot>
        </x-dialog-modal>

        {{-- ============================================================ --}}
        {{-- MODAL: CONFIRMAR CANCELACIÓN                                 --}}
        {{-- ============================================================ --}}
        <x-confirmation-modal wire:model="showCancelModal">
            <x-slot name="title">Cancelar CTA</x-slot>
            <x-slot name="content">
                ¿Seguro que deseas cancelar <strong>{{ $cta->title }}</strong>?
                <br><br>
                <span class="text-sm text-content-light/70">
                    Se actualizará el mensaje de Discord como cancelado y se eliminarán
                    los botones de Unirse/Ausente. Los participantes ya confirmados
                    permanecen en el historial.
                </span>
            </x-slot>
            <x-slot name="footer">
                <x-secondary-button wire:click="$set('showCancelModal', false)">Volver</x-secondary-button>
                <x-danger-button class="ml-2" wire:click="confirmCancel" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="confirmCancel">Sí, cancelar CTA</span>
                    <span wire:loading wire:target="confirmCancel">Cancelando…</span>
                </x-danger-button>
            </x-slot>
        </x-confirmation-modal>

    @endif
</div>