<nav class="bg-base-100 shadow-lg fixed w-full z-50" x-data="{ isOpen: false, calculatorOpen: false }">
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
                <a href="#team" class="text-content-light hover:text-content-accent transition-colors">Equipo</a>
                <a href="#nosotros" class="text-content-light hover:text-content-accent transition-colors">Nosotros</a>
                <a href="#musica" class="text-content-light hover:text-content-accent transition-colors">Música</a>
                <a href="#reclutamiento" class="text-content-light hover:text-content-accent transition-colors">Reclutamiento</a>
                <a href="{{ url('hrl') }}" class="text-content-light hover:text-content-accent transition-colors">Alianza HRL</a>
                
                <!-- Menú Calculadora Desktop -->
                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button class="text-content-light hover:text-content-accent transition-colors flex items-center">
                        Calculadoras
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div x-show="open" 
                         x-cloak
                         class="absolute left-0 mt-2 w-48 bg-base-100 rounded-md shadow-lg py-1 z-50"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        <a href="#" class="block px-4 py-2 text-sm text-content-light hover:bg-base-300">Item 1</a>
                        <a href="#" class="block px-4 py-2 text-sm text-content-light hover:bg-base-300">Item 2</a>
                        <a href="#" class="block px-4 py-2 text-sm text-content-light hover:bg-base-300">Item 3</a>
                    </div>
                </div>
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
                <a href="#team" class="block text-content-light hover:text-content-accent px-3 py-2">Equipo</a>
                <a href="#musica" class="block text-content-light hover:text-content-accent px-3 py-2">Música</a>
                <a href="#nosotros" class="block text-content-light hover:text-content-accent px-3 py-2">Nosotros</a>
                <a href="#reclutamiento" class="block text-content-light hover:text-content-accent px-3 py-2">Reclutamiento</a>
                <a href="{{ url('hrl') }}" class="block text-content-light hover:text-content-accent px-3 py-2">Alianza HRL</a>
                
                <!-- Menú Calculadora Mobile -->
                <div class="px-3 py-2">
                    <button @click="calculatorOpen = !calculatorOpen" 
                            class="w-full text-left flex justify-between items-center text-content-light hover:text-content-accent">
                        Calculadoras
                        <svg :class="{'rotate-180': calculatorOpen}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="calculatorOpen" class="ml-4 mt-2 space-y-1">
                        <a href="#" class="block px-3 py-2 text-content-light hover:text-content-accent">Item 1</a>
                        <a href="#" class="block px-3 py-2 text-content-light hover:text-content-accent">Item 2</a>
                        <a href="#" class="block px-3 py-2 text-content-light hover:text-content-accent">Item 3</a>
                    </div>
                </div>
                
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
