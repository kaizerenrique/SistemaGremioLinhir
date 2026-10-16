<div class="space-y-6">
    @if(!$cta)
        <div class="bg-error/10 border border-error/30 rounded-lg p-4 text-error">
            Actividad no encontrada.
        </div>
    @else
        <!-- Cabecera -->
        <div class="bg-base-300 rounded-lg border border-base-300 p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-content-accent">{{ $cta->title }}</h1>
                    <p class="text-content-light/80 text-sm">📍 {{ $cta->location }}</p>
                </div>
                <span class="bg-{{ $cta->status === 'scheduled' ? 'info' : 'neutro' }}/20 text-{{ $cta->status === 'scheduled' ? 'info' : 'neutro' }} px-3 py-1 rounded-full text-xs uppercase">
                    {{ $cta->status }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-xs text-content-light/60 uppercase">Inicio</div>
                    <div class="text-content-light">
                        {{ $cta->start_time->format('d/m/Y H:i') }} UTC
                    </div>
                    <div class="text-xs text-content-light/60">
                        ({{ $cta->start_time->diffForHumans() }})
                    </div>
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
            </div>
        </div>

        <!-- Ocupación de armas -->
        <div class="bg-base-300 rounded-lg border border-base-300 p-6">
            <h3 class="font-semibold text-content-accent mb-4">🎭 Ocupación de armas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($cta->composition->weapons as $weapon)
                    @php $asignado = $ocupacion[$weapon->id] ?? null; @endphp
                    <div class="flex items-center gap-2 p-2 rounded border {{ $asignado ? 'border-primary/40 bg-primary/5' : 'border-base-200' }}">
                        <div class="shrink-0">
                            {!! $weapon->icon_html !!}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-content-light truncate">{{ $weapon->weapon_name }}</div>
                            <div class="text-xs {{ $asignado ? 'text-primary' : 'text-content-light/50' }}">
                                {{ $asignado ?? '*libre*' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Participantes -->
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
    @endif
</div>