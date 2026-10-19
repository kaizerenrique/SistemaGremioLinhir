<div class="max-w-4xl mx-auto space-y-6">

    {{-- ================================================================ --}}
    {{-- ERRORES GLOBALES                                                 --}}
    {{-- ================================================================ --}}
    @if ($errorMessage)
        <div class="bg-error/10 border border-error/30 rounded-lg p-4 text-error text-sm">
            <div class="flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span>{{ $errorMessage }}</span>
            </div>
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

    {{-- ================================================================ --}}
    {{-- DATOS GENERALES                                                  --}}
    {{-- ================================================================ --}}
    <div class="bg-base-300 rounded-lg border border-base-300 p-6 space-y-4">
        <h3 class="font-semibold text-content-accent text-lg border-b border-base-300 pb-2">
            Datos generales
        </h3>

        <div>
            <x-label for="title" value="Título de la CTA *" />
            <x-input id="title" type="text" wire:model="title" class="mt-1 block w-full"
                placeholder="Ej: ZvZ defensivo en Merlyn" maxlength="120" />
            @error('title')
                <span class="text-error text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <x-label for="location" value="Ubicación *" />
            <x-input id="location" type="text" wire:model="location" class="mt-1 block w-full"
                placeholder="Ej: Merlyn / HO / Fort Sterling" maxlength="120" />
            @error('location')
                <span class="text-error text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <x-label for="start_time" value="Inicio (UTC) *" />
                <x-input id="start_time" type="datetime-local" wire:model="start_time" class="mt-1 block w-full" />
                @error('start_time')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <x-label for="end_time" value="Fin (UTC) *" />
                <x-input id="end_time" type="datetime-local" wire:model="end_time" class="mt-1 block w-full" />
                @error('end_time')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <x-label for="composition_id" value="Composición *" />
                <select id="composition_id" wire:model.live="composition_id"
                    class="mt-1 w-full bg-base-200 border border-base-300 rounded px-3 py-2
                               text-content-light focus:outline-none focus:border-primary
                               focus:ring-2 focus:ring-primary/30">
                    <option value="">— Seleccionar composición —</option>
                    @foreach ($compositions as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('composition_id')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <x-label for="reminder_minutes" value="Recordatorio (minutos antes) *" />
                <x-input id="reminder_minutes" type="number" wire:model="reminder_minutes" class="mt-1 block w-full"
                    min="1" max="120" />
                @error('reminder_minutes')
                    <span class="text-error text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PRE-ASIGNACIÓN DE ARMAS                                          --}}
    {{-- ================================================================ --}}
    @if ($compositionActiva)
        <div class="bg-base-300 rounded-lg border border-base-300 p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-300 pb-3">
                <div>
                    <h3 class="font-semibold text-content-accent text-lg">
                        Asignación de armas
                    </h3>
                    <p class="text-xs text-content-light/60 mt-0.5">
                        Asigna personajes a armas específicas. Las armas reservadas
                        🔒 no pueden ser tomadas desde Discord.
                        Las libres ⚪ sí (con el botón "Unirse").
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-content-light/60 whitespace-nowrap">
                        {{ collect($assignments)->filter()->count() }}/{{ $compositionActiva->weapons->count() }}
                        asignadas
                    </span>
                    <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar personaje…"
                        class="w-52 text-sm" />
                </div>
            </div>

            @error('assignments')
                <div class="bg-error/10 border border-error/30 rounded p-3 text-error text-sm">
                    {{ $message }}
                </div>
            @enderror

            @if ($compositionActiva->weapons->isEmpty())
                <p class="text-content-light/60 text-sm italic text-center py-4">
                    Esta composición no tiene armas definidas.
                </p>
            @else
                <div class="space-y-2 max-h-[28rem] overflow-y-auto pr-1">
                    @foreach ($compositionActiva->weapons as $weapon)
                        @php
                            $asignadoId = $assignments[$weapon->id] ?? null;
                            $asignado = $asignadoId ? $personajes->firstWhere('id', $asignadoId) : null;
                            $asignadoEnFiltrados = $asignadoId
                                ? $personajesFiltrados->contains('id', $asignadoId)
                                : false;
                        @endphp

                        <div class="flex items-center gap-3 bg-base-200/50 rounded px-3 py-2"
                            wire:key="weapon-row-{{ $weapon->id }}">

                            {{-- Icono --}}
                            <div
                                class="w-9 h-9 flex items-center justify-center rounded
                                        bg-base-200 border border-base-300 shrink-0">
                                {!! $weapon->icon_html !!}
                            </div>

                            {{-- Nombre del arma --}}
                            <div class="min-w-[130px] text-sm text-content-light font-medium truncate">
                                {{ $weapon->weapon_name }}
                            </div>

                            {{-- Selector de personaje --}}
                            <div class="flex-1 min-w-0">
                                <select wire:model="assignments.{{ $weapon->id }}"
                                    class="w-full bg-base-200 border border-base-300 rounded px-2 py-1.5
                                               text-sm text-content-light
                                               focus:outline-none focus:border-primary
                                               focus:ring-2 focus:ring-primary/30">
                                    <option value="">— Sin asignar —</option>

                                    {{-- Preservar al asignado actual aunque el filtro lo oculte --}}
                                    @if ($asignado && !$asignadoEnFiltrados)
                                        <option value="{{ $asignado->id }}">{{ $asignado->Name }}</option>
                                    @endif

                                    @foreach ($personajesFiltrados as $p)
                                        <option value="{{ $p->id }}">{{ $p->Name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Indicador --}}
                            @if ($asignado)
                                <span class="text-success text-sm" title="Asignado">✓</span>
                            @else
                                <span class="text-content-light/30 text-sm" title="Libre">•</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($personajesFiltrados->isEmpty() && $search !== '')
                    <p class="text-xs text-content-light/60 text-center pt-2">
                        Ningún personaje coincide con "<strong>{{ $search }}</strong>".
                        <button type="button" wire:click="$set('search','')"
                            class="text-primary hover:underline">Limpiar</button>
                    </p>
                @endif
            @endif
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- IMAGEN DESTACADA                                                 --}}
    {{-- ================================================================ --}}
    <div class="bg-base-300 rounded-lg border border-base-300 p-6 space-y-3">
        <h3 class="font-semibold text-content-accent text-lg border-b border-base-300 pb-2">
            Imagen destacada
        </h3>

        @php
            $parseSize = fn($v) => match (strtolower(substr(trim((string) $v), -1))) {
                'g' => (int) $v * 1024 ** 3,
                'm' => (int) $v * 1024 ** 2,
                'k' => (int) $v * 1024,
                default => (int) $v,
            };
            $phpUpload = $parseSize(ini_get('upload_max_filesize'));
            $phpPost = $parseSize(ini_get('post_max_size'));
            $appLimit = 5 * 1024 * 1024;
            $effectiveMb = round(min($phpUpload, $phpPost, $appLimit) / (1024 * 1024), 1);
        @endphp

        <div>
            <x-label value="Imagen (opcional · JPG, PNG, WEBP o GIF)" />
            <div class="mt-1 flex items-center gap-3">
                <input type="file" wire:model="image" accept="image/jpeg,image/png,image/webp,image/gif"
                    class="block w-full text-sm text-content-light/80
                          file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0
                          file:bg-primary file:text-white file:font-semibold
                          hover:file:bg-hover-primary file:cursor-pointer" />
                <div wire:loading wire:target="image"
                    class="flex items-center gap-1.5 text-xs text-content-light/60 whitespace-nowrap">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Subiendo…
                </div>
            </div>

            <p class="text-xs text-content-light/60 mt-1">
                Tamaño máximo: <strong>{{ $effectiveMb }} MB</strong>
                @if ($effectiveMb < 5)
                    <span class="text-warning">(el servidor permite menos que el máximo de la app)</span>
                @endif
            </p>

            @error('image')
                <div class="mt-2 bg-error/10 border border-error/30 rounded p-3 text-error text-sm">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <div>{{ $message }}</div>
                    </div>
                </div>
            @enderror

            @if ($image)
                <div class="mt-3 rounded-lg overflow-hidden border border-base-300 max-w-md">
                    <img src="{{ $image->temporaryUrl() }}" alt="Preview"
                        class="w-full h-auto max-h-56 object-cover">
                </div>
                <p class="text-xs text-content-light/60 mt-1">
                    {{ number_format($image->getSize() / 1024, 0, ',', '.') }} KB
                    <button type="button" wire:click="$set('image', null)"
                        class="text-error hover:underline ml-2">Quitar</button>
                </p>
            @endif
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- ACCIONES                                                         --}}
    {{-- ================================================================ --}}
    <div class="flex justify-end gap-2 pb-8">
        <a href="{{ route('ctas.index') }}"
            class="inline-flex items-center px-4 py-2 bg-base-200 border border-base-300
                  rounded-md text-sm text-content-light hover:bg-base-300">
            Cancelar
        </a>
        <x-button wire:click="save" wire:loading.attr="disabled" wire:target="save,image">
            <span wire:loading.remove wire:target="save">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 inline" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                Crear y publicar en Discord
            </span>
            <span wire:loading wire:target="save">
                <svg class="inline w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                Publicando…
            </span>
        </x-button>
    </div>

</div>
