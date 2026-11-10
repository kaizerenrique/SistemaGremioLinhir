<div class="space-y-6">
    
    <!-- ========================================== -->
    <!-- FILA 1: KPIs ECONÓMICOS Y DE GUERRA        -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Precio del Oro -->
        <div class="bg-base-300 p-4 rounded-lg border border-accent/30 flex items-center gap-4 shadow-sm">
            <div class="bg-accent/20 p-3 rounded-full text-accent shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-content-light/60 uppercase tracking-wider">Precio del Oro</p>
                <p class="text-2xl font-bold text-content-accent">{{ number_format($goldPrice->price ?? 0) }} <span class="text-sm font-normal text-content-light">Plata</span></p>
            </div>
        </div>

        <!-- Banco Gremial -->
        <div class="bg-base-300 p-4 rounded-lg border border-success/30 flex items-center gap-4 shadow-sm">
            <div class="bg-success/20 p-3 rounded-full text-success shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-content-light/60 uppercase tracking-wider">Banco Gremial</p>
                <p class="text-2xl font-bold text-success">{{ number_format($guildBalance) }} <span class="text-sm font-normal text-content-light">Plata</span></p>
            </div>
        </div>

        <!-- K/D Semanal -->
        <div class="bg-base-300 p-4 rounded-lg border border-primary/30 flex items-center gap-4 shadow-sm">
            <div class="bg-primary/20 p-3 rounded-full text-primary shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-content-light/60 uppercase tracking-wider">K/D Semanal</p>
                <p class="text-2xl font-bold {{ $weeklyKdr >= 1 ? 'text-success' : 'text-error' }}">{{ $weeklyKdr }}</p>
            </div>
        </div>

        <!-- Silver Distribuido -->
        <div class="bg-base-300 p-4 rounded-lg border border-base-300 flex items-center gap-4 shadow-sm">
            <div class="bg-info/20 p-3 rounded-full text-info shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-content-light/60 uppercase tracking-wider">Silver Actividades</p>
                <p class="text-2xl font-bold text-info">{{ number_format($weeklyActivitySilver) }}</p>
                <p class="text-xs text-content-light/50">Esta semana</p>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FILA 2: COMUNIDAD Y ALIADOS                -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Integrantes Linhir -->
        <div class="bg-base-300 p-4 rounded-lg border border-primary/30 flex items-center gap-3">
            <div class="bg-primary/20 p-2 rounded-full text-primary shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-content-light/60 uppercase">Miembros Linhir</p>
                <p class="text-xl font-bold text-content-accent truncate">{{ $totalMembers }}</p>
            </div>
        </div>

        <!-- Gremios Aliados -->
        <div class="bg-base-300 p-4 rounded-lg border border-secondary/30 flex items-center gap-3">
            <div class="bg-secondary/20 p-2 rounded-full text-secondary shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-content-light/60 uppercase">Aliados (HRL)</p>
                <p class="text-xl font-bold text-secondary truncate">{{ $alliedGuildsCount }} <span class="text-xs font-normal text-content-light/60">gremios</span></p>
                <p class="text-xs text-content-light/50">{{ $alliedMembersCount }} integrantes</p>
            </div>
        </div>

        <!-- Discord Server -->
        <div class="bg-[#5865F2]/10 p-4 rounded-lg border border-[#5865F2]/30 flex items-center gap-3">
            @if($discordGuildInfo && isset($discordGuildInfo['icon']))
                <img src="https://cdn.discordapp.com/icons/{{ $discordGuildInfo['id'] }}/{{ $discordGuildInfo['icon'] }}.png" 
                     alt="Discord Icon" 
                     class="w-10 h-10 rounded-full shrink-0 bg-base-300">
            @else
                <div class="bg-[#5865F2]/20 p-2 rounded-full text-[#5865F2] shrink-0">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"/></svg>
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-xs text-[#5865F2] font-semibold uppercase">Discord</p>
                <p class="text-xl font-bold text-content-accent truncate">{{ $discordMembersCount }}</p>
            </div>
        </div>

        <!-- Vinculación Web/Discord -->
        <div class="bg-base-300 p-4 rounded-lg border border-base-300 flex items-center gap-3">
            <div class="bg-accent/20 p-2 rounded-full text-accent shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-content-light/60 uppercase">Vinculados Web</p>
                <p class="text-xl font-bold text-content-accent truncate">{{ $linkPercentage }}%</p>
                <p class="text-xs text-content-light/50">{{ $linkedMembers }} de {{ $totalMembers }}</p>
            </div>
        </div>

        <!-- Top Recolector -->
        <div class="bg-base-300 p-4 rounded-lg border border-success/30">
            <p class="text-xs text-content-light/60 uppercase mb-1">🏆 Top Recolector</p>
            @if($topGatherer)
                <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-success truncate">{{ $topGatherer->Name }}</span>
                </div>
                <p class="text-xs text-content-light/50">+{{ number_format($topGatherer->ganancia, 0, ',', '.') }} de fama</p>
            @else
                <p class="text-sm text-content-light/50">Sin actividad</p>
            @endif
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FILA 3: FLUJO DE MIEMBROS Y ALIADOS        -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Últimos en unirse a Linhir -->
        <div class="bg-base-300 rounded-lg border border-base-300 p-4 shadow-sm">
            <h3 class="text-md font-bold text-success mb-3 border-b border-success/20 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Nuevos en Linhir
            </h3>
            <ul class="space-y-3">
                @forelse($recentJoins as $join)
                    <li class="flex justify-between items-center text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-content-light font-medium truncate max-w-[150px]">{{ $join->Name }}</span>
                            @if($join->discord_user_id)
                                <span title="Vinculado a Discord" class="text-success text-xs">✅</span>
                            @endif
                        </div>
                        <span class="text-xs text-content-light/50 whitespace-nowrap">{{ $join->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="text-sm text-content-light/50 text-center py-2">No hay registros recientes.</li>
                @endforelse
            </ul>
        </div>

        <!-- Últimos aliados en ingresar -->
        <div class="bg-base-300 rounded-lg border border-base-300 p-4 shadow-sm">
            <h3 class="text-md font-bold text-accent mb-3 border-b border-accent/20 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Nuevos Aliados (HRL)
            </h3>
            <ul class="space-y-3">
                @forelse($recentAlliedJoins as $alliedJoin)
                    <li class="flex justify-between items-center text-sm">
                        <div class="flex flex-col min-w-0 pr-2">
                            <span class="text-content-light font-medium truncate">{{ $alliedJoin->Name }}</span>
                            <span class="text-xs text-primary/80 truncate">
                                {{ $alliedJoin->alliedGuild->guild_name ?? 'Gremio Desconocido' }}
                            </span>
                        </div>
                        <span class="text-xs text-content-light/50 whitespace-nowrap">{{ $alliedJoin->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="text-sm text-content-light/50 text-center py-2">No hay aliados recientes.</li>
                @endforelse
            </ul>
        </div>

        <!-- Últimos en retirarse -->
        <div class="bg-base-300 rounded-lg border border-base-300 p-4 shadow-sm">
            <h3 class="text-md font-bold text-error mb-3 border-b border-error/20 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"></path></svg>
                Retiros de Linhir
            </h3>
            <ul class="space-y-3">
                @forelse($recentLeaves as $leave)
                    <li class="flex justify-between items-center text-sm">
                        <div class="flex flex-col min-w-0 pr-2">
                            <span class="text-content-light font-medium truncate">{{ $leave->Name }}</span>
                            <span class="text-xs text-content-light/50 truncate">
                                @if($leave->GuildId)
                                    Se unió a otro gremio
                                @else
                                    Sin gremio actualmente
                                @endif
                            </span>
                        </div>
                        <span class="text-xs text-content-light/50 whitespace-nowrap">{{ $leave->updated_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="text-sm text-content-light/50 text-center py-2">No hay retiros recientes.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FILA 4: GUERRA Y OPERACIONES               -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna Izquierda: Últimas Batallas -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-base-300 rounded-lg border border-base-300 shadow-sm">
                <div class="p-4 border-b border-primary/20 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-content-accent flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        Últimas Batallas
                    </h3>
                    <a href="{{ route('battles.index') }}" class="text-sm text-primary hover:text-accent font-semibold transition">Ver todas →</a>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($latestBattles as $battle)
                        <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg border border-base-300/50 hover:border-primary/30 transition">
                            <div>
                                <p class="font-semibold text-content-light">Batalla #{{ $battle->id }}</p>
                                <p class="text-xs text-content-light/60">{{ $battle->start_time ? $battle->start_time->format('d/m/Y H:i') : 'N/A' }} UTC</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-primary">{{ number_format($battle->total_kills) }} Kills</p>
                                <a href="{{ route('battles.show', $battle->id) }}" class="text-xs text-accent hover:underline">Ver reporte</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-content-light/50 py-4">No hay batallas recientes.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Operaciones y Admin -->
        <div class="space-y-6">
            <!-- Próxima CTA -->
            @if($nextCta)
            <div class="bg-gradient-to-br from-primary/20 to-base-300 rounded-lg border border-primary/30 p-5 shadow-sm">
                <p class="text-xs font-bold text-primary uppercase tracking-wider mb-1">Próximo Evento</p>
                <h3 class="text-xl font-bold text-content-accent mb-2">{{ $nextCta->title }}</h3>
                <p class="text-sm text-content-light mb-4">📍 {{ $nextCta->location }}</p>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold text-content-light bg-base-200 px-2 py-1 rounded">{{ $nextCta->start_time->diffForHumans() }}</span>
                    <a href="{{ route('ctas.show', $nextCta->id) }}" class="bg-primary hover:bg-hover-primary text-white px-4 py-2 rounded text-sm font-bold transition">Detalles</a>
                </div>
            </div>
            @endif

            <!-- Balance Semanal -->
            <div class="bg-base-300 rounded-lg border border-base-300 p-5 shadow-sm">
                <h3 class="text-md font-bold text-content-accent mb-3 border-b border-primary/20 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Movimientos (Semana)
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center text-success">
                        <span>Ingresos</span>
                        <span class="font-bold">+{{ number_format($weeklyGuildIncome) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-error">
                        <span>Egresos</span>
                        <span class="font-bold">-{{ number_format($weeklyGuildExpense) }}</span>
                    </div>
                    <div class="border-t border-base-200 mt-2 pt-2 flex justify-between items-center font-bold text-content-accent">
                        <span>Balance Neto</span>
                        @php $net = $weeklyGuildIncome - $weeklyGuildExpense; @endphp
                        <span class="{{ $net >= 0 ? 'text-success' : 'text-error' }}">
                            {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tareas y Retiros Pendientes -->
            @can('manage_tasks')
            <div class="bg-base-300 rounded-lg border border-base-300 p-5 shadow-sm">
                <h3 class="text-md font-bold text-content-accent mb-3 border-b border-primary/20 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Requiere tu Atención
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-content-light">Reportes de tareas</span>
                        <span class="{{ $pendingTasks > 0 ? 'bg-warning/20 text-warning' : 'bg-base-200 text-content-light/50' }} font-bold px-3 py-1 rounded-full text-xs">
                            {{ $pendingTasks }} pendientes
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-content-light">Solicitudes de retiro</span>
                        <span class="{{ $pendingWithdrawals > 0 ? 'bg-error/20 text-error' : 'bg-base-200 text-content-light/50' }} font-bold px-3 py-1 rounded-full text-xs">
                            {{ $pendingWithdrawals }} pendientes
                        </span>
                    </div>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>