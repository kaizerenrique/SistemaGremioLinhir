<div class="bg-base-300 rounded-lg border border-base-300 p-4 sm:p-6 shadow-sm">
    
    {{-- ENCABEZADO Y CONTROLES --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-content-accent capitalize flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
            </svg>
            {{ $monthName }} {{ $year }}
        </h2>
        
        <div class="flex gap-2">
            <x-secondary-button wire:click="previousMonth" title="Mes anterior">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            </x-secondary-button>
            
            <x-secondary-button wire:click="goToCurrentMonth" title="Mes actual">
                Hoy
            </x-secondary-button>

            <x-secondary-button wire:click="nextMonth" title="Mes siguiente">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </x-secondary-button>
        </div>
    </div>

    {{-- DÍAS DE LA SEMANA --}}
    <div class="grid grid-cols-7 gap-1 md:gap-2 mb-2 text-center font-semibold text-content-light/60 text-sm uppercase tracking-wider">
        <div>Lun</div>
        <div>Mar</div>
        <div>Mié</div>
        <div>Jue</div>
        <div>Vie</div>
        <div>Sáb</div>
        <div>Dom</div>
    </div>

    {{-- CUADRÍCULA DEL CALENDARIO --}}
    <div class="grid grid-cols-7 gap-1 md:gap-2">
        @foreach($calendarDays as $day)
            {{-- Día vacío (padding) --}}
            @if($day['day'] === null)
                <div class="min-h-[80px] md:min-h-[100px] bg-base-200/30 rounded-lg"></div>
            @else
                {{-- Día normal --}}
                <div class="min-h-[80px] md:min-h-[100px] p-1 md:p-2 rounded-lg border relative flex flex-col
                    {{ $day['isToday'] ? 'border-primary bg-primary/10' : 'border-base-200 bg-base-200/50' }}">
                    
                    <span class="text-xs md:text-sm font-semibold mb-1 {{ $day['isToday'] ? 'text-primary' : 'text-content-light/70' }}">
                        {{ $day['day'] }}
                    </span>

                    {{-- Cumpleañeros del día --}}
                    <div class="flex-1 overflow-y-auto space-y-1 custom-scrollbar">
                        @foreach($day['birthdays'] as $bday)
                            <div class="bg-base-300 border border-accent/30 rounded text-[10px] md:text-xs px-1 py-0.5 truncate shadow-sm" title="{{ $bday->Name }}">
                                <span class="mr-1">🎂</span>
                                <span class="text-content-light">{{ $bday->Name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- LISTA DETALLADA DEL MES (Para fácil copia/lectura) --}}
    @if($birthdaysThisMonth->count() > 0)
        <div class="mt-8 border-t border-primary/20 pt-6">
            <h3 class="text-lg font-bold text-content-accent mb-4 flex items-center gap-2">
                <span class="text-accent">🎉</span> Cumpleañeros de {{ $monthName }}
                <span class="bg-accent/20 text-accent text-xs px-2 py-0.5 rounded-full">{{ $birthdaysThisMonth->count() }}</span>
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($birthdaysThisMonth as $bday)
                    <div class="flex items-center gap-3 bg-base-200/50 p-2 rounded-lg border border-base-300">
                        <div class="bg-primary/20 text-primary font-bold rounded-full w-10 h-10 flex items-center justify-center shrink-0">
                            {{ \Carbon\Carbon::parse($bday->birthdate)->format('d') }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-content-light truncate">{{ $bday->Name }}</div>
                            <div class="text-xs text-content-light/60 capitalize">
                                {{ \Carbon\Carbon::parse($bday->birthdate)->translatedFormat('l') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-8 text-center text-content-light/50 py-4 border-t border-primary/20">
            No hay cumpleaños registrados para este mes.
        </div>
    @endif
</div>