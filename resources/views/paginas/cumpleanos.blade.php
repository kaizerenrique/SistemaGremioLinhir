<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-content-light leading-tight">
            {{ __('Calendario de Cumpleaños') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('modulo.birthdays.birthday-calendar')
        </div>
    </div>
</x-app-layout>