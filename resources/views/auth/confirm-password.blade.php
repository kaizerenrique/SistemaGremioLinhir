<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-base-200 border border-base-300 rounded-xl shadow-xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <x-authentication-card-logo class="w-16 h-16" />
                </div>

                <!-- Mensaje informativo -->
                <div class="bg-base-250/40 border border-base-300 rounded-lg p-4 mb-6 text-center">
                    <svg class="w-6 h-6 text-primary mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <p class="text-content-light text-sm">
                        {{ __('Esta es un área segura de la aplicación. Por favor confirma tu contraseña para continuar.') }}
                    </p>
                </div>

                <!-- Errores de validación -->
                <x-validation-errors class="mb-6" />

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                    @csrf

                    <!-- Campo de contraseña -->
                    <div>
                        <x-label for="password" value="{{ __('Contraseña') }}" />
                        <x-input 
                            id="password" 
                            class="block mt-1 w-full" 
                            type="password" 
                            name="password" 
                            required 
                            autocomplete="current-password" 
                            autofocus
                            placeholder="••••••••"
                        />
                    </div>

                    <!-- Botón de confirmación -->
                    <div class="flex justify-end">
                        <x-button class="w-full sm:w-auto">
                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Confirmar') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
