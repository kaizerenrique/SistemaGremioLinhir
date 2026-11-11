<div class="max-w-4xl mx-auto space-y-6">
    {{-- Errores globales --}}
    @if ($errorMessage)
        <div class="bg-error/10 border border-error/30 rounded-lg p-4 text-error text-sm">
            <span>{{ $errorMessage }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-error/10 border border-error/30 rounded-lg p-4 text-error text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- DATOS GENERALES --}}
    <div class="bg-base-300 rounded-lg border border-base-300 p-6 space-y-4">
        <h3 class="font-semibold text-content-accent text-lg border-b border-base-300 pb-2">Datos generales</h3>

        <div>
            <x-label for="title" value="Título de la CTA *" />
            <x-input id="title" type="text" wire:model="title" class="mt-1 block w-full" placeholder="Ej: ZvZ defensivo en Merlyn" maxlength="120" />
            @error('title') <span class="text-error text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <x-label for="location" value="Ubicación *" />
            <x-input id="location" type="text" wire:model="location" class="mt-1 block w-full" placeholder="Ej: Merlyn / HO / Fort Sterling" maxlength="120" />
            @error('location') <span class="text-error text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <x-label for="start_time" value="Inicio (UTC) *" />
                <x-input id="start_time" type="datetime-local" wire:model="start_time" class="mt-1 block w-full" />
                @error('start_time') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <x-label for="end_time" value="Fin (UTC) *" />
                <x-input id="end_time" type="datetime-local" wire:model="end_time" class="mt-1 block w-full" />
                @error('end_time') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <x-label for="composition_id" value="Composición *" />
                <select id="composition_id" wire:model.live="composition_id" class="mt-1 w-full bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30">
                    <option value="">— Seleccionar composición —</option>
                    @foreach ($compositions as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('composition_id') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <x-label for="reminder_minutes" value="Recordatorio (minutos antes) *" />
                <x-input id="reminder_minutes" type="number" wire:model="reminder_minutes" class="mt-1 block w-full" min="1" max="120" />
                @error('reminder_minutes') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- NUEVA SECCIÓN: CONFIGURACIÓN DE ANUNCIO Y PAGO --}}
    <div class="bg-base-300 rounded-lg border border-base-300 p-6 space-y-4">
        <h3 class="font-semibold text-content-accent text-lg border-b border-base-300 pb-2">Configuración de Anuncio y Pago</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Visibilidad --}}
            <div>
                <x-label for="visibility" value="Visibilidad *" />
                <select id="visibility" wire:model.live="visibility" class="mt-1 w-full bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30">
                    <option value="linhir">Solo Linhir (Privado)</option>
                    <option value="alliance">Alianza HRL (Público)</option>
                </select>
                <p class="text-xs text-content-light/60 mt-1">Determina a quién se menciona y quién puede entrar.</p>
                @error('visibility') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Tipo de Loot --}}
            <div>
                <x-label for="loot_type" value="Tipo de Botín *" />
                <select id="loot_type" wire:model="loot_type" class="mt-1 w-full bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30" @if($visibility === 'alliance') disabled @endif>
                    <option value="taxed_split">Split con Impuestos (Linhir)</option>
                    <option value="full_split">Full Split (Sin impuestos)</option>
                    <option value="pro_guild">Full Pro-Gremio (Todo al banco)</option>
                </select>
                @if($visibility === 'alliance')
                    <p class="text-xs text-warning mt-1">Las alianzas usan Full Split obligatoriamente.</p>
                @else
                    <p class="text-xs text-content-light/60 mt-1">Define cómo se pagará luego de la actividad.</p>
                @endif
                @error('loot_type') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Anuncio Diferido --}}
            <div>
                <x-label for="announce_before" value="¿Cuándo Anunciar? *" />
                <select id="announce_before" wire:model="announce_before" class="mt-1 w-full bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30">
                    <option value="now">Inmediatamente (Publicar ya)</option>
                    <option value="15m">15 minutos antes de iniciar</option>
                    <option value="1h">1 hora antes de iniciar</option>
                    <option value="2h">2 horas antes de iniciar</option>
                    <option value="1d">1 día antes de iniciar</option>
                </select>
                <p class="text-xs text-content-light/60 mt-1">Evita saturar de pings a los miembros.</p>
                @error('announce_before') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- PRE-ASIGNACIÓN DE ARMAS (Sin cambios, se mantiene el código original) --}}
    @if ($compositionActiva)
        <div class="bg-base-300 rounded-lg border border-base-300 p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300 pb-3">
                <div>
                    <h3 class="font-semibold text-content-accent text-lg">Asignación de armas</h3>
                    <p class="text-xs text-content-light/60 mt-0.5">Asigna personajes a armas específicas.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-content-light/60 whitespace-nowrap">
                        {{ collect($assignments)->filter()->count() }}/{{ $compositionActiva->weapons->count() }} asignadas
                    </span>
                    <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar personaje…" class="w-52 text-sm" />
                </div>
            </div>

            @error('assignments') <div class="bg-error/10 border border-error/30 rounded p-3 text-error text-sm">{{ $message }}</div> @enderror

            @if ($compositionActiva->weapons->isEmpty())
                <p class="text-content-light/60 text-sm italic text-center py-4">Esta composición no tiene armas definidas.</p>
            @else
                <div class="space-y-2 max-h-[28rem] overflow-y-auto pr-1">
                    @foreach ($compositionActiva->weapons as $weapon)
                        @php
                            $asignadoId = $assignments[$weapon->id] ?? null;
                            $asignado = $asignadoId ? $personajes->firstWhere('id', $asignadoId) : null;
                            $asignadoEnFiltrados = $asignadoId ? $personajesFiltrados->contains('id', $asignadoId) : false;
                        @endphp

                        <div class="flex items-center gap-3 bg-base-200/50 rounded px-3 py-2" wire:key="weapon-row-{{ $weapon->id }}">
                            <div class="w-9 h-9 flex items-center justify-center rounded bg-base-200 border border-base-300 shrink-0">
                                {!! $weapon->icon_html !!}
                            </div>
                            <div class="min-w-[130px] text-sm text-content-light font-medium truncate">
                                {{ $weapon->weapon_name }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <select wire:model="assignments.{{ $weapon->id }}" class="w-full bg-base-200 border border-base-300 rounded px-2 py-1.5 text-sm text-content-light focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30">
                                    <option value="">— Sin asignar —</option>
                                    @if ($asignado && !$asignadoEnFiltrados)
                                        <option value="{{ $asignado->id }}">{{ $asignado->Name }}</option>
                                    @endif
                                    @foreach ($personajesFiltrados as $p)
                                        <option value="{{ $p->id }}">{{ $p->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($asignado)
                                <span class="text-success text-sm" title="Asignado">✓</span>
                            @else
                                <span class="text-content-light/30 text-sm" title="Libre">•</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- IMAGEN DESTACADA (Mismo código anterior) --}}
    {{-- ... Puedes copiar la sección de la imagen del mensaje anterior, no tiene cambios ... --}}

    {{-- ACCIONES --}}
    <div class="flex justify-end gap-2 pb-8">
        <a href="{{ route('ctas.index') }}" class="inline-flex items-center px-4 py-2 bg-base-200 border border-base-300 rounded-md text-sm text-content-light hover:bg-base-300">Cancelar</a>
        <x-button wire:click="save" wire:loading.attr="disabled" wire:target="save,image">
            <span wire:loading.remove wire:target="save">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                {{ $announce_before === 'now' ? 'Crear y Publicar' : 'Programar CTA' }}
            </span>
            <span wire:loading wire:target="save">
                <svg class="inline w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                Procesando…
            </span>
        </x-button>
    </div>
</div>