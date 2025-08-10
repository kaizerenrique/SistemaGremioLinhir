<footer class="bg-base-100 border-t border-primary/20">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Logo y Nombre -->
            <div class="flex flex-col items-center md:items-start space-y-4">
                <div class="flex items-center gap-3 hover:scale-110 transition-transform">
                    <img src="{{ asset('imagenes/escudo_512x512.png') }}" alt="Linhir Logo" class="h-12 w-12">
                    <span class="text-2xl font-bold text-primary">LINHIR</span>
                </div>
                <p class="text-content-light text-sm text-center md:text-left">
                    Gremio de Farmers & Crafters<br>
                    Fort Sterling - Albion Online
                </p>
            </div>
            <!-- Redes Sociales -->
            <div class="flex space-x-6">
                <a href="https://discord.gg/tu-enlace" target="_blank" rel="noopener"
                    class="text-content-light hover:text-[#5865F2] transition-colors">
                    <i class="fab fa-discord text-3xl"></i>
                </a>
                <a href="https://youtube.com/tu-canal" target="_blank" rel="noopener"
                    class="text-content-light hover:text-[#FF0000] transition-colors">
                    <i class="fab fa-youtube text-3xl"></i>
                </a>
                <button class="bg-primary inline-flex py-3 px-5 rounded-lg items-center hover:bg-hover-primary focus:outline-none transition duration-300 transform hover:scale-[1.02]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-6 h-6 text-content-accent" viewBox="0 0 512 512">
                        <path d="M99.617 8.057a50.191 50.191 0 00-38.815-6.713l230.932 230.933 74.846-74.846L99.617 8.057zM32.139 20.116c-6.441 8.563-10.148 19.077-10.148 30.199v411.358c0 11.123 3.708 21.636 10.148 30.199l235.877-235.877L32.139 20.116zM464.261 212.087l-67.266-37.637-81.544 81.544 81.548 81.548 67.273-37.64c16.117-9.03 25.738-25.442 25.738-43.908s-9.621-34.877-25.749-43.907zM291.733 279.711L60.815 510.629c3.786.891 7.639 1.371 11.492 1.371a50.275 50.275 0 0027.31-8.07l266.965-149.372-74.849-74.847z"></path>
                    </svg>
                    <span class="ml-4 flex items-start flex-col leading-none">
                        <span class="text-xs text-content-accent mb-1">GET IT ON</span>
                        <span class="title-font font-medium text-content-light">Google Play</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Derechos -->
        <div class="mt-8 pt-6 border-t border-primary/10">
            <p class="text-center text-content-light text-sm">
                &copy; {{ now()->year }} Linhir. Todos los derechos reservados.<br>
            </p>
        </div>
    </div>
</footer>
