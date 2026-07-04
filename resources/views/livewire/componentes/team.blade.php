<section class="py-20 bg-base-100 relative" id="team">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl md:text-5xl font-bold text-content-accent text-center mb-4 animate-slide-in">
            Maestros de Oficio
        </h2>
        
        <!-- 👇 NUEVO: Indicador de la semana -->
        <p class="text-center text-content-light/70 text-sm md:text-base mb-12">
            <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Ranking semanal: {{ $semanaInicio }} - {{ $semanaFin }}
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($specialists as $specialist)
                <div class="relative bg-base-300 rounded-xl p-4 transition-all hover:transform hover:scale-[1.02] group border border-base-300 hover:border-primary"
                     x-intersect="$el.classList.add('animate-fade-in-up')">
                    
                    <!-- Contenedor imagen -->
                    <div class="mb-4 overflow-hidden rounded-lg w-[217px] h-[217px] mx-auto">
                        <img src="{{ asset('imagenes/' . $specialist['image']) }}" 
                             alt="{{ $specialist['especialidad'] }}"
                             class="w-full h-full object-none rounded-lg transition-transform duration-300 group-hover:scale-105"
                             loading="lazy">
                    </div>
                    
                    <!-- Contenido -->
                    <div class="text-center space-y-3">
                        <h3 class="text-xl font-bold text-content-light leading-tight">{{ $specialist['name'] }}</h3>
                        <p class="text-content-accent font-semibold text-sm uppercase tracking-wide">{{ $specialist['especialidad'] }}</p>
                        
                        <!-- 👇 NUEVO: Indicador de fama semanal -->
                        <div class="flex items-center justify-center space-x-1 text-content-light text-sm">
                            <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            
                            @if($specialist['fame'] > 0)
                                <span class="font-bold text-primary">
                                    +{{ number_format($specialist['fame'], 0, ',', '.') }}
                                </span>
                                <span class="text-xs text-content-light/60">esta semana</span>
                            @else
                                <span class="text-content-light/50 text-sm">Sin actividad semanal</span>
                            @endif
                        </div>
                        
                        <!-- 👇 OPCIONAL: Barra de progreso visual -->
                        @if($loop->index == 0 && $specialist['fame'] > 0)
                            <div class="mt-2 inline-block px-2 py-0.5 bg-accent/20 text-accent text-xs rounded-full border border-accent/30">
                                🏆 Líder de la semana
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
