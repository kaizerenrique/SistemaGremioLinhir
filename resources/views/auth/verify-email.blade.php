<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-base-200 border border-base-300 rounded-xl shadow-xl overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <x-authentication-card-logo class="w-16 h-16" />
                </div>

                <!-- Mensaje principal -->
                <div class="bg-base-250/40 border border-base-300 rounded-lg p-4 mb-6 flex items-start">
                    <svg class="w-6 h-6 text-primary mr-3 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <p class="text-content-light text-sm">
                        {{ __('Antes de continuar, por favor verifica tu dirección de email haciendo clic en el enlace que te acabamos de enviar. Si no recibiste el correo, con gusto te enviaremos otro.') }}
                    </p>
                </div>

                <!-- Mensaje de éxito -->
                @if (session('status') == 'verification-link-sent')
                    <div class="bg-success/10 border border-success/30 rounded-lg p-4 mb-6 flex items-start">
                        <svg class="w-6 h-6 text-success mr-3 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-success text-sm">
                            {{ __('Se ha enviado un nuevo enlace de verificación a la dirección de email que proporcionaste en tu perfil.') }}
                        </p>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                    <!-- Botón de reenvío -->
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                        @csrf
                        <x-button class="w-full">
                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ __('Reenviar correo de verificación') }}
                        </x-button>
                    </form>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        <!-- Enlace a perfil -->
                        <a href="{{ route('profile.show') }}" class="text-sm text-primary hover:text-accent transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                            {{ __('Editar perfil') }}
                        </a>

                        <!-- Formulario de logout -->
                        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="text-sm text-content-light/80 hover:text-error transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                {{ __('Cerrar sesión') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Consejo adicional -->
                <div class="mt-8 pt-5 border-t border-base-300/50 text-center">
                    <p class="text-xs text-content-light/60">
                        {{ __('¿No ves el correo? Revisa tu carpeta de spam o') }}
                        <button 
                            x-data
                            x-on:click="$dispatch('resend-verification')"
                            class="text-primary hover:text-accent cursor-pointer font-medium"
                        >
                            {{ __('vuelve a solicitar el enlace') }}
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>