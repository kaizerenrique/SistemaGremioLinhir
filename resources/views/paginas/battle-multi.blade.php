<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-content-light leading-tight">
            {{ __('Reporte multi-batalla') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(empty($ids))
                <div class="bg-error/10 border border-error/30 rounded-lg p-6 text-error text-center">
                    <p class="text-lg font-semibold">No se recibieron IDs de batalla.</p>
                    <a href="{{ route('battles.index') }}"
                       class="inline-block mt-3 text-primary hover:text-accent underline">
                        Volver a la lista de batallas
                    </a>
                </div>
            @else
                @livewire('modulo.battles.multi-battle-show', ['ids' => $ids])
            @endif
        </div>
    </div>
</x-app-layout>