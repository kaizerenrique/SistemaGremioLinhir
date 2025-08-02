<section class="relative py-16 md:py-24 overflow-hidden bg-dark">
    <!-- Fondo -->
    <div class="absolute inset-0 z-0 bg-dark">
        <div class="absolute inset-0 bg-cover bg-center opacity-30 animate-pan"
            style="background-image: url({{ asset('imagenes/territorios.jpeg') }})"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-dark via-dark/60 to-dark"></div>
    </div>

    <!-- Contenido principal -->
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-5xl lg:text-6xl font-bold text-accent mb-4 md:mb-6 px-2 drop-shadow-xl">
                ¡Las Puertas de Linhir Están Abiertas!
            </h2>

            <!-- Estadísticas responsive -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 md:gap-3 mb-3 md:mb-12">
                <div class="bg-dark/70 p-3 md:p-4 rounded-lg border-2 border-accent/30 backdrop-blur-sm">
                    <div class="text-accent text-xl md:text-2xl font-bold">Numero</div>
                    <div class="text-light text-xs md:text-sm uppercase">Miembros</div>
                </div>
                <div class="bg-dark/70 p-3 md:p-4 rounded-lg border-2 border-accent/30 backdrop-blur-sm">
                    <div class="text-accent text-xl md:text-2xl font-bold">Numero</div>
                    <div class="text-light text-xs md:text-sm uppercase">Fama Total</div>
                </div>
                <!--
                <div class="bg-dark/70 p-3 md:p-4 rounded-lg border-2 border-accent/30 backdrop-blur-sm">
                    <div class="text-accent text-xl md:text-2xl font-bold">1</div>
                    <div class="text-light text-xs md:text-sm uppercase">Territorio</div>
                </div>
                -->
                <div class="bg-dark/70 p-3 md:p-4 rounded-lg border-2 border-accent/30 backdrop-blur-sm">
                    <div class="text-accent text-xl md:text-2xl font-bold">2</div>
                    <div class="text-light text-xs md:text-sm uppercase">HO</div>
                </div>
            </div>

            <!-- Botón responsive -->
            <a href="#reclutamiento"
                class="inline-block bg-accent hover:bg-hover text-white px-8 md:px-12 py-3 md:py-4 rounded-lg text-base md:text-xl font-bold transition-all hover:scale-105 shadow-lg mt-4">
                ¡Únete al Gremio!
            </a>
        </div>
    </div>
</section>
