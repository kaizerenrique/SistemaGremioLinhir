<div class="container mx-auto px-4 py-8">

    {{-- ============================================================ --}}
    {{-- CABECERA                                                     --}}
    {{-- ============================================================ --}}
    <div class="mb-8">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-3xl font-bold text-content-accent flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    Reporte multi-batalla
                </h1>
                <p class="text-content-light/70 text-sm mt-1">
                    {{ $totals['sessions'] }} reportes concatenados · {{ $totals['date_range'] }} · {{ $totals['active_time'] }} de combate activo
                </p>
            </div>
            <a href="{{ route('battles.index') }}" class="inline-flex items-center gap-2 bg-base-200 hover:bg-base-300 border border-base-300 text-content-light px-4 py-2 rounded-lg text-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a batallas
            </a>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- KPIs PRINCIPALES                                             --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-base-300 p-4 rounded-lg border border-primary/30">
            <div class="text-xs uppercase text-content-light/60">Kills totales</div>
            <div class="text-3xl font-bold text-primary mt-1">{{ number_format($totals['kill_events'], 0, ',', '.') }}</div>
            <div class="text-xs text-content-light/50 mt-1">{{ number_format($totals['death_events'], 0, ',', '.') }} muertes-evento</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-accent/30">
            <div class="text-xs uppercase text-content-light/60">Bajas únicas</div>
            <div class="text-3xl font-bold text-accent mt-1">{{ number_format($totals['unique_casualties'], 0, ',', '.') }}</div>
            <div class="text-xs text-content-light/50 mt-1">de {{ number_format($totals['unique_players'], 0, ',', '.') }} jugadores únicos</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-xs uppercase text-content-light/60">Duración</div>
            <div class="text-3xl font-bold text-content-light mt-1">{{ $totals['active_time'] }}</div>
            <div class="text-xs text-content-light/50 mt-1">{{ $totals['sessions'] }} sesiones</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-xs uppercase text-content-light/60">K/D campaña</div>
            <div class="text-3xl font-bold mt-1 {{ ($totals['kdr'] ?? 0) >= 1 ? 'text-success' : 'text-error' }}">
                {{ $totals['kdr'] !== null ? number_format($totals['kdr'], 2) : '—' }}
            </div>
            <div class="text-xs text-content-light/50 mt-1">{{ $totals['avg_deaths_player'] }} muertes/jugador</div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TABLAS DE ALIANZAS Y GREMIOS (LADO A LADO)                   --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        {{-- ALIANZAS --}}
        <div class="bg-base-300 rounded-lg border border-base-300">
            <div class="px-4 py-3 border-b border-primary/30">
                <h3 class="font-semibold text-content-accent">🤝 Alianzas ({{ $alliancesSummary->count() }})</h3>
            </div>
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-base-300">
                        <tr class="text-xs font-semibold tracking-wide text-left text-content-light uppercase border-b border-base-200">
                            <th class="px-3 py-2">Alianza</th>
                            <th class="px-3 py-2 text-center">Jugadores</th>
                            <th class="px-3 py-2 text-right text-primary">Kills</th>
                            <th class="px-3 py-2 text-right text-error">Muertes</th>
                            <th class="px-3 py-2 text-right">Avg IP</th>
                            <th class="px-3 py-2 text-right text-accent">Fama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($alliancesSummary as $alliance)
                            <tr class="hover:bg-base-200/30">
                                <td class="px-3 py-2 text-content-light font-medium">{{ $alliance->alliance_name }}</td>
                                <td class="px-3 py-2 text-center text-content-light/70">{{ $alliance->players_count }}</td>
                                <td class="px-3 py-2 text-right text-primary">{{ number_format($alliance->total_kills) }}</td>
                                <td class="px-3 py-2 text-right text-error">{{ number_format($alliance->total_deaths) }}</td>
                                <td class="px-3 py-2 text-right text-content-light/70">{{ $alliance->avg_ip ? round($alliance->avg_ip) : '—' }}</td>
                                <td class="px-3 py-2 text-right text-accent font-semibold">{{ number_format($alliance->total_fame, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-4 text-center text-content-light/50">Sin alianzas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- GREMIOS --}}
        <div class="bg-base-300 rounded-lg border border-base-300">
            <div class="px-4 py-3 border-b border-primary/30">
                <h3 class="font-semibold text-content-accent">🛡️ Gremios ({{ $guildsSummary->count() }})</h3>
            </div>
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-base-300">
                        <tr class="text-xs font-semibold tracking-wide text-left text-content-light uppercase border-b border-base-200">
                            <th class="px-3 py-2">Gremio</th>
                            <th class="px-3 py-2">Alianza</th>
                            <th class="px-3 py-2 text-center">Jugadores</th>
                            <th class="px-3 py-2 text-right text-primary">Kills</th>
                            <th class="px-3 py-2 text-right text-error">Muertes</th>
                            <th class="px-3 py-2 text-right text-accent">Fama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($guildsSummary as $guild)
                            <tr class="hover:bg-base-200/30">
                                <td class="px-3 py-2 text-content-light font-medium">{{ $guild->guild_name }}</td>
                                <td class="px-3 py-2 text-content-light/70">{{ $guild->alliance_name ?? '—' }}</td>
                                <td class="px-3 py-2 text-center text-content-light/70">{{ $guild->players_count }}</td>
                                <td class="px-3 py-2 text-right text-primary">{{ number_format($guild->total_kills) }}</td>
                                <td class="px-3 py-2 text-right text-error">{{ number_format($guild->total_deaths) }}</td>
                                <td class="px-3 py-2 text-right text-accent font-semibold">{{ number_format($guild->total_fame, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-4 text-center text-content-light/50">Sin gremios</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- LISTADO DE SUB-BATALLAS                                       --}}
    {{-- ============================================================ --}}
    <div class="bg-base-300 rounded-lg border border-base-300 mb-6">
        <div class="px-4 py-3 border-b border-primary/30">
            <h3 class="font-semibold text-content-accent">🔗 Reportes concatenados ({{ $battles->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
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
                            <td class="px-4 py-2 font-mono text-content-light">{{ $battle->id }}</td>
                            <td class="px-4 py-2 text-content-light">{{ $battle->start_time?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-2 text-right text-primary">{{ number_format($battle->total_kills) }}</td>
                            <td class="px-4 py-2 text-right text-accent">{{ number_format($battle->total_fame, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right text-content-light/70">{{ $battle->start_time && $battle->end_time ? gmdate('H\hi', $battle->start_time->diffInSeconds($battle->end_time)) : '—' }}</td>
                            <td class="px-4 py-2 text-content-light/70">{{ $battle->cluster_name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('battles.show', $battle->id) }}" class="text-primary hover:text-accent">Ver →</a>
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
        <div class="px-4 py-3 border-b border-primary/30 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <h3 class="font-semibold text-content-accent">👥 Jugadores</h3>
            </div>
            <x-input type="search" wire:model.live.debounce.400ms="searchPlayer" placeholder="Buscar jugador..." class="w-56 text-sm" />
            
            <select wire:model.live="guildFilter" class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm max-w-[200px]">
                <option value="">Todos los gremios</option>
                @foreach($guildsSummary as $g)
                    <option value="{{ $g->guild_id }}">{{ $g->guild_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="perPagePlayers" class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light text-sm">
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>

            <button wire:click="resetFilters" class="text-content-light/60 hover:text-error text-xs underline">Limpiar filtros</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
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
                        <th class="px-3 py-2 text-right cursor-pointer hover:bg-base-200" wire:click="sortBy('kill_fame')">
                            Fama
                            @if($sortField === 'kill_fame') <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200">
                    @forelse($paginatedPlayers as $player)
                        <tr class="hover:bg-base-200/30">
                            <td class="px-3 py-2 text-content-light">{{ $player->player_name }}</td>
                            <td class="px-3 py-2 text-content-light/70">{{ $player->guild_name ?? 'Sin gremio' }}</td>
                            <td class="px-3 py-2 text-right text-primary font-semibold">{{ number_format($player->kills) }}</td>
                            <td class="px-3 py-2 text-right text-error">{{ number_format($player->deaths) }}</td>
                            <td class="px-3 py-2 text-right text-accent">{{ number_format($player->kill_fame, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right text-content-light/80">{{ number_format($player->damage_done, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right text-content-light/80">{{ number_format($player->healing_done, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full">
                                    {{ $player->appearances }}/{{ $totals['sessions'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-content-light/70">Ningún jugador coincide con los filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedPlayers->hasPages())
            <div class="mx-4 px-4 py-3 border-t border-primary/20">
                {{ $paginatedPlayers->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>

    {{-- NOTA METODOLÓGICA --}}
    <div class="mt-6 bg-base-200/50 border border-base-300 rounded-lg p-4 text-xs text-content-light/60">
        <p class="font-semibold text-content-light/80 mb-2">📖 Cómo leer este reporte</p>
        <ul class="space-y-1 list-disc list-inside">
            <li><strong class="text-primary">Kills totales</strong> = suma de eventos de kill de todos los reportes.</li>
            <li><strong class="text-error">Muertes-evento</strong> = suma de veces que un jugador cayó al suelo (un jugador puede morir N veces).</li>
            <li><strong class="text-accent">Bajas únicas</strong> = jugadores distintos que murieron al menos una vez en toda la campaña.</li>
            <li><strong>K/D campaña</strong> se recalcula sobre los totales, nunca promediando K/D por sesión.</li>
            <li><strong>Sesiones</strong> = N/Total indica en cuántos reportes apareció ese jugador.</li>
        </ul>
    </div>

</div>