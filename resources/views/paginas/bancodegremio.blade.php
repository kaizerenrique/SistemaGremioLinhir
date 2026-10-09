<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-content-light leading-tight">
            {{ __('Banco') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-base-300 border border-base-300 rounded-lg p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-primary/40 mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-semibold text-content-accent mb-2">Banco Gremial</h3>
                <p class="text-content-light/70">Este módulo estará disponible próximamente.</p>
            </div>
        </div>
    </div>
</x-app-layout>
