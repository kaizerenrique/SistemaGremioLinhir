<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-content-light leading-tight">
            {{ __('CTA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">  
            @livewire('modulo.ctas.cta-show', ['id' => $id])
        </div>
    </div>
</x-app-layout>