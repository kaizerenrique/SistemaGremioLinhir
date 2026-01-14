<div class="bg-base-300 sm:rounded-lg">
    <div class="flex flex-wrap items-center px-4 py-2">
        <div class="relative w-full max-w-full flex-grow flex-1">
            <h3 class="font-semibold text-base text-content-light leading-tight">
                Miembros del Gremio
            </h3>
        </div>
        <div class="flex flex-col items-center w-full max-w-xl">
            <x-input class="block mt-1 w-100" type="search" wire:model.live="buscar" placeholder="Buscar" />
        </div>
        <div class="relative w-full max-w-full flex-grow flex-1 text-center mt-1 mx-5">
            <select wire:model.live="lim"
                class="w-32 border rounded-md shadow-sm bg-base-200 text-content-light placeholder:text-content-light/60 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 disabled:bg-base-300 disabled:opacity-70 transition-colors px-3 py-2">
                <option value="6" selected>6</option>
                <option value="12">12</option>
                <option value="24">24</option>
                <option value="36">36</option>
                <option value="48">48</option>
            </select>
        </div>        
    </div>
    
    <!-- Notificación -->
    @if (session()->has('message'))
        <div class="mx-4 mb-4 px-4 py-3 bg-success/10 border border-success/30 text-success rounded-md animate-fade-in">
            {{ session('message') }}
        </div>
    @endif
    
    <!-- tabla -->
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Usuario Web</th>
                        <th class="px-4 py-3">Discord</th>
                        <th class="px-4 py-3">Fama PVE</th>
                        <th class="px-4 py-3">Fama de crafteo</th>
                        <th class="px-4 py-3">Saldo</th>
                        <th class="px-4 py-3">Cumpleaños</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                    @foreach ($miembros as $miembro)
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $miembro->Name }}
                            </td>
                            
                            <!-- Registrado en la web -->
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                @if (!empty($miembro->user_id))
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-8 text-success">
                                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-8 text-error">
                                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </td>
                            
                            <!-- Registrado en discord -->
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                @if (!empty($miembro->discord_user_id))
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-8 text-success">
                                        <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-8 text-error">
                                        <path fill-rule="evenodd" d="M11.484 2.17a.75.75 0 0 1 1.032 0 11.209 11.209 0 0 0 7.877 3.08.75.75 0 0 1 .722.515 12.74 12.74 0 0 1 .635 3.985c0 5.942-4.064 10.933-9.563 12.348a.749.749 0 0 1-.374 0C6.314 20.683 2.25 15.692 2.25 9.75c0-1.39.223-2.73.635-3.985a.75.75 0 0 1 .722-.516l.143.001c2.996 0 5.718-1.17 7.734-3.08ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75ZM12 15a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75v-.008a.75.75 0 0 0-.75-.75H12Z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </td>
                            
                            <!-- info de fama-->
                            @if ($miembro->lifetimeStatistics)
                                <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    {{ number_format($miembro->lifetimeStatistics->PvE_Total, 0, ',', '.') }}
                                </td>
                                <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    {{ number_format($miembro->lifetimeStatistics->Crafting_Total, 0, ',', '.') }}
                                </td>
                                <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    {{ number_format($miembro->saldoActual(), 0, ',', '.') }}
                                </td>
                            @else
                                <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    No disponible
                                </td>
                                <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    No disponible
                                </td>
                                <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    No disponible
                                </td>
                            @endif

                            <!-- Fecha de cumpleaños -->
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                @if($miembro->birthdate)
                                    <div class="flex items-center space-x-2">
                                        <!-- Icono de calendario para cumpleaños -->
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 text-accent">
                                            <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3a.75.75 0 0 1 1.5 0v1.5h3a.75.75 0 0 1 .75.75v15a.75.75 0 0 1-.75.75H3a.75.75 0 0 1-.75-.75V5.25A.75.75 0 0 1 3 4.5h3V3a.75.75 0 0 1 .75-.75ZM6 6H4.5v3h15V6h-1.5v.75a.75.75 0 0 1-1.5 0V6H6v.75a.75.75 0 0 1-1.5 0V6Zm15 4.5h-15v9h15v-9ZM8.25 15a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                        </svg>
                                        <span>
                                            {{ \Carbon\Carbon::parse($miembro->birthdate)->format('d/m/Y') }}
                                            @if(\Carbon\Carbon::parse($miembro->birthdate)->isBirthday())
                                                <!-- Icono de fiesta para cumpleaños de hoy -->
                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-accent/20 text-accent animate-pulse">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-3 mr-1">
                                                        <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd" />
                                                    </svg>
                                                    ¡Hoy!
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                @else
                                    <span class="text-neutro italic">No registrado</span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <button wire:click="editarinfo({{ $miembro->id }})" class="p-1 hover:bg-base-200 rounded transition-colors group" title="Editar cumpleaños">
                                    <!-- Icono de calendario con edición -->
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 text-content-light/70 group-hover:text-primary group-hover:scale-110 transition-all">
                                        <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                        <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mx-4">
            <span class="px-4 py-3">
                {{ $miembros->links('vendor.pagination.tailwind') }}
            </span>
        </div>
    </div>

    <!-- Modal para editar fecha de cumpleaños -->
    <x-confirmation-modal wire:model="modaleditar">
        <x-slot name="title">
            <div class="flex items-center space-x-2">
                <!-- Icono de calendario para el título del modal -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 text-primary">
                    <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3a.75.75 0 0 1 1.5 0v1.5h3a.75.75 0 0 1 .75.75v15a.75.75 0 0 1-.75.75H3a.75.75 0 0 1-.75-.75V5.25A.75.75 0 0 1 3 4.5h3V3a.75.75 0 0 1 .75-.75ZM6 6H4.5v3h15V6h-1.5v.75a.75.75 0 0 1-1.5 0V6H6v.75a.75.75 0 0 1-1.5 0V6Zm15 4.5h-15v9h15v-9ZM8.25 15a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                </svg>
                <span>Editar Cumpleaños</span>
            </div>
        </x-slot>

        <x-slot name="content">
            @if($nombre_personaje)
                <div class="mb-4 p-3 bg-base-200 rounded-lg">
                    <p class="text-sm text-content-light/80">Personaje:</p>
                    <p class="text-lg font-semibold text-content-light">{{ $nombre_personaje }}</p>
                </div>
            @endif
            
            <div class="space-y-4">
                <div>
                    <x-label for="birthdate" value="Fecha de Cumpleaños" class="text-content-light" />
                    <div class="flex space-x-2 mt-1">
                        <x-input 
                            id="birthdate" 
                            type="date" 
                            class="block w-full"
                            wire:model="birthdate"
                            max="{{ now()->format('Y-m-d') }}"
                        />
                        <x-secondary-button wire:click="limpiarFecha" type="button" title="Limpiar fecha">
                            <!-- Icono de limpiar -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
                            </svg>
                        </x-secondary-button>
                    </div>
                    @error('birthdate') 
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                    
                    @if($birthdate)
                        <div class="mt-2 p-2 bg-base-200 rounded">
                            <p class="text-sm text-content-light">
                                Fecha seleccionada: <span class="font-semibold">{{ \Carbon\Carbon::parse($birthdate)->format('d/m/Y') }}</span>
                            </p>
                        </div>
                    @endif
                </div>
                
                <div class="p-3 bg-base-200/50 rounded border border-primary/20">
                    <p class="text-xs text-content-light/70">
                        <!-- Icono de información -->
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 inline mr-1">
                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                        </svg>
                        La fecha de cumpleaños se mostrará en la tabla y se indicará con un mensaje especial cuando sea el día.
                    </p>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('modaleditar')" wire:loading.attr="disabled">
                Cancelar
            </x-secondary-button>
            <x-button class="ml-2" wire:click="guardarBirthdate" wire:loading.attr="disabled">
                <!-- Icono de guardar (check) -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 mr-1">
                    <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" />
                </svg>
                Guardar Fecha
            </x-button>
        </x-slot>
    </x-confirmation-modal>
</div>