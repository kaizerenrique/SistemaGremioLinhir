<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-content-light leading-tight">
                {{ __('Crear CTA') }}
            </h2>
            <a href="{{ route('ctas.index') }}"
               class="text-sm text-content-light/70 hover:text-content-accent">
                ← Volver a CTAs
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('modulo.ctas.create-cta')
        </div>
    </div>
</x-app-layout>