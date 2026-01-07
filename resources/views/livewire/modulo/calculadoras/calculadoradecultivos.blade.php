<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-base-300 sm:rounded-lg">
        <!-- Encabezado con controles -->
        <div class="flex flex-wrap items-center px-4 py-2">
            <div class="relative w-full max-w-full flex-grow flex-1">
                <h3 class="font-semibold text-base text-content-light leading-tight">
                    Calculadora de Cultivos
                </h3>
            </div>
            <div class="relative w-full max-w-full flex-grow flex-1 text-right mt-1">
                <!-- Selector de Parcelas Ajustado -->
                <span class="ml-2 text-sm text-content-light">{{ __('Parcelas') }}</span>
                <select wire:model.live="parcelas" 
                        class="ml-2 w-16 bg-base-100 border border-primary/30 rounded-md px-2 py-1 text-content-light text-sm focus:border-primary focus:ring-primary">
                    @for($i = 1; $i <= 16; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <!-- Configuración rápida -->
        <div class="flex flex-wrap items-center px-4 py-2 bg-base-200/50">
            <div class="flex space-x-6">
                <div class="flex items-center">
                    <x-checkbox name="premium" wire:model.live="premium" />
                    <span class="ml-2 text-sm text-content-light">{{ __('Premium ') }}</span>
                    <span class="ml-2 text-sm text-content-light">{{ __('Impuesto: ') }} {{$imp}}%</span>
                </div>
                
                <div class="flex items-center">
                    <x-checkbox name="foco" wire:model.live="foco" />
                    <span class="ml-2 text-sm text-content-light">{{ __('Uso Foco ') }}</span>
                </div>
                
                <div class="flex items-center">
                    <x-checkbox name="bono" wire:model.live="bono" />
                    <span class="ml-2 text-sm text-content-light">{{ __('Bono de Ciudad ') }}</span>
                </div>

                <div class="flex items-center">
                    <button wire:click="resetear" class="text-sm bg-primary/20 text-primary px-3 py-1 rounded hover:bg-primary/30 transition-colors">
                        Resetear
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de Cultivos -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                            <th class="px-4 py-3">Ciudad</th>
                            <th class="px-4 py-3">Semilla</th>
                            <th class="px-4 py-3">Precio Semilla</th>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3">Precio Producto</th>
                            <th class="px-4 py-3">% Retorno</th>
                            <th class="px-4 py-3">Semillas Ret.</th>
                            <th class="px-4 py-3">Profit</th>
                        </tr>
                    </thead>
                    <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                        
                        <!-- Zanahoria -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_carrot['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T1_FARM_CARROT_SEED.png') }}" 
                                        alt="Zanahoria" 
                                        class="w-8 h-8 rounded"
                                        title="Zanahoria">
                                    <span class="hidden md:inline">Zanahoria</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="carrotseed" 
                                        wire:model.live="carrotseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T1_CARROT.png') }}" 
                                    alt="Zanahoria" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="carrot" 
                                        wire:model.live="carrot" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_carrot['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_carrot['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_carrot['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_carrot['profit']) }}
                            </td>
                        </tr>

                        <!-- Frijol -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_bean['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T2_FARM_BEAN_SEED.png') }}" 
                                        alt="Frijol" 
                                        class="w-8 h-8 rounded"
                                        title="Frijol">
                                    <span class="hidden md:inline">Frijol</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="beanseed" 
                                        wire:model.live="beanseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T2_BEAN.png') }}" 
                                    alt="Frijol" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="bean" 
                                        wire:model.live="bean" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_bean['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_bean['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_bean['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_bean['profit']) }}
                            </td>
                        </tr>

                        <!-- Trigo -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_wheat['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T3_FARM_WHEAT_SEED.png') }}" 
                                        alt="Trigo" 
                                        class="w-8 h-8 rounded"
                                        title="Trigo">
                                    <span class="hidden md:inline">Trigo</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="wheatseed" 
                                        wire:model.live="wheatseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T3_WHEAT.png') }}" 
                                    alt="Trigo" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="wheat" 
                                        wire:model.live="wheat" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_wheat['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_wheat['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_wheat['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_wheat['profit']) }}
                            </td>
                        </tr>

                        <!-- Rábano -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_turnip['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T4_FARM_TURNIP_SEED.png') }}" 
                                        alt="Rábano" 
                                        class="w-8 h-8 rounded"
                                        title="Rábano">
                                    <span class="hidden md:inline">Rábano</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="turnipseed" 
                                        wire:model.live="turnipseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T4_TURNIP.png') }}" 
                                    alt="Rábano" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="turnip" 
                                        wire:model.live="turnip" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_turnip['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_turnip['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_turnip['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_turnip['profit']) }}
                            </td>
                        </tr>

                        <!-- Col -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_cabbage['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T5_FARM_CABBAGE_SEED.png') }}" 
                                        alt="Col" 
                                        class="w-8 h-8 rounded"
                                        title="Col">
                                    <span class="hidden md:inline">Col</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="cabbageseed" 
                                        wire:model.live="cabbageseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T5_CABBAGE.png') }}" 
                                    alt="Col" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="cabbage" 
                                        wire:model.live="cabbage" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_cabbage['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_cabbage['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_cabbage['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_cabbage['profit']) }}
                            </td>
                        </tr>

                        <!-- Papa -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_potato['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T6_FARM_POTATO_SEED.png') }}" 
                                        alt="Papa" 
                                        class="w-8 h-8 rounded"
                                        title="Papa">
                                    <span class="hidden md:inline">Papa</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="potatoseed" 
                                        wire:model.live="potatoseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T6_POTATO.png') }}" 
                                    alt="Papa" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="potato" 
                                        wire:model.live="potato" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_potato['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_potato['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_potato['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_potato['profit']) }}
                            </td>
                        </tr>

                        <!-- Maíz -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_corn['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T7_FARM_CORN_SEED.png') }}" 
                                        alt="Maíz" 
                                        class="w-8 h-8 rounded"
                                        title="Maíz">
                                    <span class="hidden md:inline">Maíz</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="cornseed" 
                                        wire:model.live="cornseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T7_CORN.png') }}" 
                                    alt="Maíz" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="corn" 
                                        wire:model.live="corn" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_corn['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_corn['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_corn['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_corn['profit']) }}
                            </td>
                        </tr>

                        <!-- Calabaza -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_punpkin['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T8_FARM_PUMPKIN_SEED.png') }}" 
                                        alt="Calabaza" 
                                        class="w-8 h-8 rounded"
                                        title="Calabaza">
                                    <span class="hidden md:inline">Calabaza</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="punpkinseed" 
                                        wire:model.live="punpkinseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T8_PUMPKIN.png') }}" 
                                    alt="Calabaza" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="punpkin" 
                                        wire:model.live="punpkin" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_punpkin['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_punpkin['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_punpkin['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_punpkin['profit']) }}
                            </td>
                        </tr>

                        <!-- Separador Cultivos Especiales -->
                        <tr class="bg-base-200/70">
                            <td colspan="8" class="px-4 py-2 text-center">
                                <span class="text-sm font-semibold text-content-light">Cultivos Especiales</span>
                            </td>
                        </tr>

                        <!-- Agárico Arcano -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_agaric['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T2_FARM_AGARIC_SEED.png') }}" 
                                        alt="Agárico Arcano" 
                                        class="w-8 h-8 rounded"
                                        title="Agárico Arcano">
                                    <span class="hidden md:inline">Agárico Arcano</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="agaricseed" 
                                        wire:model.live="agaricseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T2_AGARIC.png') }}" 
                                    alt="Agárico Arcano" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="agaric" 
                                        wire:model.live="agaric" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_agaric['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_agaric['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_agaric['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_agaric['profit']) }}
                            </td>
                        </tr>

                        <!-- Consuelda Hoja Brillante -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_comfrey['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T3_FARM_COMFREY_SEED.png') }}" 
                                        alt="Consuelda Hoja Brillante" 
                                        class="w-8 h-8 rounded"
                                        title="Consuelda Hoja Brillante">
                                    <span class="hidden md:inline">Consuelda Hoja Brillante</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="comfreyseed" 
                                        wire:model.live="comfreyseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T3_COMFREY.png') }}" 
                                    alt="Consuelda Hoja Brillante" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="comfrey" 
                                        wire:model.live="comfrey" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_comfrey['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_comfrey['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_comfrey['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_comfrey['profit']) }}
                            </td>
                        </tr>
                        
                        <!-- Consuelda Bardana Almenada -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_burdock['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T4_FARM_BURDOCK_SEED.png') }}" 
                                        alt="Bardana Almenada" 
                                        class="w-8 h-8 rounded"
                                        title="Bardana Almenada">
                                    <span class="hidden md:inline">Bardana Almenada</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="burdockseed" 
                                        wire:model.live="burdockseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T4_BURDOCK.png') }}" 
                                    alt="Bardana Almenada" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="burdock" 
                                        wire:model.live="burdock" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_burdock['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_burdock['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_burdock['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_burdock['profit']) }}
                            </td>
                        </tr> 

                        <!-- Consuelda Cardo de dragón -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_teasel['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T5_FARM_TEASEL_SEED.png') }}" 
                                        alt="Cardo de dragón" 
                                        class="w-8 h-8 rounded"
                                        title="Cardo de dragón">
                                    <span class="hidden md:inline">Cardo de dragón</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="teaselseed" 
                                        wire:model.live="teaselseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T5_TEASEL.png') }}" 
                                    alt="Cardo de dragón" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="teasel" 
                                        wire:model.live="teasel" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_teasel['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_teasel['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_teasel['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_teasel['profit']) }}
                            </td>
                        </tr> 

                        <!-- Consuelda Dedalera elusiva -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_foxglove['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T6_FARM_FOXGLOVE_SEED.png') }}" 
                                        alt="Dedalera elusiva" 
                                        class="w-8 h-8 rounded"
                                        title="Dedalera elusiva">
                                    <span class="hidden md:inline">Dedalera elusiva</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="foxgloveseed" 
                                        wire:model.live="foxgloveseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T6_FOXGLOVE.png') }}" 
                                    alt="Dedalera elusiva" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="foxglove" 
                                        wire:model.live="foxglove" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_foxglove['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_foxglove['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_foxglove['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_foxglove['profit']) }}
                            </td>
                        </tr> 

                        <!-- Consuelda Gordolobo de fuego -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_mullein['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T7_FARM_MULLEIN_SEED.png') }}" 
                                        alt="Gordolobo de fuego" 
                                        class="w-8 h-8 rounded"
                                        title="Gordolobo de fuego">
                                    <span class="hidden md:inline">Gordolobo de fuego</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="mulleinseed" 
                                        wire:model.live="mulleinseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T7_MULLEIN.png') }}" 
                                    alt="Gordolobo de fuego" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="mullein" 
                                        wire:model.live="mullein" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_mullein['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_mullein['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_mullein['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_mullein['profit']) }}
                            </td>
                        </tr> 

                        <!-- Consuelda Milenrama demoníaca -->
                        <tr class="text-content-light hover:bg-base-200/50 transition-colors">
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_yarrow['ciudad'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('imagenes/cultivos/T8_FARM_YARROW_SEED.png') }}" 
                                        alt="Milenrama demoníaca" 
                                        class="w-8 h-8 rounded"
                                        title="Milenrama demoníaca">
                                    <span class="hidden md:inline">Milenrama demoníaca</span>
                                </div>
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="yarrowseed" 
                                        wire:model.live="yarrowseed" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <img src="{{ asset('imagenes/cultivos/T8_YARROW.png') }}" 
                                    alt="Milenrama demoníaca" 
                                    class="w-8 h-8 rounded mx-auto">
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                <x-input class="block w-full" type="number" 
                                        name="yarrow" 
                                        wire:model.live="yarrow" 
                                        min="0" />
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_yarrow['retorno'] }}%
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                {{ $r_yarrow['r_semillas'] }}
                            </td>
                            <td class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left font-semibold 
                                {{ $r_yarrow['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                {{ number_format($r_yarrow['profit']) }}
                            </td>
                        </tr> 

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resumen Total -->
        <div class="flex flex-wrap items-center px-4 py-3 bg-base-200/50 border-t border-primary/30">
            <div class="flex justify-between items-center w-full">
                <div class="text-sm text-content-light">
                    <span class="font-semibold">Profit Total: </span>
                    <span class="{{ $totalProfit >= 0 ? 'text-green-500' : 'text-red-500' }} font-bold">
                        {{ number_format($totalProfit) }}
                    </span>
                </div>
                <div class="text-sm text-content-light">
                    <span class="font-semibold">Semillas Totales: </span>
                    <span class="font-bold">{{ $parcelas * 9 }}</span>
                </div>
                <div class="text-sm text-content-light">
                    <span class="font-semibold">Configuración: </span>
                    <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-full">
                        {{ $premium ? 'Premium' : 'Básico' }} • 
                        {{ $foco ? 'Con Foco' : 'Sin Foco' }} • 
                        {{ $bono ? 'Con Bono' : 'Sin Bono' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
