<div class="container mx-auto px-4 py-8">

    {{-- ============================================================ --}}
    {{-- CABECERA                                                     --}}
    {{-- ============================================================ --}}
    <div class="mb-8">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-3xl font-bold text-content-accent flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    Reporte multi-batalla
                </h1>
                <p class="text-content-light/70 text-sm mt-1">
                    {{ $aggregated['totals']['sessions'] }} reportes concatenados ·
                    {{ $aggregated['totals']['date_range'] }} ·
                    {{ $aggregated['totals']['active_time'] }} de combate activo
                </p>
            </div>

            <a href="{{ route('battles.index') }}"
               class="inline-flex items-center gap-2 bg-base-200 hover:bg-base-300 border border-base-300
                      text-content-light px-4 py-2 rounded-lg text-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver a batallas
            </a>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- KPIs PRINCIPALES                                             --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        {{-- Kills-evento --}}
        <div class="bg-base-300 p-4 rounded-lg border border-primary/30">
            <div class="text-xs uppercase text-content-light/60 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c.25.112.526.174.818.174.292 0 .569-.062.818-.174L5 10.274zm10 0l-.818 2.552c.25.112.526.174.818.174.292 0 .569-.062.818-.174L15 10.274z" clip-rule="evenodd"/>
                </svg>
                Kills totales
            </div>
            <div class="text-3xl font-bold text-primary mt-1">
                {{ number_format($aggregated['totals']['kill_events'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-content-light/50 mt-1">
                {{ number_format($aggregated['totals']['death_events'], 0, ',', '.') }} muertes-evento
            </div>
        </div>

        {{-- Bajas únicas --}}
        <div class="bg-base-300 p-4 rounded-lg border border-accent/30">
            <div class="text-xs uppercase text-content-light/60 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-accent" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                Bajas únicas
            </div>
            <div class="text-3xl font-bold text-accent mt-1">
                {{ number_format($aggregated['totals']['unique_casualties'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-content-light/50 mt-1">
                de {{ number_format($aggregated['totals']['unique_players'], 0, ',', '.') }} jugadores únicos
            </div>
        </div>

        {{-- Duración --}}
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-xs uppercase text-content-light/60 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-content-light" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                Tiempo activo
            </div>
            <div class="text-3xl font-bold text-content-light mt-1">
                {{ $aggregated['totals']['active_time'] }}
            </div>
            <div class="text-xs text-content-light/50 mt-1">
                {{ $aggregated['totals']['sessions'] }} sesiones · pared {{ $aggregated['totals']['wall_clock'] }}
            </div>
        </div>

        {{-- K/D campaña --}}
        @php $kdr = $aggregated['totals']['kdr']; @endphp
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-xs uppercase text-content-light/60 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                    <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                </svg>
                K/D campaña
            </div>
            <div class="text-3xl font-bold mt-1 {{ ($kdr ?? 0) >= 1 ? 'text-success' : 'text-error' }}">
                {{ $kdr !== null ? number_format($kdr, 2) : '—' }}
            </div>
            <div class="text-xs text-content-light/50 mt-1">
                {{ $aggregated['totals']['avg_deaths_player'] }} muertes/jugador ·
                {{ $aggregated['totals']['survival_rate_pct'] }}% sobrevivió
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TOPs INDIVIDUALES                                            --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @foreach ([
            ['label' => '🏆 TOP Kills',    'data' => $aggregated['topKills'],  'value' => 'kills',        'color' => 'text-primary',   'fmt' => 'int'],
            ['label' => '🏆 TOP Daño',     'data' => $aggregated['topDamage'], 'value' => 'damage_done',  'color' => 'text-accent',    'fmt' => 'num'],
            ['label' => '🏆 TOP Curación', 'data' => $aggregated['topHeals'],  'value' => 'healing_done', 'color' => 'text-success',   'fmt' => 'num'],
        ] as $card)
            <div class="bg-base-300 p-4 rounded-lg border border-primary/30">
                <h4 class="font-semibold text-content-light mb-2">{{ $card['label'] }}</h4>
                @if($card['data'])
                    <div class="text-center">
                        <p class="text-xl font-bold text-primary">{{ $card['data']['player_name'] }}</p>
                        <p class="text-2xl font-bold {{ $card['color'] }}">
                            @if($card['fmt'] === 'int')
                                {{ number_format($card['data'][$card['value']], 0, ',', '.') }}
                            @else
                                {{ number_format($card['data'][$card['value']], 0, ',', '.') }}
                            @endif
                        </p>
                        <p class="text-xs text-content-light/60">
                            {{ $card['data']['guild'] ?? 'Sin gremio' }}
                            · {{ $card['data']['appearances'] }}/{{ $aggregated['totals']['sessions'] }} sesiones
                        </p>
                    </div>
                @else
                    <p class="text-content-light/70 text-center">Sin datos</p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ============================================================ --}}
    {{-- TOP KILL FAME (evento individual más caro de la campaña)      --}}
    {{-- ============================================================ --}}
    @if($aggregated['topKillFameEvent'])
        @php $topK = $aggregated['topKillFameEvent']; @endphp
        <div class="bg-gradient-to-r from-primary/10 to-accent/10 border border-primary/30 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-content-accent flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-accent" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Kill más caro de la campaña
            </h3>
            <p class="text-content-light mt-2">
                <span class="text-primary font-bold text-lg">{{ $topK->killer_name }}</span>
                <span class="text-content-light/60"> mató a </span>
                <span class="text-error font-bold text-lg">{{ $topK->victim_name }}</span>
                <span class="text-content-light/60"> otorgando </span>
                <span class="text-accent font-bold text-lg">
                    {{ number_format($topK->killer_kill_fame, 0, ',', '.') }}
                </span>
                <span class="text-content-light/60"> de fama</span>
                <span class="text-xs text-content-light/40 ml-2">
                    ({{ $topK->timestamp?->format('d/m/Y H:i') }} UTC)
                </span>
            </p>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- DESGLOSE POR DÍA                                              --}}
    {{-- ============================================================ --}}
    @if($aggregated['byDay']->count() > 1)
        <div class="bg-base-300 rounded-lg border border-base-300 mb-6">
            <div class="px-4 py-3 border-b border-primary/30">
                <h3 class="font-semibold text-content-accent">📅 Desglose por día</h3>
                <p class="text-xs text-content-light/60 mt-0.5">
                    Totales por jornada — útil para recaps de campaña multi-día.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                            <th class="px-4 py-2">Fecha</th>
                            <th class="px-4 py-2 text-center">Sesiones</th>
                            <th class="px-4 py-2 text-right">Kills</th>
                            <th class="px-4 py-2 text-right">Muertes</th>
                            <th class="px-4 py-2 text-right">Fama</th>
                            <th class="px-4 py-2 text-right">Tiempo activo</th>
                            <th class="px-4 py-2 text-center">Reportes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @foreach($aggregated['byDay'] as $day)
                            <tr class="hover:bg-base-200/30">
                                <td class="px-4 py-2 text-sm text-content-light">
                                    {{ $day['date'] !== 'sin-fecha'
                                        ? \Carbon\Carbon::parse($day['date'])->format('d/m/Y')
                                        : '—' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-center text-content-light">
                                    {{ $day['sessions'] }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-primary font-semibold">
                                    {{ number_format($day['kill_events']) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-error">
                                    {{ number_format($day['death_events']) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-accent">
                                    {{ number_format($day['total_fame'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-content-light/70">
                                    {{ gmdate('H\hi', $day['active_seconds']) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-center">
                                    <div class="flex flex-wrap gap-1 justify-center">
                                        @foreach($day['battle_ids'] as $bid)
                                            <a href="{{ route('battles.show', $bid) }}"
                                               class="text-xs bg-primary/10 text-primary hover:bg-primary/20 px-2 py-0.5 rounded">
                                                #{{ $bid }}
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- LISTADO DE SUB-BATALLAS                                       --}}
    {{-- ============================================================ --}}
    <div class="bg-base-300 rounded-lg border border-base-300 mb-6">
        <div class="px-4 py-3 border-b border-primary/30">
            <h3 class="font-semibold text-content-accent">
                🔗 Reportes concatenados ({{ $battles->count() }})
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Inicio (UTC)</th>
                        <th class="px-4 py-2 text-right">Kills</th>
                        <th class="px-4 py-2 text-right">Fama</th>
                        <th class="px-4 py-2 text-right">Duración</th>
                        <th class="px-4 py-2">Cluster</th>
                        <th class="px-4 py-2 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200">
                    @foreach($battles as $battle)
                        <tr class="hover:bg-base-200/30">
                            <td class="px-4 py-2 text-sm font-mono text-content-light">{{ $battle->id }}</td>
                            <td class="px-4 py-2 text-sm text-content-light">
                                {{ $battle->start_time?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-primary">{{ number_format($battle->total_kills) }}</td>
                            <td class="px-4 py-2 text-sm text-right text-accent">
                                {{ number_format($battle->total_fame, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right text-content-light/70">
                                {{ $battle->start_time && $battle->end_time
                                    ? gmdate('H\hi', $battle->start_time->diffInSeconds($battle->end_time))
                                    : '—' }}
                            </td>
                            <td class="px-4 py-2 text-sm text-content-light/70">
                                {{ $battle->cluster_name ?? '—' }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right">
                                <a href="{{ route('battles.show', $battle->id) }}"
                                   class="text-primary hover:text-accent text-sm">
                                    Ver →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TABLA DE JUGADORES AGREGADA                                   --}}
    {{-- ============================================================ --}}
    <div class="bg-base-300 rounded-lg border border-base-300">

        {{-- Filtros --}}
        <div class="px-4 py-3 border-b border-primary/30 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <h3 class="font-semibold text-content-accent">
                    👥 Jugadores ({{ $aggregated['players']->count() }})
                </h3>
            </div>
            <x-input type="search"
                     wire:model.live.debounce.400ms="searchPlayer"
                     placeholder="Buscar jugador..."
                     class="w-56 text-sm" />
            <select wire:model.live="guildFilter"
                    class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm max-w-[220px]">
                <option value="">Todos los gremios</option>
                @foreach($guilds as $g)
                    <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                @endforeach
            </select>
            <select wire:model.live="perPagePlayers"
                    class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm">
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <button wire:click="resetFilters"
                    class="text-content-light/60 hover:text-error text-xs underline">
                Limpiar filtros
            </button>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-3 py-2 cursor-pointer hover:bg-base-200" wire:click="sortBy('player_name')">
                            Jugador
                            @if($sortField === 'player_name') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-2">Gremio</th>
                        <th class="px-3 py-2 text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('kills')">
                            Kills
                            @if($sortField === 'kills') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-2 text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('deaths')">
                            Muertes
                            @if($sortField === 'deaths') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-2 text-right">K/D</th>
                        <th class="px-3 py-2 text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('damage_done')">
                            Daño
                            @if($sortField === 'damage_done') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-2 text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('healing_done')">
                            Curación
                            @if($sortField === 'healing_done') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-2 text-center cursor-pointer hover:bg-base-200" wire:click="sortBy('appearances')">
                            Sesiones
                            @if($sortField === 'appearances') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-3 py-2 text-center">Bajas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200">
                    @forelse($paginatedPlayers as $player)
                        <tr class="hover:bg-base-200/30 {{ $player['is_linhir'] ? 'bg-primary/5' : '' }}">
                            <td class="px-3 py-2 text-sm text-content-light flex items-center gap-1.5">
                                @if($player['is_linhir'])
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary" title="Linhir"></span>
                                @endif
                                {{ $player['player_name'] }}
                            </td>
                            <td class="px-3 py-2 text-sm text-content-light/70">
                                {{ $player['guild'] }}
                            </td>
                            <td class="px-3 py-2 text-sm text-right text-primary font-semibold">
                                {{ number_format($player['kills']) }}
                            </td>
                            <td class="px-3 py-2 text-sm text-right text-error">
                                {{ number_format($player['deaths']) }}
                            </td>
                            <td class="px-3 py-2 text-sm text-right text-content-light">
                                {{ $player['deaths'] > 0
                                    ? number_format($player['kills'] / $player['deaths'], 2)
                                    : '∞' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-right text-content-light/80">
                                {{ number_format($player['damage_done'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-sm text-right text-content-light/80">
                                {{ number_format($player['healing_done'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-sm text-center">
                                <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full">
                                    {{ $player['appearances'] }}/{{ $aggregated['totals']['sessions'] }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($player['deaths'] === 0)
                                    <span class="text-xs bg-success/20 text-success px-2 py-0.5 rounded-full">
                                        Sin bajas
                                    </span>
                                @elseif($player['deaths'] === 1)
                                    <span class="text-xs bg-warning/20 text-warning px-2 py-0.5 rounded-full">
                                        1 baja
                                    </span>
                                @else
                                    <span class="text-xs bg-error/20 text-error px-2 py-0.5 rounded-full">
                                        {{ $player['deaths'] }} bajas
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-content-light/70">
                                Ningún jugador coincide con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($paginatedPlayers->hasPages())
            <div class="mx-4 px-4 py-3 border-t border-primary/20">
                {{ $paginatedPlayers->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- NOTA METODOLÓGICA                                             --}}
    {{-- ============================================================ --}}
    <div class="mt-6 bg-base-200/50 border border-base-300 rounded-lg p-4 text-xs text-content-light/60">
        <p class="font-semibold text-content-light/80 mb-2">📖 Cómo leer este reporte</p>
        <ul class="space-y-1 list-disc list-inside">
            <li><strong class="text-primary">Kills totales</strong> = suma de eventos de kill de todos los reportes.</li>
            <li><strong class="text-error">Muertes-evento</strong> = suma de veces que un jugador cayó al suelo (un jugador puede morir N veces).</li>
            <li><strong class="text-accent">Bajas únicas</strong> = jugadores distintos que murieron al menos una vez en toda la campaña.</li>
            <li><strong>K/D campaña</strong> se recalcula sobre los totales, nunca promediando K/D por sesión.</li>
            <li><strong>Tiempo activo</strong> suma la duración de cada sesión. <strong>Tiempo de pared</strong> es el calendario (útil para campañas multi-día).</li>
            <li><strong>Sesiones</strong> = N/Total indica en cuántos reportes apareció ese jugador (no cuántas veces murió).</li>
        </ul>
    </div>

</div>
