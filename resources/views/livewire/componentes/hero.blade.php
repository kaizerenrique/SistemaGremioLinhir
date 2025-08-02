<section class="relative h-screen flex items-center overflow-hidden" id="hero">
    <!-- Fondo con overlay -->
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('imagenes/hero.jpg') }}" alt="Fondo hero Linhir"
            class="w-full h-full object-cover animate-fade-in">
        <div class="absolute inset-0 bg-dark/70 backdrop-blur-sm"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
            <!-- Sección texto -->
            <div class="text-center lg:text-left lg:w-1/2 animate-slide-in-left">
                <h1 class="text-4xl md:text-6xl xl:text-7xl font-bold text-accent mb-6">
                    LINHIR
                </h1>

                <p class="text-light text-xl md:text-2xl xl:text-3xl mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Un gremio de crafters y farmers en Albion Online
                </p>

                <a href="#reclutamiento"
                    class="bg-accent hover:bg-hover text-white px-8 py-4 rounded-lg text-lg md:text-xl font-semibold transition-all transform hover:scale-105 inline-block">
                    Únete ahora
                </a>
            </div>

            <!-- Sección escudo -->
            <div class="lg:w-1/2 animate-float">
                <img src="{{ asset('imagenes/escudo_512x512.png') }}" alt="Escudo del gremio Linhir"
                    class="w-full max-w-lg mx-auto lg:max-w-none hover:scale-105 transition-transform duration-300">
            </div>
        </div>
    </div>
</section>
