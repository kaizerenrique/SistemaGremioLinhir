<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-content-light leading-tight">
            {{ __('Dashboard Administrativo') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('modulo.admin.dashboard-overview')
        </div>
    </div>
</x-app-layout>