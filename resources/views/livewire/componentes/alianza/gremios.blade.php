<section class="py-16 bg-base-100 relative" id="nosotros">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl md:text-5xl font-bold text-content-accent text-center mb-12 animate-slide-in">
            Gremios de la Alianza
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($guilds as $guild)
                <div class="relative bg-base-200 rounded-xl p-4 transition-all hover:transform hover:scale-[1.02] group border border-base-300 hover:border-accent"
                     x-intersect="$el.classList.add('animate-fade-in-up')">
                    
                    <div class="text-center space-y-3">
                        <!-- Logo del gremio -->
                        <div class="mb-3 overflow-hidden rounded-full w-16 h-16 mx-auto bg-base-300 flex items-center justify-center border-2 border-accent/30">
                            <span class="text-lg text-content-light">🏰</span>
                        </div>
                        
                        <!-- Nombre del gremio -->
                        <h3 class="text-lg font-bold text-content-light leading-tight">{{ $guild['name'] }}</h3>
                        
                        <!-- Estadísticas del gremio -->
                        <div class="text-center p-3 bg-base-300 rounded-lg">
                            <div class="text-primary font-bold text-xl">
                                {{ $this->formatNumber($guild['members']) }}
                            </div>
                            <div class="text-xs text-content-light/80">Miembros</div>
                        </div>                      

                        <!-- Botón de Discord -->
                        @if($guild['discord_url'])
                        <div class="pt-1">
                            <a href="{{ $guild['discord_url'] }}" 
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center px-3 py-2 bg-[#5865F2] text-white text-sm font-semibold rounded-lg transition-all duration-300 hover:bg-[#4752c4] hover:transform hover:scale-105 group w-full justify-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.73 4.87a18.2 18.2 0 0 0-4.6-1.44c-.21.4-.4.8-.58 1.21a16.8 16.8 0 0 0-5.1 0c-.18-.41-.37-.82-.59-1.21-1.62.27-3.17.75-4.6 1.44A19 19 0 0 0 .96 17.7a18.4 18.4 0 0 0 5.63 2.87c.45-.6.85-1.24 1.2-1.91a12 12 0 0 1-1.68-.81c.14-.1.28-.21.41-.31a13 13 0 0 0 11.16 0c.13.1.27.21.41.31-.54.32-1.1.6-1.68.81.35.67.75 1.31 1.2 1.91 2.1-.68 4.04-1.74 5.63-2.87.47-4.17-.78-8.23-3.26-11.83zM8.7 14.8c-1.1 0-2-1.02-2-2.3 0-1.27.88-2.3 2-2.3 1.12 0 2.02 1.03 2 2.3 0 1.28-.88 2.3-2 2.3zm6.6 0c-1.1 0-2-1.02-2-2.3 0-1.27.88-2.3 2-2.3 1.12 0 2.02 1.03 2 2.3 0 1.28-.88 2.3-2 2.3z"/>
                                </svg>
                                Discord
                            </a>
                        </div>
                        @else
                        <div class="pt-1">
                            <button class="inline-flex items-center px-3 py-2 bg-base-300 text-content-light/50 text-sm font-semibold rounded-lg cursor-not-allowed w-full justify-center" disabled>
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.73 4.87a18.2 18.2 0 0 0-4.6-1.44c-.21.4-.4.8-.58 1.21a16.8 16.8 0 0 0-5.1 0c-.18-.41-.37-.82-.59-1.21-1.62.27-3.17.75-4.6 1.44A19 19 0 0 0 .96 17.7a18.4 18.4 0 0 0 5.63 2.87c.45-.6.85-1.24 1.2-1.91a12 12 0 0 1-1.68-.81c.14-.1.28-.21.41-.31a13 13 0 0 0 11.16 0c.13.1.27.21.41.31-.54.32-1.1.6-1.68.81.35.67.75 1.31 1.2 1.91 2.1-.68 4.04-1.74 5.63-2.87.47-4.17-.78-8.23-3.26-11.83zM8.7 14.8c-1.1 0-2-1.02-2-2.3 0-1.27.88-2.3 2-2.3 1.12 0 2.02 1.03 2 2.3 0 1.28-.88 2.3-2 2.3zm6.6 0c-1.1 0-2-1.02-2-2.3 0-1.27.88-2.3 2-2.3 1.12 0 2.02 1.03 2 2.3 0 1.28-.88 2.3-2 2.3z"/>
                                </svg>
                                Sin Discord
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>