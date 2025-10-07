<div class="bg-base-300 sm:rounded-lg">
    <div class="flex flex-wrap items-center px-4 py-2">
        <div class="relative w-full max-w-full flex-grow flex-1">
            <h3 class="font-semibold text-base text-content-light leading-tight">
                Personaje
            </h3>
        </div>
        <div class="flex flex-col items-center w-full max-w-xl">
            <x-input class="block mt-1 w-100" type="search" wire:model.live="buscar" placeholder="Buscar" />

        </div>
        <div class="relative w-full max-w-full flex-grow flex-1 text-center mt-1 mx-5">

        </div>
    </div>

    @if (!empty($informacion))

        <!-- tabla -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Gremio</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-content-light bg-base-300 divide-y divide-primary/30">
                        @foreach ($informacion as $informacio)
                            <tr class="text-content-light">
                                <th
                                    class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    {{ $informacio->Name }}
                                </th>
                                <th
                                    class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    {{ $informacio->GuildName ?? null }}
                                </th>
                                <!-- Acciones -->
                                <th
                                    class="border-t-0 px-4 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left">
                                    <div class="" wire:click="buscarperfil('{{ $informacio->Id }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                        </svg>

                                    </div>
                                </th>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>
        </div>

    @endif

    <!-- Inicio del Modal para Confirmar Perfil -->
    <x-dialog-modal wire:model="modalconfirmarperfil">
        <x-slot name="title">
            {{ $titulo }}
        </x-slot>
        <x-slot name="content">
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
                <div class="w-full overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr
                                class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                                <th class="px-4 py-3">Nombre :</th>
                                <th class="px-4 py-3">{{ $nombre }}</th>
                            </tr>
                            <tr
                                class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                                <th class="px-4 py-3">ID :</th>
                                <th class="px-4 py-3">{{ $idpersonaje }}</th>
                            </tr>
                            <tr
                                class="text-xs font-semibold tracking-wide text-left bg-base-300 text-content-light uppercase border-b border-primary/30">
                                <th class="px-4 py-3">Gremio :</th>
                                <th class="px-4 py-3">{{ $gremio }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <button type="button" wire:click="$toggle('modalconfirmarperfil', false)" wire:loading.attr="disabled"
                class="border border-red-700 bg-red-700 text-white rounded-lg px-4 py-2 m-2 transition duration-500 ease select-none hover:bg-red-800 focus:outline-none focus:shadow-outline">
                {{ __('Cancelar') }}
            </button>
            <button type="button" wire:click="guardarperfil('{{ $idpersonaje }}')" wire:loading.attr="disabled"
                class="border border-emerald-700 bg-emerald-700 text-white rounded-lg px-4 py-2 m-2 transition duration-500 ease select-none hover:bg-emerald-800 focus:outline-none focus:shadow-outline">
                {{ __('Confirmar') }}
            </button>
        </x-slot>
    </x-dialog-modal>
    <!-- Fin del Modal para Confirmar perfil -->

    <!-- Inicio del Modal para mensaje -->
    <x-dialog-modal wire:model="modalmensaje">
        <x-slot name="title">
            {{ $titulo }}
        </x-slot>
        <x-slot name="content">
            <p class="text-content-light">
                {{ $mensaje }}
            </p>
        </x-slot>

        <x-slot name="footer">
            <button type="button" wire:click="$toggle('modalmensaje', false)" wire:loading.attr="disabled"
                class="border border-red-700 bg-red-700 text-white rounded-lg px-4 py-2 m-2 transition duration-500 ease select-none hover:bg-red-800 focus:outline-none focus:shadow-outline">
                {{ __('Cancelar') }}
            </button>
            <button type="button" wire:click="redirectToDashboard" wire:loading.attr="disabled"
                class="border border-emerald-700 bg-emerald-700 text-white rounded-lg px-4 py-2 m-2 transition duration-500 ease select-none hover:bg-emerald-800 focus:outline-none focus:shadow-outline">
                {{ __('Confirmar') }}
            </button>
        </x-slot>
    </x-dialog-modal>
    <!-- Fin del Modal para mensaje -->

</div>
