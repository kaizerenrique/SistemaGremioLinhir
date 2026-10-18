<nav x-data="{ open: false, openSection: null }" class="bg-base-100 border-b border-base-300">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- ========================================================= -->
            <!-- IZQUIERDA: Logo + navegación                              -->
            <!-- ========================================================= -->
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Links desktop (>= lg) -->
                <div class="hidden lg:flex lg:items-center lg:space-x-6 lg:ms-10">

                    <!-- Dashboard (link directo) -->
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @auth

                        <!-- ============================================ -->
                        <!-- GREMIO                                        -->
                        <!-- ============================================ -->
                        @if (auth()->user()->canAny(['Ver Linhir', 'Ver CTAs', 'Gestionar CTAs']))
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button type="button" @click="open = !open"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5
                       {{ request()->routeIs('linhir', 'ranking', 'ctas.*', 'composiciones.*') ? 'border-primary text-content-light' : 'text-content-light/70 hover:text-content-light hover:border-primary' }}
                       focus:outline-none transition duration-150 ease-in-out">
                                    Gremio
                                    <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute left-0 mt-2 w-56 bg-base-200 border border-base-300 rounded-md shadow-lg z-50 py-1">
                                    @can('Ver Linhir')
                                        <x-dropdown-link href="{{ route('linhir') }}">{{ __('Miembros') }}</x-dropdown-link>
                                        <x-dropdown-link
                                            href="{{ route('ranking') }}">{{ __('Ranking de puntos') }}</x-dropdown-link>
                                    @endcan
                                    @can('Ver CTAs')
                                        <x-dropdown-link
                                            href="{{ route('ctas.index') }}">{{ __('Actividades (CTAs)') }}</x-dropdown-link>
                                    @endcan
                                    @can('Gestionar CTAs')
                                        <x-dropdown-link
                                            href="{{ route('composiciones.index') }}">{{ __('Composiciones') }}</x-dropdown-link>
                                    @endcan
                                </div>
                            </div>
                        @endif

                        <!-- ============================================ -->
                        <!-- CONTENIDO                                     -->
                        <!-- ============================================ -->
                        @if (auth()->user()->canAny(['Ver Actividades', 'Ver Batallas']))
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button type="button" @click="open = !open"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5
                                               {{ request()->routeIs('actividades.*', 'battles.*') ? 'border-primary text-content-light' : 'text-content-light/70 hover:text-content-light hover:border-primary' }}
                                               focus:outline-none transition duration-150 ease-in-out">
                                    Contenido
                                    <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute left-0 mt-2 w-56 bg-base-200 border border-base-300 rounded-md shadow-lg z-50 py-1">
                                    @can('Ver Actividades')
                                        <x-dropdown-link
                                            href="{{ route('actividades.index') }}">{{ __('Registro financiero') }}</x-dropdown-link>
                                    @endcan
                                    @can('Ver Batallas')
                                        <x-dropdown-link
                                            href="{{ route('battles.index') }}">{{ __('Batallas') }}</x-dropdown-link>
                                    @endcan
                                </div>
                            </div>
                        @endif

                        <!-- ============================================ -->
                        <!-- FINANZAS                                      -->
                        <!-- ============================================ -->
                        @auth
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button type="button" @click="open = !open"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5
                       {{ request()->routeIs('cuentapersonal', 'cuenta-gremial.*', 'retiros.admin') ? 'border-primary text-content-light' : 'text-content-light/70 hover:text-content-light hover:border-primary' }}
                       focus:outline-none transition duration-150 ease-in-out">
                                    Finanzas
                                    <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute left-0 mt-2 w-56 bg-base-200 border border-base-300 rounded-md shadow-lg z-50 py-1">
                                    {{-- Banco personal: accesible para todos --}}
                                    <x-dropdown-link
                                        href="{{ route('cuentapersonal') }}">{{ __('Banco personal') }}</x-dropdown-link>

                                    @can('Ver Cuenta Gremial')
                                        <x-dropdown-link
                                            href="{{ route('cuenta-gremial.index') }}">{{ __('Cuenta del gremio') }}</x-dropdown-link>
                                    @endcan
                                    @can('Gestionar Cuenta Gremial')
                                        <x-dropdown-link
                                            href="{{ route('retiros.admin') }}">{{ __('Solicitudes de retiro') }}</x-dropdown-link>
                                    @endcan
                                </div>
                            </div>
                        @endauth

                        <!-- ============================================ -->
                        <!-- ADMINISTRACIÓN                                -->
                        <!-- ============================================ -->
                        @if (auth()->user()->canAny(['manage_tasks', 'Ver Usuarios', 'Ver Roles y Permisos', 'Ver Discord']))
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button type="button" @click="open = !open"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5
                                               {{ request()->routeIs('tasks.*', 'usuarios', 'roles', 'discord.*') ? 'border-primary text-content-light' : 'text-content-light/70 hover:text-content-light hover:border-primary' }}
                                               focus:outline-none transition duration-150 ease-in-out">
                                    Admin
                                    <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute left-0 mt-2 w-56 bg-base-200 border border-base-300 rounded-md shadow-lg z-50 py-1">
                                    @can('manage_tasks')
                                        <x-dropdown-link
                                            href="{{ route('tasks.management') }}">{{ __('Gestión de tareas') }}</x-dropdown-link>
                                    @endcan
                                    @can('Ver Usuarios')
                                        <x-dropdown-link
                                            href="{{ route('usuarios') }}">{{ __('Usuarios') }}</x-dropdown-link>
                                    @endcan
                                    @can('Ver Roles y Permisos')
                                        <x-dropdown-link
                                            href="{{ route('roles') }}">{{ __('Roles y permisos') }}</x-dropdown-link>
                                    @endcan
                                    @can('Ver Discord')
                                        <x-dropdown-link
                                            href="{{ route('discord.index') }}">{{ __('Discord server') }}</x-dropdown-link>
                                    @endcan
                                </div>
                            </div>
                        @endif

                    @endauth
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- DERECHA: user menu + hamburguesa                          -->
            <!-- ========================================================= -->
            <div class="hidden lg:flex lg:items-center lg:ms-6">

                <!-- Teams Dropdown (si está habilitado) -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="ms-3 relative">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-content-light bg-base-200 hover:text-primary focus:outline-none focus:bg-base-300 active:bg-base-300 transition ease-in-out duration-150">
                                        {{ Auth::user()->currentTeam->name }}
                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>
                            <x-slot name="content">
                                <div class="w-60 bg-base-200 border border-base-300 rounded-md shadow-lg">
                                    <div class="block px-4 py-2 text-xs text-content-light/80">{{ __('Manage Team') }}
                                    </div>
                                    <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-dropdown-link>
                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-dropdown-link
                                            href="{{ route('teams.create') }}">{{ __('Create New Team') }}</x-dropdown-link>
                                    @endcan
                                    @if (Auth::user()->allTeams()->count() > 1)
                                        <div class="border-t border-base-300"></div>
                                        <div class="block px-4 py-2 text-xs text-content-light/80">
                                            {{ __('Switch Teams') }}</div>
                                        @foreach (Auth::user()->allTeams() as $team)
                                            <x-switchable-team :team="$team" />
                                        @endforeach
                                    @endif
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button
                                    class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-primary transition">
                                    <img class="size-8 rounded-full object-cover"
                                        src="{{ Auth::user()->profile_photo_url }}"
                                        alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-content-light bg-base-200 hover:text-primary focus:outline-none focus:bg-base-300 active:bg-base-300 transition ease-in-out duration-150">
                                        {{ Auth::user()->name }}
                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>
                        <x-slot name="content">
                            <div class="bg-base-200 border border-base-300 rounded-md shadow-lg">
                                <div class="block px-4 py-2 text-xs text-content-light/80">{{ __('Manage Account') }}
                                </div>
                                <x-dropdown-link
                                    href="{{ route('profile.show') }}">{{ __('Profile') }}</x-dropdown-link>
                                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-dropdown-link
                                        href="{{ route('api-tokens.index') }}">{{ __('API Tokens') }}</x-dropdown-link>
                                @endif
                                <div class="border-t border-base-300"></div>
                                <form method="POST" action="{{ route('logout') }}" x-data>
                                    @csrf
                                    <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger (mobile/tablet, < lg) -->
            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-content-light/80 hover:text-primary hover:bg-base-300 focus:outline-none focus:bg-base-300 focus:text-primary transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================= -->
    <!-- RESPONSIVE MENU (mobile / tablet < lg)                        -->
    <!-- ============================================================= -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden lg:hidden bg-base-200 border-t border-base-300">
        <div class="pt-2 pb-3 space-y-1">

            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @auth

                {{-- =========================================== --}}
                {{-- GREMIO                                       --}}
                {{-- =========================================== --}}
                @if (auth()->user()->canAny(['Ver Linhir', 'Ver CTAs', 'Gestionar CTAs']))
                    <div>
                        <button type="button" @click="openSection = openSection === 'gremio' ? null : 'gremio'"
                            class="w-full flex items-center justify-between ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-content-light/80 hover:text-content-light hover:bg-base-300 focus:outline-none transition">
                            <span>Gremio</span>
                            <svg :class="{ 'rotate-180': openSection === 'gremio' }"
                                class="h-4 w-4 transform transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openSection === 'gremio'" x-cloak class="pl-3">
                            @can('Ver Linhir')
                                <x-responsive-nav-link href="{{ route('linhir') }}" :active="request()->routeIs('linhir')">
                                    {{ __('Miembros') }}
                                </x-responsive-nav-link>
                                <x-responsive-nav-link href="{{ route('ranking') }}" :active="request()->routeIs('ranking')">
                                    {{ __('Ranking de puntos') }}
                                </x-responsive-nav-link>
                            @endcan
                            @can('Ver CTAs')
                                <x-responsive-nav-link href="{{ route('ctas.index') }}" :active="request()->routeIs('ctas.*')">
                                    {{ __('Actividades (CTAs)') }}
                                </x-responsive-nav-link>
                            @endcan
                            @can('Gestionar CTAs')
                                <x-responsive-nav-link href="{{ route('composiciones.index') }}" :active="request()->routeIs('composiciones.*')">
                                    {{ __('Composiciones') }}
                                </x-responsive-nav-link>
                            @endcan
                        </div>
                    </div>
                @endif

                {{-- =========================================== --}}
                {{-- CONTENIDO                                    --}}
                {{-- =========================================== --}}
                @if (auth()->user()->canAny(['Ver Actividades', 'Ver Batallas']))
                    <div>
                        <button type="button" @click="openSection = openSection === 'contenido' ? null : 'contenido'"
                            class="w-full flex items-center justify-between ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-content-light/80 hover:text-content-light hover:bg-base-300 focus:outline-none transition">
                            <span>Contenido</span>
                            <svg :class="{ 'rotate-180': openSection === 'contenido' }"
                                class="h-4 w-4 transform transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openSection === 'contenido'" x-cloak class="pl-3">
                            @can('Ver Actividades')
                                <x-responsive-nav-link href="{{ route('actividades.index') }}" :active="request()->routeIs('actividades.*')">
                                    {{ __('Registro financiero') }}
                                </x-responsive-nav-link>
                            @endcan
                            @can('Ver Batallas')
                                <x-responsive-nav-link href="{{ route('battles.index') }}" :active="request()->routeIs('battles.*')">
                                    {{ __('Batallas') }}
                                </x-responsive-nav-link>
                            @endcan
                        </div>
                    </div>
                @endif

                {{-- =========================================== --}}
                {{-- FINANZAS                                     --}}
                {{-- =========================================== --}}
                @auth
                    <div>
                        <button type="button" @click="openSection = openSection === 'finanzas' ? null : 'finanzas'"
                            class="w-full flex items-center justify-between ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-content-light/80 hover:text-content-light hover:bg-base-300 focus:outline-none transition">
                            <span>Finanzas</span>
                            <svg :class="{ 'rotate-180': openSection === 'finanzas' }"
                                class="h-4 w-4 transform transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openSection === 'finanzas'" x-cloak class="pl-3">
                            <x-responsive-nav-link href="{{ route('cuentapersonal') }}" :active="request()->routeIs('cuentapersonal')">
                                {{ __('Banco personal') }}
                            </x-responsive-nav-link>

                            @can('Ver Cuenta Gremial')
                                <x-responsive-nav-link href="{{ route('cuenta-gremial.index') }}" :active="request()->routeIs('cuenta-gremial.*')">
                                    {{ __('Cuenta del gremio') }}
                                </x-responsive-nav-link>
                            @endcan
                            @can('Gestionar Cuenta Gremial')
                                <x-responsive-nav-link href="{{ route('retiros.admin') }}" :active="request()->routeIs('retiros.admin')">
                                    {{ __('Solicitudes de retiro') }}
                                </x-responsive-nav-link>
                            @endcan
                        </div>
                    </div>
                @endauth

                {{-- =========================================== --}}
                {{-- ADMINISTRACIÓN                               --}}
                {{-- =========================================== --}}
                @if (auth()->user()->canAny(['manage_tasks', 'Ver Usuarios', 'Ver Roles y Permisos', 'Ver Discord']))
                    <div>
                        <button type="button" @click="openSection = openSection === 'admin' ? null : 'admin'"
                            class="w-full flex items-center justify-between ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-content-light/80 hover:text-content-light hover:bg-base-300 focus:outline-none transition">
                            <span>Administración</span>
                            <svg :class="{ 'rotate-180': openSection === 'admin' }"
                                class="h-4 w-4 transform transition-transform" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="openSection === 'admin'" x-cloak class="pl-3">
                            @can('manage_tasks')
                                <x-responsive-nav-link href="{{ route('tasks.management') }}" :active="request()->routeIs('tasks.*')">
                                    {{ __('Gestión de tareas') }}
                                </x-responsive-nav-link>
                            @endcan
                            @can('Ver Usuarios')
                                <x-responsive-nav-link href="{{ route('usuarios') }}" :active="request()->routeIs('usuarios')">
                                    {{ __('Usuarios') }}
                                </x-responsive-nav-link>
                            @endcan
                            @can('Ver Roles y Permisos')
                                <x-responsive-nav-link href="{{ route('roles') }}" :active="request()->routeIs('roles')">
                                    {{ __('Roles y permisos') }}
                                </x-responsive-nav-link>
                            @endcan
                            @can('Ver Discord')
                                <x-responsive-nav-link href="{{ route('discord.index') }}" :active="request()->routeIs('discord.*')">
                                    {{ __('Discord server') }}
                                </x-responsive-nav-link>
                            @endcan
                        </div>
                    </div>
                @endif

            @endauth
        </div>

        <!-- ========================================================= -->
        <!-- Responsive Settings Options                                -->
        <!-- ========================================================= -->
        <div class="pt-4 pb-1 border-t border-base-300">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="size-10 rounded-full object-cover border border-base-300"
                            src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif
                <div>
                    <div class="font-medium text-base text-content-light">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-content-light/80">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-t border-base-300"></div>
                    <div class="block px-4 py-2 text-xs text-content-light/80">{{ __('Manage Team') }}</div>
                    <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}"
                        :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-responsive-nav-link>
                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>
                    @endcan
                    @if (Auth::user()->allTeams()->count() > 1)
                        <div class="border-t border-base-300"></div>
                        <div class="block px-4 py-2 text-xs text-content-light/80">{{ __('Switch Teams') }}</div>
                        @foreach (Auth::user()->allTeams() as $team)
                            <x-switchable-team :team="$team" component="responsive-nav-link" />
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </div>
</nav>
