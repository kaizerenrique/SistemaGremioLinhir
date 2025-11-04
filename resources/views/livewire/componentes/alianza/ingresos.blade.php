<section class="py-20 bg-base-100 relative" id="reclutamiento">
    <div class="container mx-auto px-4">
        <!-- Encabezado -->
        <div class="text-center mb-16 animate-fade-in">
            <h2 class="text-4xl md:text-5xl font-bold text-content-light mb-4">
                ¿Quieres unirte a <span class="text-accent">nuestra alianza</span>?
            </h2>
            <p class="text-xl text-content-light/80 max-w-3xl mx-auto">
                Estamos buscando gremios activos y comprometidos para fortalecer nuestra comunidad
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-6xl mx-auto">
            <!-- Columna izquierda - Requisitos -->
            <div class="space-y-8">
                <div class="text-center lg:text-left">
                    <h3 class="text-3xl font-bold text-content-light mb-4">
                        Requisitos para <span class="text-accent">nuevos gremios</span>
                    </h3>
                    <p class="text-content-light/70">
                        Buscamos gremios que compartan nuestra visión y valores
                    </p>
                </div>

                <!-- Lista de requisitos -->
                <div class="space-y-6">
                    @foreach($requisitos as $requisito)
                    <div class="bg-base-200 rounded-xl p-6 transition-all duration-300 hover:transform hover:scale-[1.02] group border border-base-300 hover:border-accent/50">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-accent/20 rounded-full flex items-center justify-center text-xl">
                                {{ $requisito['icono'] }}
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-content-light mb-2 group-hover:text-accent transition-colors">
                                    {{ $requisito['titulo'] }}
                                </h4>
                                <p class="text-content-light/80 leading-relaxed">
                                    {{ $requisito['descripcion'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Nota adicional -->
                <div class="bg-primary/10 border border-primary/20 rounded-xl p-6">
                    <div class="flex items-start space-x-3">
                        <span class="text-2xl">💡</span>
                        <div>
                            <h4 class="text-lg font-bold text-content-light mb-2">
                                ¿No cumples todos los requisitos?
                            </h4>
                            <p class="text-content-light/80 text-sm">
                                Contáctanos igualmente. Evaluamos cada caso de forma individual 
                                y podemos hacer excepciones para gremios con mucho potencial.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha - Procedimiento -->
            <div class="space-y-8">
                <div class="text-center lg:text-left">
                    <h3 class="text-3xl font-bold text-content-light mb-4">
                        Procedimiento de <span class="text-primary">ingreso</span>
                    </h3>
                    <p class="text-content-light/70">
                        Sigue estos simples pasos para comenzar el proceso
                    </p>
                </div>

                <!-- Pasos del procedimiento -->
                <div class="space-y-6">
                    @foreach($procedimiento as $paso)
                    <div class="relative bg-base-200 rounded-xl p-6 transition-all duration-300 group border border-base-300">
                        <!-- Número del paso -->
                        <div class="absolute -top-3 -left-3 w-8 h-8 bg-accent rounded-full flex items-center justify-center text-base-100 font-bold text-sm">
                            {{ $paso['paso'] }}
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-primary/20 rounded-full flex items-center justify-center text-lg">
                                {{ $paso['icono'] }}
                            </div>
                            <div class="pt-1">
                                <h4 class="text-lg font-bold text-content-light mb-2">
                                    {{ $paso['titulo'] }}
                                </h4>
                                <p class="text-content-light/80 text-sm leading-relaxed">
                                    {{ $paso['descripcion'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Llamada a la acción -->
                <div class="bg-gradient-to-r from-primary/10 to-accent/10 rounded-xl p-8 text-center border border-base-300">
                    <h4 class="text-2xl font-bold text-content-light mb-4">
                        ¿Listo para unirte?
                    </h4>
                    <p class="text-content-light/80 mb-6">
                        Da el primer paso hacia una alianza fuerte y unida
                    </p>
                    
                    <div class="space-y-4">
                        <a href="{{ $discordUrl }}" 
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center px-8 py-4 bg-[#5865F2] text-white font-bold rounded-xl transition-all duration-300 hover:bg-[#4752c4] hover:transform hover:scale-105 group w-full justify-center text-lg">
                            <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.73 4.87a18.2 18.2 0 0 0-4.6-1.44c-.21.4-.4.8-.58 1.21a16.8 16.8 0 0 0-5.1 0c-.18-.41-.37-.82-.59-1.21-1.62.27-3.17.75-4.6 1.44A19 19 0 0 0 .96 17.7a18.4 18.4 0 0 0 5.63 2.87c.45-.6.85-1.24 1.2-1.91a12 12 0 0 1-1.68-.81c.14-.1.28-.21.41-.31a13 13 0 0 0 11.16 0c.13.1.27.21.41.31-.54.32-1.1.6-1.68.81.35.67.75 1.31 1.2 1.91 2.1-.68 4.04-1.74 5.63-2.87.47-4.17-.78-8.23-3.26-11.83zM8.7 14.8c-1.1 0-2-1.02-2-2.3 0-1.27.88-2.3 2-2.3 1.12 0 2.02 1.03 2 2.3 0 1.28-.88 2.3-2 2.3zm6.6 0c-1.1 0-2-1.02-2-2.3 0-1.27.88-2.3 2-2.3 1.12 0 2.02 1.03 2 2.3 0 1.28-.88 2.3-2 2.3z"/>
                            </svg>
                            Unirse al Discord y Solicitar Reunión
                        </a>
                        
                        <p class="text-content-light/60 text-sm">
                            Te responderemos en menos de 24 horas
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="mt-16 max-w-4xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div class="bg-base-200 rounded-xl p-6">
                    <div class="text-3xl mb-3">⚡</div>
                    <h4 class="font-bold text-content-light mb-2">Respuesta Rápida</h4>
                    <p class="text-content-light/70 text-sm">Te contactamos en menos de 24 horas</p>
                </div>
                <div class="bg-base-200 rounded-xl p-6">
                    <div class="text-3xl mb-3">🛡️</div>
                    <h4 class="font-bold text-content-light mb-2">Soporte Activo</h4>
                    <p class="text-content-light/70 text-sm">Ayuda constante para tu crecimiento</p>
                </div>
                <div class="bg-base-200 rounded-xl p-6">
                    <div class="text-3xl mb-3">🌐</div>
                    <h4 class="font-bold text-content-light mb-2">Comunidad Global</h4>
                    <p class="text-content-light/70 text-sm">Conecta con gremios de todo el mundo</p>
                </div>
            </div>
        </div>
    </div>
</section>
