<div>
    {{-- ============================================================== --}}
    {{-- ENCABEZADO + TOGGLE DE VISTA                                   --}}
    {{-- ============================================================== --}}
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
        <div class="flex items-center gap-3 flex-wrap">
            <h3 class="font-semibold text-base text-content-light">Actividades (CTAs)</h3>

            {{-- Toggle Lista / Semana --}}
            <div class="inline-flex rounded-lg border border-base-300 overflow-hidden">
                <button wire:click="setViewMode('list')"
                        class="px-3 py-1.5 text-xs font-semibold transition
                               {{ $viewMode === 'list'
                                    ? 'bg-primary text-white'
                                    : 'bg-base-200 text-content-light/70 hover:bg-base-300' }}">
                    📋 Lista
                </button>
                <button wire:click="setViewMode('calendar')"
                        class="px-3 py-1.5 text-xs font-semibold transition
                               {{ $viewMode === 'calendar'
                                    ? 'bg-primary text-white'
                                    : 'bg-base-200 text-content-light/70 hover:bg-base-300' }}">
                    📅 Semana
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
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

            @if($viewMode === 'list')
                <select wire:model.live="filter"
                        class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm">
                    <option value="scheduled">Programadas</option>
                    <option value="past">Pasadas</option>
                    <option value="all">Todas</option>
                </select>
            @endif

            <x-input type="search" wire:model.live="buscar" placeholder="Buscar..." class="w-56" />
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- VISTA: LISTA                                                    --}}
    {{-- ============================================================== --}}
    @if($viewMode === 'list')
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
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
                                    <span class="{{ $colors[$cta->status] ?? 'bg-base-200' }} px-2 py-1 rounded-full text-xs">
                                        {{ ucfirst($cta->status) }}
                                    </span>
                                    @if ($cta->paid)
                                        <span class="ml-1 bg-success/20 text-success px-2 py-1 rounded-full text-xs">Pagada</span>
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
    @endif

    {{-- ============================================================== --}}
    {{-- VISTA: SEMANA                                                   --}}
    {{-- ============================================================== --}}
    @if($viewMode === 'calendar')
        @php
            $totalWeek   = collect($weekDays)->sum(fn($d) => $d['ctas']->count());
            $totalConf   = collect($weekDays)->sum(fn($d) => $d['ctas']->sum('confirmed_count'));
            $totalCancel = collect($weekDays)->sum(fn($d) => $d['ctas']->where('status', 'cancelled')->count());
            $totalDone   = collect($weekDays)->sum(fn($d) => $d['ctas']->where('status', 'completed')->count());
        @endphp

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-4">

            {{-- ============================================ --}}
            {{-- COLUMNA IZQUIERDA: CALENDARIO SEMANAL        --}}
            {{-- ============================================ --}}
            <div>
                <div class="bg-base-300 rounded-lg border border-base-300 overflow-hidden">

                    {{-- -------- Navegación semanal -------- --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3
                                border-b border-primary/20 bg-base-200/40">
                        <x-secondary-button wire:click="previousWeek" title="Semana anterior">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                      clip-rule="evenodd" />
                            </svg>
                        </x-secondary-button>

                        <div class="text-center flex-1 min-w-[220px]">
                            <div class="text-lg font-bold text-content-accent capitalize">
                                {{-- AQUI ESTÁ EL FIX --}}
                                {{ $displayWeekStart->translatedFormat('F Y') }}
                            </div>
                            <div class="text-xs text-content-light/60">
                                {{ $displayWeekStart->format('d/m') }} — {{ $displayWeekEnd->format('d/m') }}
                                · Semana {{ $displayWeekStart->weekOfYear }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-secondary-button wire:click="goToCurrentWeek" title="Volver a la semana actual">
                                Hoy
                            </x-secondary-button>
                            <x-secondary-button wire:click="nextWeek" title="Semana siguiente">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                          clip-rule="evenodd" />
                                </svg>
                            </x-secondary-button>
                        </div>
                    </div>

                    {{-- -------- Resumen rápido -------- --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-base-300 border-b border-base-300">
                        <div class="p-3 text-center">
                            <div class="text-xs uppercase text-content-light/60">Total semana</div>
                            <div class="text-2xl font-bold text-content-accent">{{ $totalWeek }}</div>
                        </div>
                        <div class="p-3 text-center">
                            <div class="text-xs uppercase text-content-light/60">Confirmados</div>
                            <div class="text-2xl font-bold text-success">{{ $totalConf }}</div>
                        </div>
                        <div class="p-3 text-center">
                            <div class="text-xs uppercase text-content-light/60">Completadas</div>
                            <div class="text-2xl font-bold text-neutro">{{ $totalDone }}</div>
                        </div>
                        <div class="p-3 text-center">
                            <div class="text-xs uppercase text-content-light/60">Canceladas</div>
                            <div class="text-2xl font-bold text-error">{{ $totalCancel }}</div>
                        </div>
                    </div>

                    {{-- -------- Grid de 7 días -------- --}}
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-px bg-base-300">
                        @foreach($weekDays as $day)
                            <div class="bg-base-300 min-h-[160px] flex flex-col
                                        {{ $day['isToday'] ? 'ring-2 ring-inset ring-primary/60' : '' }}">

                                {{-- Cabecera del día --}}
                                <div class="px-2 py-2 border-b border-base-200 flex items-center justify-between
                                            {{ $day['isToday'] ? 'bg-primary/20' : 'bg-base-200/40' }}">
                                    <div>
                                        <div class="text-[10px] uppercase font-semibold
                                                    {{ $day['isToday'] ? 'text-primary' : 'text-content-light/60' }}">
                                            {{ $day['date']->translatedFormat('D') }}
                                        </div>
                                        <div class="text-lg font-bold leading-none
                                                    {{ $day['isToday'] ? 'text-primary' : 'text-content-light' }}">
                                            {{ $day['date']->format('d') }}
                                        </div>
                                    </div>
                                    @if($day['ctas']->count() > 0)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold
                                                     {{ $day['isToday']
                                                        ? 'bg-primary text-white'
                                                        : 'bg-base-200 text-content-light/70' }}">
                                            {{ $day['ctas']->count() }}
                                        </span>
                                    @endif
                                </div>

                                {{-- CTAs del día --}}
                                <div class="flex-1 p-1.5 space-y-1.5">
                                    @forelse($day['ctas'] as $cta)
                                        @php
                                            // Canceladas: tachadas y apagadas (decisión B)
                                            $isCancelled = $cta->status === 'cancelled';
                                            $statusStyle = match($cta->status) {
                                                'scheduled' => 'border-primary/40 bg-primary/5 hover:bg-primary/10',
                                                'cancelled' => 'border-error/30 bg-error/5 hover:bg-error/10 opacity-70',
                                                'completed' => 'border-neutro/40 bg-neutro/5 hover:bg-neutro/10',
                                                default     => 'border-base-300 bg-base-200',
                                            };
                                        @endphp
                                        <a href="{{ route('ctas.show', $cta->id) }}"
                                           class="block px-2 py-1.5 rounded border {{ $statusStyle }}
                                                  transition text-left"
                                           title="{{ $cta->title }} · {{ $cta->start_time->format('H:i') }} UTC">

                                            <div class="flex items-center justify-between gap-1">
                                                <span class="text-[10px] font-mono text-content-light/70">
                                                    {{ $cta->start_time->format('H:i') }}
                                                </span>
                                                @if($cta->status === 'cancelled')
                                                    <span class="text-[9px] text-error">✕</span>
                                                @elseif($cta->status === 'completed')
                                                    <span class="text-[9px] text-neutro">✓</span>
                                                @endif
                                            </div>

                                            <div class="text-xs font-semibold truncate
                                                        {{ $isCancelled
                                                            ? 'line-through text-content-light/60'
                                                            : 'text-content-light' }}">
                                                {{ $cta->title }}
                                            </div>

                                            @if($cta->composition)
                                                <div class="text-[10px] text-content-light/50 truncate
                                                            {{ $isCancelled ? 'line-through' : '' }}">
                                                    🎭 {{ $cta->composition->name }}
                                                </div>
                                            @endif

                                            {{-- Fila de indicadores (C) --}}
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="text-[10px] text-primary">👥 {{ $cta->confirmed_count }}</span>

                                                {{-- Estado de publicación en Discord --}}
                                                @if($cta->discord_message_id)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-success"
                                                          title="Publicada en Discord"></span>
                                                @else
                                                    <span class="w-1.5 h-1.5 rounded-full bg-warning"
                                                          title="Sin publicar en Discord"></span>
                                                @endif

                                                {{-- Estado de pago (solo si completada) --}}
                                                @if($cta->status === 'completed')
                                                    @if($cta->paid)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-success"
                                                              title="Pagada"></span>
                                                    @else
                                                        <span class="w-1.5 h-1.5 rounded-full bg-accent"
                                                              title="Sin pagar"></span>
                                                    @endif
                                                @endif
                                            </div>
                                        </a>
                                    @empty
                                        <div class="h-full flex items-center justify-center
                                                    text-[10px] text-content-light/20 italic">
                                            —
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- -------- Leyenda -------- --}}
                <div class="flex flex-wrap gap-4 text-xs text-content-light/60 mt-3 px-2">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded border border-primary/40 bg-primary/20"></span>
                        Programada
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded border border-neutro/40 bg-neutro/20"></span>
                        Completada
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded border border-error/30 bg-error/10"></span>
                        Cancelada
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded ring-2 ring-primary/60 bg-primary/10"></span>
                        Hoy
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                        Anuncio publicada / Pagada
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-warning"></span>
                        Sin publicar
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent"></span>
                        Sin pagar
                    </span>
                </div>

                {{-- -------- Listado detallado de la semana -------- --}}
                @if($totalWeek > 0)
                    <div class="mt-6 bg-base-300 rounded-lg border border-base-300">
                        <div class="px-4 py-3 border-b border-primary/30">
                            <h3 class="font-semibold text-content-accent flex items-center gap-2">
                                📅 CTAs de esta semana
                                <span class="bg-primary/20 text-primary text-xs px-2 py-0.5 rounded-full">
                                    {{ $totalWeek }}
                                </span>
                            </h3>
                        </div>
                        <div class="divide-y divide-base-200">
                            @foreach(collect($weekDays)->flatMap(fn($d) => $d['ctas'])->sortBy('start_time') as $cta)
                                @php
                                    $isCancelled = $cta->status === 'cancelled';
                                @endphp
                                <a href="{{ route('ctas.show', $cta->id) }}"
                                   class="flex items-center gap-3 px-4 py-2 hover:bg-base-200/50 transition
                                          {{ $isCancelled ? 'opacity-60' : '' }}">
                                    <div class="text-xs font-mono text-content-light/60 w-24 shrink-0">
                                        {{ $cta->start_time->translatedFormat('D d/m') }}
                                    </div>
                                    <div class="text-xs font-mono text-content-light/60 w-12 shrink-0">
                                        {{ $cta->start_time->format('H:i') }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm truncate
                                                    {{ $isCancelled
                                                        ? 'line-through text-content-light/60'
                                                        : 'text-content-light' }}">
                                            {{ $cta->title }}
                                        </div>
                                        <div class="text-xs text-content-light/50 truncate">
                                            📍 {{ $cta->location }}
                                            @if($cta->composition)
                                                · 🎭 {{ $cta->composition->name }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-xs text-primary shrink-0">👥 {{ $cta->confirmed_count }}</div>

                                    {{-- Indicador Discord (C) --}}
                                    @if($cta->discord_message_id)
                                        <span class="bg-success/20 text-success px-1.5 py-0.5 rounded text-[10px] shrink-0"
                                              title="Publicada en Discord">📣</span>
                                    @else
                                        <span class="bg-warning/20 text-warning px-1.5 py-0.5 rounded text-[10px] shrink-0"
                                              title="Sin publicar en Discord">🔕</span>
                                    @endif

                                    {{-- Indicador Pago (C) --}}
                                    @if($cta->status === 'completed')
                                        @if($cta->paid)
                                            <span class="bg-success/20 text-success px-1.5 py-0.5 rounded text-[10px] shrink-0"
                                                  title="Pagada">💰</span>
                                        @else
                                            <span class="bg-accent/20 text-accent px-1.5 py-0.5 rounded text-[10px] shrink-0"
                                                  title="Sin pagar">💤</span>
                                        @endif
                                    @endif

                                    @php
                                        $badge = match($cta->status) {
                                            'scheduled' => 'bg-info/20 text-info',
                                            'cancelled' => 'bg-error/20 text-error',
                                            'completed' => 'bg-neutro/20 text-neutro',
                                            default     => 'bg-base-200',
                                        };
                                    @endphp
                                    <span class="{{ $badge }} px-2 py-0.5 rounded-full text-[10px] uppercase shrink-0">
                                        {{ $cta->status }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ============================================ --}}
            {{-- COLUMNA DERECHA: PRÓXIMAS CTAs (E)           --}}
            {{-- ============================================ --}}
            <aside class="bg-base-300 rounded-lg border border-base-300 h-fit
                          xl:sticky xl:top-20">
                <div class="px-4 py-3 border-b border-primary/30">
                    <h3 class="font-semibold text-content-accent flex items-center gap-2 text-sm">
                        ⏭️ Próximas CTAs
                        @if($upcoming->count() > 0)
                            <span class="bg-primary/20 text-primary text-[10px] px-1.5 py-0.5 rounded-full">
                                {{ $upcoming->count() }}
                            </span>
                        @endif
                    </h3>
                    <p class="text-[10px] text-content-light/50 mt-0.5">
                        Independiente de la semana mostrada
                    </p>
                </div>

                @if($upcoming->isEmpty())
                    <div class="p-4 text-center text-xs text-content-light/50">
                        No hay CTAs programadas próximamente.
                    </div>
                @else
                    <ul class="divide-y divide-base-200">
                        @foreach($upcoming as $next)
                            @php
                                $daysToNext = (int) now()->startOfDay()->diffInDays(
                                    $next->start_time->copy()->startOfDay(),
                                    false
                                );
                                $relative = match(true) {
                                    $daysToNext <= 0 => 'Hoy',
                                    $daysToNext === 1 => 'Mañana',
                                    $daysToNext < 7 => "En {$daysToNext} días",
                                    default => $next->start_time->translatedFormat('d M'),
                                };
                            @endphp
                            <li>
                                <a href="{{ route('ctas.show', $next->id) }}"
                                   class="block px-3 py-2 hover:bg-base-200/50 transition">
                                    <div class="flex items-center justify-between gap-2 mb-0.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider
                                                     {{ $daysToNext <= 1 ? 'text-primary' : 'text-content-light/60' }}">
                                            {{ $relative }}
                                        </span>
                                        <span class="text-[10px] font-mono text-content-light/50">
                                            {{ $next->start_time->format('H:i') }}
                                        </span>
                                    </div>
                                    <div class="text-sm font-medium text-content-light truncate">
                                        {{ $next->title }}
                                    </div>
                                    <div class="flex items-center justify-between gap-2 mt-0.5">
                                        <span class="text-[10px] text-content-light/50 truncate">
                                            {{ $next->composition?->name ?? '—' }}
                                        </span>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <span class="text-[10px] text-primary">👥 {{ $next->confirmed_count }}</span>
                                            @if($next->discord_message_id)
                                                <span class="w-1.5 h-1.5 rounded-full bg-success"
                                                      title="Publicada en Discord"></span>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-warning"
                                                      title="Sin publicar"></span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </aside>
        </div>
    @endif
</div>