<nav class="bg-dark shadow-lg fixed w-full z-50" x-data="{ isOpen: false }">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo y nombre -->
            <div class="flex-shrink-0 flex items-center">
                <img src="{{ asset('imagenes/escudo_512x512.png') }}" class="h-8 w-8" alt="Linhir Logo">
                <span class="ml-2 text-light text-xl font-bold">LINHIR</span>
            </div>

            <!-- Menú desktop -->
            <div class="hidden md:flex space-x-8">
                <a href="#hero" class="text-light hover:text-accent transition-colors">Inicio</a>
                <a href="#team" class="text-light hover:text-accent transition-colors">Equipo</a>
                <a href="#nosotros" class="text-light hover:text-accent transition-colors">Nosotros</a>
                <a href="#reclutamiento" class="text-light hover:text-accent transition-colors">Reclutamiento</a>
            </div>

            <!-- Auth links desktop -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="{{ route('login') }}" class="text-light hover:text-accent">Inicio de sesión</a>
                <a href="{{ route('register') }}" class="bg-accent text-white px-4 py-2 rounded hover:bg-hover transition-colors">
                    Registrarse
                </a>
            </div>

            <!-- Botón hamburguesa -->
            <button @click="isOpen = !isOpen" 
                    class="md:hidden text-light hover:text-accent focus:outline-none"
                    aria-label="Menú móvil">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Menú mobile -->
        <div class="md:hidden" 
             x-show="isOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="#" class="block text-light hover:text-accent px-3 py-2">Inicio</a>
                <a href="#" class="block text-light hover:text-accent px-3 py-2">Equipo</a>
                <a href="#" class="block text-light hover:text-accent px-3 py-2">Nosotros</a>
                <a href="#" class="block text-light hover:text-accent px-3 py-2">Reclutamiento</a>
                
                <div class="border-t border-secondary pt-2 mt-2">
                    <a href="{{ route('login') }}" class="block text-light hover:text-accent px-3 py-2">Inicio de sesión</a>
                    <a href="{{ route('register') }}" class="block text-light hover:text-accent px-3 py-2 mt-1">Registrarse</a>
                </div>
            </div>
        </div>
    </div>
</nav>
