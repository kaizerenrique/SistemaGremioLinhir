<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-base-200 border border-base-300 rounded-xl shadow-xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <x-authentication-card-logo class="w-16 h-16" />
                </div>

                <div x-data="{ recovery: false }" class="space-y-6">
                    <!-- Mensaje de autenticación -->
                    <div class="bg-base-250/40 border border-base-300 rounded-lg p-4 text-center" x-show="! recovery">
                        <svg class="w-8 h-8 text-primary mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <p class="text-content-light text-sm">
                            {{ __('Por favor confirma el acceso a tu cuenta ingresando el código de autenticación de tu aplicación.') }}
                        </p>
                    </div>

                    <!-- Mensaje de recuperación -->
                    <div class="bg-base-250/40 border border-base-300 rounded-lg p-4 text-center" x-cloak
                        x-show="recovery">
                        <svg class="w-8 h-8 text-accent mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <p class="text-content-light text-sm">
                            {{ __('Por favor confirma el acceso usando uno de tus códigos de recuperación de emergencia.') }}
                        </p>
                    </div>

                    <!-- Errores de validación -->
                    <x-validation-errors class="mb-4" />

                    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
                        @csrf

                        <!-- Código de autenticación -->
                        <div x-show="! recovery" class="space-y-3">
                            <x-label for="code" value="{{ __('Código de Autenticación') }}" />
                            <x-input id="code" class="block w-full text-center tracking-[0.5em] font-mono"
                                type="text" inputmode="numeric" name="code" autofocus x-ref="code"
                                autocomplete="one-time-code" placeholder="••••••" maxlength="6" />
                            <p class="text-xs text-content-light/70 mt-1">
                                {{ __('Ingresa el código de 6 dígitos de tu aplicación') }}
                            </p>
                        </div>

                        <!-- Código de recuperación -->
                        <div x-cloak x-show="recovery" class="space-y-3">
                            <x-label for="recovery_code" value="{{ __('Código de Recuperación') }}" />
                            <x-input id="recovery_code" class="block w-full font-mono" type="text"
                                name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code"
                                placeholder="XXXX-XXXX-XXXX" />
                            <p class="text-xs text-content-light/70 mt-1">
                                {{ __('Ingresa uno de tus códigos de recuperación guardados') }}
                            </p>
                        </div>

                        <!-- Botones de alternancia -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4">
                            <button type="button"
                                class="text-sm text-primary hover:text-accent underline cursor-pointer flex items-center"
                                x-show="! recovery"
                                x-on:click="
                                    recovery = true;
                                    $nextTick(() => { $refs.recovery_code.focus() })
                                ">
                                <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                {{ __('Usar código de recuperación') }}
                            </button>

                            <button type="button"
                                class="text-sm text-primary hover:text-accent underline cursor-pointer flex items-center"
                                x-cloak x-show="recovery"
                                x-on:click="
                                    recovery = false;
                                    $nextTick(() => { $refs.code.focus() })
                                ">
                                <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                {{ __('Usar código de autenticación') }}
                            </button>
                        </div>

                        <!-- Botón de envío -->
                        <div class="flex justify-center mt-6">
                            <x-button.primary class="w-full sm:w-auto">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                {{ __('Verificar') }}
                            </x-button.primary>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
