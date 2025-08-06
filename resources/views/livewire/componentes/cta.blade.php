<section class="relative py-16 md:py-24 overflow-hidden bg-base-100">
    <!-- Fondo -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center opacity-80 animate-pan"
            style="background-image: url({{ asset('imagenes/territorios.jpeg') }})"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-base-100 via-base-100/60 to-base-100"></div>
    </div>

    <!-- Contenido principal -->
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-5xl lg:text-6xl font-bold text-content-accent mb-4 md:mb-6 px-2 drop-shadow-xl">
                ¡Las Puertas de Linhir Están Abiertas!
            </h2>

            <!-- Estadísticas responsive -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 md:gap-3 mb-3 md:mb-12">
                <div class="bg-base-300/70 p-3 md:p-4 rounded-lg border-2 border-primary/30 backdrop-blur-sm">
                    <div class="text-primary text-xl md:text-2xl font-bold">Integrantes</div>
                    <div class="text-content-light text-xs md:text-sm uppercase">{{$linhir_datos}}</div>
                </div>
                <div class="bg-base-300/70 p-3 md:p-4 rounded-lg border-2 border-primary/30 backdrop-blur-sm">
                    <div class="text-primary text-xl md:text-2xl font-bold">Alianza</div>
                    <div class="text-content-light text-xs md:text-sm uppercase">{{$alianza}}</div>
                </div>
                <div class="bg-base-300/70 p-3 md:p-4 rounded-lg border-2 border-primary/30 backdrop-blur-sm">
                    <div class="text-primary text-xl md:text-2xl font-bold">Hideouts</div>
                    <div class="text-content-light text-xs md:text-sm uppercase">{{$ho_gremiales}}</div>
                </div>
            </div>

            <!-- Botón responsive -->
            <a href="#reclutamiento"
                class="inline-block bg-primary hover:bg-hover-primary text-white px-8 md:px-12 py-3 md:py-4 rounded-lg text-base md:text-xl font-bold transition-all hover:scale-105 shadow-lg mt-4">
                ¡Únete al Gremio!
            </a>
        </div>
    </div>
</section>
