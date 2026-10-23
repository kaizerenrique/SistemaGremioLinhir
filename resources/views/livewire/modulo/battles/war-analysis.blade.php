<div class="container mx-auto px-4 py-8">
    {{-- FORMULARIO DE BÚSQUEDA --}}
    <div class="bg-base-300 rounded-lg border border-base-300 p-6 mb-8 shadow-sm">
        <h2 class="text-xl font-bold text-content-accent mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
            </svg>
            Análisis de Guerra (H2H)
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <x-label for="startDate" value="Desde" />
                <x-input id="startDate" type="date" wire:model="startDate" class="mt-1 block w-full" />
                @error('startDate') <span class="text-error text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <x-label for="endDate" value="Hasta" />
                <x-input id="endDate" type="date" wire:model="endDate" class="mt-1 block w-full" />
                @error('endDate') <span class="text-error text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-label for="enemyGuild" value="Gremio Enemigo" />
                <select id="enemyGuild" wire:model="enemyGuildId" class="mt-1 w-full bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light focus:ring-primary focus:border-primary">
                    <option value="">— Seleccionar Enemigo —</option>
                    @foreach($guilds as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
                @error('enemyGuildId') <span class="text-error text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-button wire:click="analyze" wire:loading.attr="disabled" class="w-full justify-center py-2">
                    <span wire:loading.remove wire:target="analyze">Analizar Conflicto</span>
                    <span wire:loading wire:target="analyze">Analizando...</span>
                </x-button>
            </div>
        </div>
    </div>

    {{-- RESULTADOS --}}
    @if($isAnalyzed)
        @if(empty($battleIds))
            <div class="bg-warning/10 border border-warning/30 rounded-lg p-8 text-center text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-lg font-semibold">No hubo enfrentamientos</p>
                <p class="text-sm mt-1">No se encontraron batallas donde Linhir y {{ $enemyGuild->name }} coincidieran en esas fechas.</p>
            </div>
        @else
        
            {{-- SCOREBOARD --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-base-300 rounded-lg border border-success/30 p-6 text-center">
                    <h3 class="text-sm font-semibold text-content-light/60 uppercase tracking-widest mb-2">Kills de Linhir</h3>
                    <div class="text-5xl font-black text-success">{{ number_format($scoreboard['linhir_kills']) }}</div>
                    <div class="text-sm text-content-light/50 mt-2">{{ number_format($scoreboard['linhir_fame'], 0, ',', '.') }} Fama ganada</div>
                    <div class="text-xs text-content-light/40 mt-1">Avg IP: {{ $scoreboard['linhir_avg_ip'] }}</div>
                </div>

                <div class="flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-xl font-bold text-content-accent bg-base-200 px-6 py-3 rounded-full border border-base-300">
                            K/D: 
                            @php
                                $ratio = $scoreboard['enemy_kills'] > 0 
                                    ? round($scoreboard['linhir_kills'] / $scoreboard['enemy_kills'], 2) 
                                    : $scoreboard['linhir_kills'];
                            @endphp
                            <span class="{{ $ratio >= 1 ? 'text-success' : 'text-error' }}">{{ $ratio }}</span>
                        </div>
                        <p class="text-xs text-content-light/50 mt-2">{{ $battles->count() }} batallas en el periodo</p>
                    </div>
                </div>

                <div class="bg-base-300 rounded-lg border border-error/30 p-6 text-center">
                    <h3 class="text-sm font-semibold text-content-light/60 uppercase tracking-widest mb-2">Kills del Enemigo</h3>
                    <div class="text-5xl font-black text-error">{{ number_format($scoreboard['enemy_kills']) }}</div>
                    <div class="text-sm text-content-light/50 mt-2">{{ number_format($scoreboard['enemy_fame'], 0, ',', '.') }} Fama ganada</div>
                    <div class="text-xs text-content-light/40 mt-1">Avg IP: {{ $scoreboard['enemy_avg_ip'] }}</div>
                </div>
            </div>

            {{-- TOP PERFORMERS --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Linhir Killers --}}
                <div class="bg-base-300 rounded-lg border border-base-300">
                    <div class="px-4 py-3 border-b border-primary/30 flex justify-between items-center">
                        <h3 class="font-semibold text-content-accent">🔥 Top Linhir (Asesinos)</h3>
                        <span class="text-xs text-content-light/50">Kills al enemigo</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-base-200">
                            @forelse($topLinhirKillers as $index => $k)
                                <tr class="hover:bg-base-200/30">
                                    <td class="px-4 py-2 text-content-light/50 w-8">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-content-light">{{ $k->killer_name }}</td>
                                    <td class="px-4 py-2 text-right font-bold text-success">{{ $k->total }}</td>
                                    <td class="px-4 py-2 text-right text-xs text-accent">{{ number_format($k->fame, 0, ',', '.') }} fame</td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-content-light/50">Sin datos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Enemy Killers --}}
                <div class="bg-base-300 rounded-lg border border-base-300">
                    <div class="px-4 py-3 border-b border-error/30 flex justify-between items-center">
                        <h3 class="font-semibold text-content-accent">☠️ Top Enemigo (Asesinos)</h3>
                        <span class="text-xs text-content-light/50">Kills a Linhir</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-base-200">
                            @forelse($topEnemyKillers as $index => $k)
                                <tr class="hover:bg-base-200/30">
                                    <td class="px-4 py-2 text-content-light/50 w-8">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-content-light">{{ $k->killer_name }}</td>
                                    <td class="px-4 py-2 text-right font-bold text-error">{{ $k->total }}</td>
                                    <td class="px-4 py-2 text-right text-xs text-accent">{{ number_format($k->fame, 0, ',', '.') }} fame</td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-content-light/50">Sin datos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Linhir Victims --}}
                <div class="bg-base-300 rounded-lg border border-base-300">
                    <div class="px-4 py-3 border-b border-error/30 flex justify-between items-center">
                        <h3 class="font-semibold text-content-accent">🩸 Linhir (Más bajas)</h3>
                        <span class="text-xs text-content-light/50">Mueren ante el enemigo</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-base-200">
                            @forelse($topLinhirVictims as $index => $v)
                                <tr class="hover:bg-base-200/30">
                                    <td class="px-4 py-2 text-content-light/50 w-8">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-content-light">{{ $v->victim_name }}</td>
                                    <td class="px-4 py-2 text-right font-bold text-error">{{ $v->total }} muertes</td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-content-light/50">Sin datos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Enemy Victims --}}
                <div class="bg-base-300 rounded-lg border border-base-300">
                    <div class="px-4 py-3 border-b border-success/30 flex justify-between items-center">
                        <h3 class="font-semibold text-content-accent">🎯 Enemigo (Más bajas)</h3>
                        <span class="text-xs text-content-light/50">Mueren ante Linhir</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-base-200">
                            @forelse($topEnemyVictims as $index => $v)
                                <tr class="hover:bg-base-200/30">
                                    <td class="px-4 py-2 text-content-light/50 w-8">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-content-light">{{ $v->victim_name }}</td>
                                    <td class="px-4 py-2 text-right font-bold text-success">{{ $v->total }} muertes</td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-4 text-center text-content-light/50">Sin datos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BATALLAS INVOLUCRADAS --}}
            <div class="bg-base-300 rounded-lg border border-base-300">
                <div class="px-4 py-3 border-b border-primary/30">
                    <h3 class="font-semibold text-content-accent">⚔️ Batallas del Conflicto</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-content-light uppercase border-b border-base-200">
                            <th class="px-4 py-2 text-left">Fecha</th>
                            <th class="px-4 py-2 text-right">ID</th>
                            <th class="px-4 py-2 text-right">Kills Totales</th>
                            <th class="px-4 py-2 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @foreach($battles as $battle)
                            <tr class="hover:bg-base-200/30">
                                <td class="px-4 py-2 text-content-light">{{ $battle->start_time->format('d/m/Y H:i') }} UTC</td>
                                <td class="px-4 py-2 text-right font-mono text-content-light/70">{{ $battle->id }}</td>
                                <td class="px-4 py-2 text-right text-primary font-bold">{{ number_format($battle->total_kills) }}</td>
                                <td class="px-4 py-2 text-right">
                                    <a href="{{ route('battles.show', $battle->id) }}" class="text-primary hover:text-accent text-xs">Ver →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
