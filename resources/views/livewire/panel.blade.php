<div>
    <!-- Estadísticas responsive -->
    <div class="text-content-light bg-base-300 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="container px-5 py-5 mx-auto">
            <div class="flex flex-wrap -m-4 text-center">
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full">
                    <div class="border-2 border-primary px-4 py-6 rounded-lg">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" class="text-primary w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        @if ($oro['price'])
                            <h2 class="title-font font-medium text-3xl text-content-light">
                                {{ number_format($oro['price'], 0, '', '.') }} Plata</h2>
                            <p class="leading-relaxed">Valor del Oro actual</p>
                        @else
                        @endif

                    </div>
                </div>
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full">
                    <div class="border-2 border-primary px-4 py-6 rounded-lg">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" class="text-primary w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"></path>
                        </svg>
                        <h2 class="title-font font-medium text-3xl text-white">{{$num}} </h2>
                        <p class="leading-relaxed">Perfiles</p>
                    </div>
                </div>
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full">
                    <div class="border-2 border-primary px-4 py-6 rounded-lg">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" class="text-primary w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                            <path
                                d="M14.82 4.26a10.14 10.14 0 0 0-.53 1.1 14.66 14.66 0 0 0-4.58 0 10.14 10.14 0 0 0-.53-1.1 16 16 0 0 0-4.13 1.3 17.33 17.33 0 0 0-3 11.59 16.6 16.6 0 0 0 5.07 2.59A12.89 12.89 0 0 0 8.23 18a9.65 9.65 0 0 1-1.71-.83 3.39 3.39 0 0 0 .42-.33 11.66 11.66 0 0 0 10.12 0q.21.18.42.33a10.84 10.84 0 0 1-1.71.84 12.41 12.41 0 0 0 1.08 1.78 16.44 16.44 0 0 0 5.06-2.59 17.22 17.22 0 0 0-3-11.59 16.09 16.09 0 0 0-4.09-1.35zM8.68 14.81a1.94 1.94 0 0 1-1.8-2 1.93 1.93 0 0 1 1.8-2 1.93 1.93 0 0 1 1.8 2 1.93 1.93 0 0 1-1.8 2zm6.64 0a1.94 1.94 0 0 1-1.8-2 1.93 1.93 0 0 1 1.8-2 1.92 1.92 0 0 1 1.8 2 1.92 1.92 0 0 1-1.8 2z" />
                        </svg>

                        @if (!$linhir_servidor)
                            <h2 class="title-font font-medium text-3xl text-white">NO</h2>
                        @else
                            <h2 class="title-font font-medium text-3xl text-white">SI</h2>
                        @endif
                        <p class="leading-relaxed">Discord de Linhir</p>

                    </div>
                </div>
                <div class="p-4 md:w-1/4 sm:w-1/2 w-full">
                    <div class="border-2 border-primary px-4 py-6 rounded-lg">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" class="text-primary w-12 h-12 mb-3 inline-block" viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        @if ($algunoEsMiembro == true)
                            <h2 class="title-font font-medium text-3xl text-white">SI</h2>
                        @else
                            <h2 class="title-font font-medium text-3xl text-white">NO</h2>
                        @endif
                        
                        <p class="leading-relaxed">Integrante de Linhir</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Estadísticas responsive -->

    <div class="bg-base-300 sm:rounded-lg mt-4">
        <div class="flex flex-wrap items-center px-4 py-2">
            <div class="relative w-full max-w-full flex-grow flex-1">
                <h3 class="font-semibold text-base text-content-light leading-tight">
                    Personajes
                </h3>
            </div>
            <div class="flex flex-col items-center w-full max-w-xl">
                <x-input class="block mt-1 w-100" type="search" wire:model.live="buscar" placeholder="Buscar" />

            </div>
            <div class="relative w-full max-w-full flex-grow flex-1 text-center mt-1 mx-5">
                <button
                    class="bg-primary hover:bg-hover-primary text-white px-8 py-2 rounded-lg text-base font-semibold transition-all transform hover:scale-105 inline-block">
                    Registrar personaje
                </button>
            </div>
        </div>

        @if (!empty($perfiles))
            <!-- tabla -->
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
                <div class="w-full overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr
                                class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                                <th class="px-4 py-3">Nombre</th>
                                <th class="px-4 py-3">Albion ID</th>
                                <th class="px-4 py-3">Linhir</th>
                                <th class="px-4 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                            @foreach ($perfiles as $perfile)
                                <tr class="text-content-light">
                                    <th
                                        class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                        {{ $perfile->Name }}
                                    </th>
                                    <th
                                        class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                        {{ $perfile->Id_albion}}
                                    </th>
                                    <th
                                        class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                        @if ($perfile->miembro == true)
                                            <span class="bg-neutro text-content-light px-2 py-1 rounded-full text-xs">
                                                SI
                                            </span>
                                        @else
                                            <span class="bg-primary text-content-light px-2 py-1 rounded-full text-xs">
                                                NO
                                            </span>
                                        @endif
                                    </th> 
                                    <!-- Acciones -->
                                    <th
                                        class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                            fill="currentColor" class="size-6">
                                            <path fill-rule="evenodd"
                                                d="M12 6.75a5.25 5.25 0 0 1 6.775-5.025.75.75 0 0 1 .313 1.248l-3.32 3.319c.063.475.276.934.641 1.299.365.365.824.578 1.3.64l3.318-3.319a.75.75 0 0 1 1.248.313 5.25 5.25 0 0 1-5.472 6.756c-1.018-.086-1.87.1-2.309.634L7.344 21.3A3.298 3.298 0 1 1 2.7 16.657l8.684-7.151c.533-.44.72-1.291.634-2.309A5.342 5.342 0 0 1 12 6.75ZM4.117 19.125a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75h-.008a.75.75 0 0 1-.75-.75v-.008Z"
                                                clip-rule="evenodd" />
                                            <path
                                                d="m10.076 8.64-2.201-2.2V4.874a.75.75 0 0 0-.364-.643l-3.75-2.25a.75.75 0 0 0-.916.113l-.75.75a.75.75 0 0 0-.113.916l2.25 3.75a.75.75 0 0 0 .643.364h1.564l2.062 2.062 1.575-1.297Z" />
                                            <path fill-rule="evenodd"
                                                d="m12.556 17.329 4.183 4.182a3.375 3.375 0 0 0 4.773-4.773l-3.306-3.305a6.803 6.803 0 0 1-1.53.043c-.394-.034-.682-.006-.867.042a.589.589 0 0 0-.167.063l-3.086 3.748Zm3.414-1.36a.75.75 0 0 1 1.06 0l1.875 1.876a.75.75 0 1 1-1.06 1.06L15.97 17.03a.75.75 0 0 1 0-1.06Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>
                <div class="mx-4">
                    <span class="px-4 py-3">
                        {{ $perfiles->links('vendor.pagination.tailwind') }}
                    </span>
                </div>
            </div>
        @endif


    </div>
</div>
