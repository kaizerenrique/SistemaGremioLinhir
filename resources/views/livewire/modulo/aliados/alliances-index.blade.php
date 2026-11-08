<div>
    {{-- ============================================================== --}}
    {{-- MENSAJES DE FEEDBACK                                          --}}
    {{-- ============================================================== --}}
    @if($successMessage)
        <div class="bg-success/10 border border-success/30 rounded-lg p-3 text-success text-sm mb-3 flex justify-between">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)">✕</button>
        </div>
    @endif
    @if($errorMessage)
        <div class="bg-error/10 border border-error/30 rounded-lg p-3 text-error text-sm mb-3 flex justify-between">
            <span>{{ $errorMessage }}</span>
            <button wire:click="$set('errorMessage', null)">✕</button>
        </div>
    @endif

    {{-- ============================================================== --}}
    {{-- ENCABEZADO                                                    --}}
    {{-- ============================================================== --}}
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
        <h3 class="font-semibold text-base text-content-light">Gremios Aliados (HRL)</h3>
        <div class="flex gap-2 flex-wrap">
            <x-input type="search" wire:model.live.debounce.300ms="buscar"
                     placeholder="Buscar por nombre o TAG..." class="w-56" />
            <x-secondary-button wire:click="openSyncModal">
                🔄 Sincronizar desde Albion
            </x-secondary-button>
            <x-button wire:click="newGuild">
                + Añadir gremio
            </x-button>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- TABLA                                                         --}}
    {{-- ============================================================== --}}
    <div class="overflow-hidden rounded-lg shadow-xs">
        <table class="w-full">
            <thead>
                <tr class="text-xs uppercase text-content-light bg-base-300 border-b border-primary/30">
                    <th class="px-4 py-3 text-left">TAG</th>
                    <th class="px-4 py-3 text-left">Gremio</th>
                    <th class="px-4 py-3 text-left">Rol Discord</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-primary/30">
                @forelse($guilds as $g)
                    <tr class="hover:bg-base-200/50">
                        <td class="px-4 py-3 text-sm font-mono font-semibold text-accent">
                            {{ $g->tag ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div>{{ $g->guild_name }}</div>
                            <div class="text-xs text-content-light/50 font-mono">{{ $g->guild_id }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($g->discord_role_id)
                                <code class="text-xs">{{ $g->discord_role_id }}</code>
                            @else
                                <span class="text-warning text-xs">⚠️ Sin rol</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if(!$g->is_configured)
                                <span class="bg-warning/20 text-warning px-2 py-1 rounded-full text-xs">
                                    ⚠️ Pendiente
                                </span>
                            @elseif($g->active)
                                <span class="bg-success/20 text-success px-2 py-1 rounded-full text-xs">Activo</span>
                            @else
                                <span class="bg-error/20 text-error px-2 py-1 rounded-full text-xs">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if(!$g->is_configured)
                                <button wire:click="openConfigure({{ $g->id }})"
                                        class="text-info hover:text-info/80 text-xs mr-2">
                                    Configurar
                                </button>
                            @elseif($g->active)
                                <button wire:click="confirmDeactivate({{ $g->id }})"
                                        class="text-error hover:text-error/80 text-xs">
                                    Desactivar
                                </button>
                            @else
                                <button wire:click="activate({{ $g->id }})"
                                        class="text-success hover:text-success/80 text-xs">
                                    Activar
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-content-light/70">
                            No hay gremios aliados registrados. Usa "🔄 Sincronizar desde Albion" para importar el roster de la HRL.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $guilds->links('vendor.pagination.tailwind') }}</div>
    </div>

    {{-- ============================================================== --}}
    {{-- MODAL: SINCRONIZAR CON ALBION                                 --}}
    {{-- ============================================================== --}}
    <x-dialog-modal wire:model="showSyncModal" maxWidth="2xl">
        <x-slot name="title">Sincronizar con la alianza HRL en Albion</x-slot>
        <x-slot name="content">
            @if($syncError)
                <div class="bg-error/10 border border-error/30 rounded p-3 text-error text-sm mb-3">
                    {{ $syncError }}
                </div>
            @endif

            @if(!empty($syncDiff['alliance']))
                <div class="mb-4 p-3 bg-base-200 rounded">
                    <div class="text-xs text-content-light/60 uppercase">Alianza</div>
                    <div class="text-lg font-semibold text-accent">
                        {{ $syncDiff['alliance']['name'] }}
                        <span class="text-sm font-mono text-content-light/60">
                            ({{ $syncDiff['alliance']['tag'] }})
                        </span>
                    </div>
                    <div class="text-xs text-content-light/60">
                        {{ count($syncDiff['alliance']['guilds']) }} gremios ·
                        {{ $syncDiff['alliance']['num_players'] }} jugadores
                    </div>
                </div>

                {{-- FALTANTES --}}
                @if(!empty($syncDiff['missing']))
                    <div class="mb-3">
                        <h4 class="text-sm font-semibold text-warning mb-2">
                            ⚠️ En Albion pero NO registrados ({{ count($syncDiff['missing']) }})
                        </h4>
                        <div class="space-y-1 max-h-48 overflow-y-auto">
                            @foreach($syncDiff['missing'] as $g)
                                <label class="flex items-center gap-2 p-2 bg-base-200/50 rounded cursor-pointer">
                                    <input type="checkbox"
                                           @checked(in_array($g['id'], $syncSelected))
                                           wire:click="toggleSyncSelected('{{ $g['id'] }}')"
                                           class="rounded text-primary">
                                    <span class="text-sm">{{ $g['name'] }}</span>
                                    <code class="text-xs text-content-light/50">{{ $g['id'] }}</code>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-sm text-success mb-3">
                        ✅ Todos los gremios de la alianza ya están registrados en tu DB.
                    </p>
                @endif

                {{-- HUÉRFANOS --}}
                @if(!empty($syncDiff['orphan']))
                    <div class="mb-3">
                        <h4 class="text-sm font-semibold text-error mb-2">
                            🚨 En tu DB pero NO en la alianza actual ({{ count($syncDiff['orphan']) }})
                        </h4>
                        <div class="space-y-1">
                            @foreach($syncDiff['orphan'] as $g)
                                <div class="p-2 bg-error/10 rounded text-sm">
                                    <span class="text-error">{{ $g['guild_name'] }}</span>
                                    <span class="text-xs text-content-light/50 ml-2">{{ $g['tag'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-content-light/60 mt-1">
                            Revisa manualmente si estos gremios salieron de la HRL.
                        </p>
                    </div>
                @endif
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showSyncModal', false)">Cerrar</x-secondary-button>
            @if(!empty($syncDiff['missing']))
                <x-button class="ml-2" wire:click="importSelected"
                          wire:loading.attr="disabled" wire:target="importSelected">
                    <span wire:loading.remove wire:target="importSelected">
                        Importar {{ count($syncSelected) }} gremio(s)
                    </span>
                    <span wire:loading wire:target="importSelected">Importando…</span>
                </x-button>
            @endif
        </x-slot>
    </x-dialog-modal>

    {{-- ============================================================== --}}
    {{-- MODAL: CREAR / CONFIGURAR GREMIO                              --}}
    {{-- ============================================================== --}}
    <x-dialog-modal wire:model="showModal" maxWidth="lg">
        <x-slot name="title">
            {{ $editingId ? 'Configurar gremio' : 'Añadir gremio aliado' }}
        </x-slot>
        <x-slot name="content">
            <div class="space-y-3">
                <div>
                    <x-label value="ID del gremio en Albion *" />
                    <div class="flex gap-2 mt-1">
                        <x-input type="text" wire:model="guild_id" class="flex-1"
                                 :readonly="$editingId !== null" />
                        @if(!$editingId)
                            <x-secondary-button type="button" wire:click="lookupInAlbion"
                                                wire:loading.attr="disabled" wire:target="lookupInAlbion">
                                <span wire:loading.remove wire:target="lookupInAlbion">🔍 Verificar</span>
                                <span wire:loading wire:target="lookupInAlbion">Buscando…</span>
                            </x-secondary-button>
                        @endif
                    </div>
                    @error('guild_id') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label value="Nombre (auto-rellenado)" />
                    <x-input type="text" wire:model="guild_name" readonly class="mt-1 bg-base-200/50" />
                    @error('guild_name') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-label value="TAG (2-4 mayúsculas) *" />
                        <div class="flex gap-2 mt-1">
                            <x-input type="text" wire:model="tag" maxlength="4"
                                     class="flex-1 font-mono uppercase" placeholder="ERR" />
                            <x-secondary-button type="button" wire:click="detectRole"
                                                wire:loading.attr="disabled" wire:target="detectRole">
                                🔍
                            </x-secondary-button>
                        </div>
                        @if($roleFound)
                            <p class="text-xs text-success mt-1">
                                ✅ Rol existente detectado (ID {{ $discord_role_id }}). Se reutilizará.
                            </p>
                        @elseif($discord_role_id)
                            <p class="text-xs text-content-light/60 mt-1">
                                Rol: {{ $discord_role_id }}
                            </p>
                        @else
                            <p class="text-xs text-content-light/60 mt-1">
                                No se detectó rol con ese nombre. Se creará al guardar.
                            </p>
                        @endif
                        @error('tag') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label value="Color del rol *" />
                        <input type="color" wire:model="color"
                               class="mt-1 w-full h-10 rounded border border-base-300 bg-base-200" />
                        @error('color') <span class="text-error text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <x-label value="Notas internas (opcional)" />
                    <textarea wire:model="notes" rows="2"
                              class="mt-1 w-full border rounded-md shadow-sm bg-base-200 text-content-light px-3 py-2"></textarea>
                    @error('notes') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="bg-warning/10 border border-warning/30 rounded p-2 text-xs text-warning">
                    ⚠️ Tras crear/guardar el gremio, deberás pulsar <strong>Activar</strong> en la tabla
                    para que sus miembros reciban <strong>@HRL</strong> al registrarse.
                    El rol debe estar por encima del rol del bot en la jerarquía de Discord.
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)">Cancelar</x-secondary-button>
            <x-button class="ml-2" wire:click="save"
                      wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- ============================================================== --}}
    {{-- MODAL: CONFIRMAR DESACTIVACIÓN                                --}}
    {{-- ============================================================== --}}
    <x-confirmation-modal wire:model="showConfirm">
        <x-slot name="title">Desactivar gremio</x-slot>
        <x-slot name="content">
            ¿Seguro que deseas desactivar este gremio?
            Sus miembros <strong>no serán degradados automáticamente</strong>;
            el bot los detectará en el próximo reporte de integridad HRL.
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showConfirm', false)">Cancelar</x-secondary-button>
            <x-danger-button wire:click="deactivate">Desactivar</x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>