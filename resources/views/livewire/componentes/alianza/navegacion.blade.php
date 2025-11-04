<nav class="bg-base-100 shadow-lg fixed w-full z-50" x-data="{ isOpen: false }">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo y nombre -->
            <div class="flex-shrink-0 flex items-center">
                <a class="flex-shrink-0 flex items-center" href="{{ route('welcome') }}">
                    <img src="{{ asset('imagenes/escudo_512x512.png') }}" class="h-8 w-8" alt="Linhir Logo">
                    <span class="ml-2 text-content-light text-xl font-bold">LINHIR</span>
                </a>                
            </div>

            <!-- Menú desktop -->
            <div class="hidden md:flex space-x-8">
                <a href="#hero" class="text-content-light hover:text-content-accent transition-colors">Inicio</a>
                <a href="#nuestrahistoria" class="text-content-light hover:text-content-accent transition-colors">Nuestra Historia</a>
                <a href="#nosotros" class="text-content-light hover:text-content-accent transition-colors">Gremios de la Alianza</a>
                <a href="#reclutamiento" class="text-content-light hover:text-content-accent transition-colors">Reclutamiento</a>
            </div>

            <!-- Auth links desktop -->
            <div class="hidden md:flex items-center space-x-4">
                @if (Route::has('login'))                            
                    @auth                         
                        <a href="{{ url('dashboard') }}" class="bg-primary text-white px-4 py-2 rounded hover:bg-hover-primary transition-colors">
                            Dashboard
                        </a>
                    @else                            
                        <a href="{{ route('login') }}" class="text-content-light hover:text-content-accent">Inicio de sesión</a>
                        <a href="{{ route('register') }}" class="bg-primary text-white px-4 py-2 rounded hover:bg-hover-primary transition-colors">
                            Registrarse
                        </a>
                    @endauth                            
                @endif
            </div>

            <!-- Botón hamburguesa -->
            <button @click="isOpen = !isOpen" 
                    class="md:hidden text-content-light hover:text-content-accent focus:outline-none"
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
                <a href="#hero" class="block text-content-light hover:text-content-accent px-3 py-2">Inicio</a>
                <a href="#nuestrahistoria" class="block text-content-light hover:text-content-accent px-3 py-2">Nuestra Historia</a>
                <a href="#nosotros" class="block text-content-light hover:text-content-accent px-3 py-2">Gremios de la Alianza</a>
                <a href="#reclutamiento" class="block text-content-light hover:text-content-accent px-3 py-2">Reclutamiento</a>
                
                <div class="border-t border-base-300 pt-2 mt-2">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('dashboard') }}" class="block text-content-light hover:text-content-accent px-3 py-2">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="block text-content-light hover:text-content-accent px-3 py-2">Inicio de sesión</a>
                            <a href="{{ route('register') }}" class="block text-content-light hover:text-content-accent px-3 py-2 mt-1">Registrarse</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </div>
</nav>