<div class="container mx-auto px-4 py-8">
    <!-- Cabecera -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-content-accent">Batalla #{{ $battle->id }}</h1>
        <p class="text-content-light">
            Inicio: {{ $battle->start_time ? $battle->start_time->format('d/m/Y H:i:s') : 'N/A' }} &bull;
            Fin: {{ $battle->end_time ? $battle->end_time->format('d/m/Y H:i:s') : 'N/A' }} &bull;
            Cluster: {{ $battle->cluster_name ?? 'Desconocido' }}
        </p>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-content-light text-sm">Total Kills</div>
            <div class="text-2xl font-bold text-primary">{{ number_format($battle->total_kills) }}</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-content-light text-sm">Fama Total</div>
            <div class="text-2xl font-bold text-accent">{{ number_format($battle->total_fame, 0, ',', '.') }}</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-content-light text-sm">Alianzas</div>
            <div class="text-2xl font-bold text-content-light">{{ $alliances->count() }}</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-content-light text-sm">Gremios</div>
            <div class="text-2xl font-bold text-content-light">{{ $guilds->count() }}</div>
        </div>
    </div>

    <!-- Alianzas y Gremios Implicados (lado a lado) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <!-- Alianzas -->
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <h3 class="font-semibold text-content-light mb-2">Alianzas Implicadas</h3>
            <div class="overflow-x-auto max-h-60 overflow-y-auto">
                <table class="w-full">
                    <thead class="sticky top-0 bg-base-300">
                        <tr class="border-b border-primary/30">
                            <th class="px-3 py-2 text-left text-content-light text-sm">#</th>
                            <th class="px-3 py-2 text-left text-content-light text-sm">Nombre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alliances as $index => $alliance)
                            <tr class="border-b border-base-200">
                                <td class="px-3 py-2 text-content-light/60 text-sm">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 text-content-light text-sm">{{ $alliance->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-4 text-center text-content-light/70 text-sm">Sin alianzas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gremios -->
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <h3 class="font-semibold text-content-light mb-2">Gremios Implicados</h3>
            <div class="overflow-x-auto max-h-60 overflow-y-auto">
                <table class="w-full">
                    <thead class="sticky top-0 bg-base-300">
                        <tr class="border-b border-primary/30">
                            <th class="px-3 py-2 text-left text-content-light text-sm">#</th>
                            <th class="px-3 py-2 text-left text-content-light text-sm">Nombre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guilds as $index => $guild)
                            <tr class="border-b border-base-200">
                                <td class="px-3 py-2 text-content-light/60 text-sm">{{ $index + 1 }}</td>
                                <td class="px-3 py-2 text-content-light text-sm">{{ $guild->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-4 text-center text-content-light/70 text-sm">Sin gremios</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TOPs individuales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-base-300 p-4 rounded-lg border border-primary/30">
            <h4 class="font-semibold text-content-light mb-2">🏆 TOP Kills</h4>
            @if($topKills)
                <div class="text-center">
                    <p class="text-xl font-bold text-primary">{{ $topKills->player_name }}</p>
                    <p class="text-2xl font-bold text-accent">{{ $topKills->kills }} kills</p>
                    <p class="text-xs text-content-light/60">{{ $topKills->guild ? $topKills->guild->name : 'Sin gremio' }}</p>
                </div>
            @else
                <p class="text-content-light/70 text-center">Sin datos</p>
            @endif
        </div>

        <div class="bg-base-300 p-4 rounded-lg border border-primary/30">
            <h4 class="font-semibold text-content-light mb-2">🏆 TOP Heals</h4>
            @if($topHeals)
                <div class="text-center">
                    <p class="text-xl font-bold text-primary">{{ $topHeals->player_name }}</p>
                    <p class="text-2xl font-bold text-accent">{{ number_format($topHeals->support_healing_done, 0) }} curación</p>
                    <p class="text-xs text-content-light/60">{{ $topHeals->guild ? $topHeals->guild->name : 'Sin gremio' }}</p>
                </div>
            @else
                <p class="text-content-light/70 text-center">Sin datos</p>
            @endif
        </div>

        <div class="bg-base-300 p-4 rounded-lg border border-primary/30">
            <h4 class="font-semibold text-content-light mb-2">🏆 TOP Daño</h4>
            @if($topDamage)
                <div class="text-center">
                    <p class="text-xl font-bold text-primary">{{ $topDamage->player_name }}</p>
                    <p class="text-2xl font-bold text-accent">{{ number_format($topDamage->damage_done, 0) }} daño</p>
                    <p class="text-xs text-content-light/60">{{ $topDamage->guild ? $topDamage->guild->name : 'Sin gremio' }}</p>
                </div>
            @else
                <p class="text-content-light/70 text-center">Sin datos</p>
            @endif
        </div>
    </div>

    <!-- TOP muerte con más fama -->
    @if($topKillFame)
        <div class="bg-base-300 p-4 rounded-lg border border-primary/30 mb-8">
            <h3 class="font-semibold text-content-light">💀 Asesinato con más fama</h3>
            <p class="text-content-light">
                <span class="text-primary font-bold">{{ $topKillFame->killer_name }}</span>
                mató a <span class="text-error">{{ $topKillFame->victim_name }}</span>
                con <span class="text-accent">{{ number_format($topKillFame->killer_kill_fame, 0, ',', '.') }}</span> de fama
            </p>
        </div>
    @endif

    <!-- Estadísticas por Gremio (Tabla sin paginación) -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-content-accent mb-4">Estadísticas por Gremio</h3>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full bg-base-300 rounded-lg border border-base-300">
                <thead class="sticky top-0 bg-base-300">
                    <tr class="border-b border-primary/30">
                        <th class="px-3 py-2 text-left text-content-light text-sm">#</th>
                        <th class="px-3 py-2 text-left text-content-light text-sm">Gremio</th>
                        <th class="px-3 py-2 text-center text-content-light text-sm">Kills</th>
                        <th class="px-3 py-2 text-center text-content-light text-sm">Muertes</th>
                        <th class="px-3 py-2 text-center text-content-light text-sm">IP Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guildStats as $index => $stat)
                        <tr class="border-b border-base-200">
                            <td class="px-3 py-2 text-content-light/60 text-sm">{{ $index + 1 }}</td>
                            <td class="px-3 py-2 text-content-light text-sm">{{ $stat->guild ? $stat->guild->name : 'Desconocido' }}</td>
                            <td class="px-3 py-2 text-center text-primary text-sm">{{ number_format($stat->total_kills) }}</td>
                            <td class="px-3 py-2 text-center text-error text-sm">{{ number_format($stat->total_deaths) }}</td>
                            <td class="px-3 py-2 text-center text-content-light text-sm">{{ number_format($stat->avg_ip, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-content-light/70 text-sm">No hay datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Participantes -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-content-accent">Participantes</h3>
            <div>
                <label class="text-content-light text-sm mr-2">Filtrar por gremio:</label>
                <select wire:model.live="guildFilter" class="bg-base-300 border border-base-300 rounded px-3 py-1 text-content-light text-sm">
                    <option value="all">Todos</option>
                    @foreach($guilds as $guild)
                        <option value="{{ $guild->id }}">{{ $guild->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full bg-base-300 rounded-lg border border-base-300">
                <thead class="sticky top-0 bg-base-300">
                    <tr class="border-b border-primary/30">
                        <th class="px-3 py-2 text-left text-content-light text-sm">Jugador</th>
                        <th class="px-3 py-2 text-left text-content-light text-sm">Gremio</th>
                        <th class="px-3 py-2 text-center text-content-light text-sm">Kills</th>
                        <th class="px-3 py-2 text-center text-content-light text-sm">Muertes</th>
                        <th class="px-3 py-2 text-center text-content-light text-sm">Daño</th>
                        <th class="px-3 py-2 text-center text-content-light text-sm">Curación</th>
                        <th class="px-3 py-2 text-center text-content-light text-sm">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $player)
                        <tr class="border-b border-base-200">
                            <td class="px-3 py-2 text-content-light text-sm">{{ $player->player_name }}</td>
                            <td class="px-3 py-2 text-content-light/80 text-sm">{{ $player->guild ? $player->guild->name : 'Sin gremio' }}</td>
                            <td class="px-3 py-2 text-center text-primary text-sm">{{ $player->kills }}</td>
                            <td class="px-3 py-2 text-center text-error text-sm">{{ $player->deaths }}</td>
                            <td class="px-3 py-2 text-center text-content-light text-sm">{{ number_format($player->damage_done, 0) }}</td>
                            <td class="px-3 py-2 text-center text-content-light text-sm">{{ number_format($player->support_healing_done, 0) }}</td>
                            <td class="px-3 py-2 text-center text-content-light text-sm">{{ number_format($player->average_item_power, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-4 text-center text-content-light/70 text-sm">No hay participantes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kill Feed -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-content-accent mb-4">Kill Feed</h3>
        <div class="space-y-2 max-h-96 overflow-y-auto">
            @forelse($killEvents as $event)
                <div class="bg-base-300 p-3 rounded-lg border border-base-300 flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-content-light/60">{{ $event->timestamp->format('H:i:s') }}</span>
                    <span class="text-primary font-semibold">{{ $event->killer_name }}</span>
                    <span class="text-content-light">→</span>
                    <span class="text-error font-semibold">{{ $event->victim_name }}</span>
                    <span class="text-content-light/60 ml-auto">Fama: {{ number_format($event->killer_kill_fame, 0) }}</span>
                </div>
            @empty
                <p class="text-content-light/70">No hay eventos de asesinato.</p>
            @endforelse
        </div>
    </div>

    <!-- Equipamiento Perdido (deshabilitado temporalmente) -->
    <div class="bg-base-300 p-4 rounded-lg border border-base-300 mb-4">
        <h3 class="text-xl font-semibold text-content-accent mb-2">Equipamiento Perdido</h3>
        <p class="text-content-light/70">⚠️ Esta sección está deshabilitada temporalmente mientras mejoramos el rendimiento. Volverá pronto.</p>
    </div>
</div>