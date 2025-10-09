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
                ¡Hispanic Republic of Linhir!
            </h2>

            <!-- Imagen centrada del escudo de la alianza -->   
            <div class="flex justify-center mb-6 md:mb-8">
                <img 
                    src="{{ asset('imagenes/lebennin_400x400.png') }}" 
                    alt="Escudo de Hispanic Republic of Linhir"
                    class="w-24 h-24 md:w-36 md:h-36 lg:w-48 lg:h-48 hover:scale-105 transition-transform duration-300"
                >
            </div>

            <!-- Texto descriptivo opcional -->
            <p class="text-content-light text-lg md:text-xl mb-6 max-w-2xl mx-auto px-4">
                Luchamos juntos, crecemos juntos.
            </p>


            <!-- Botón responsive -->
            <a href="#reclutamiento"
                class="inline-block bg-primary hover:bg-hover-primary text-white px-8 md:px-12 py-3 md:py-4 rounded-lg text-base md:text-xl font-bold transition-all hover:scale-105 shadow-lg mt-4">
                ¡Ingresa a Discord!
            </a>
        </div>
    </div>
</section>