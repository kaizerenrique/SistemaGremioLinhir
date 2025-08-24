<!-- Sección CTA con Cuenta Regresiva -->
<section class="relative py-16 md:py-24 overflow-hidden bg-base-100" id="aniversario">
    <!-- Fondo -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center opacity-80"
            style="background-image: url({{ asset('imagenes/CTA_Aniversario.jpeg') }})">
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-base-100 via-base-100/80 to-base-100"></div>
    </div>

    <!-- Contenido principal -->
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Badge de aniversario -->
            <div
                class="inline-flex items-center bg-primary/20 text-primary px-4 py-2 rounded-full mb-6 animate-pulse-gold">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                        clip-rule="evenodd"></path>
                </svg>
                <span class="font-semibold">3º Aniversario</span>
            </div>

            <h2 class="text-3xl md:text-5xl lg:text-6xl font-bold text-content-accent mb-6 px-2 drop-shadow-xl">
                Celebra con Nosotros el <span class="text-accent">Tercer Aniversario</span>
            </h2>

            <p class="text-content-light text-lg md:text-xl max-w-2xl mx-auto mb-10">
                Únete a la celebración de nuestros 3 años de aventuras, conquistas y camaradería. ¡Eventos especiales y
                sorpresas te esperan!
            </p>

            <!-- Cuenta regresiva -->
            <div class="mb-12">
                <h3 class="text-content-accent text-xl md:text-2xl font-bold mb-6">La celebración comienza en:</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto">
                    <!-- Días -->
                    <div class="bg-base-200/80 backdrop-blur-sm p-4 rounded-xl border-2 border-accent/30">
                        <div id="days" class="countdown-number text-accent text-3xl md:text-5xl font-bold">00</div>
                        <div class="text-content-light text-sm mt-2">Días</div>
                    </div>

                    <!-- Horas -->
                    <div class="bg-base-200/80 backdrop-blur-sm p-4 rounded-xl border-2 border-accent/30">
                        <div id="hours" class="countdown-number text-accent text-3xl md:text-5xl font-bold">00</div>
                        <div class="text-content-light text-sm mt-2">Horas</div>
                    </div>

                    <!-- Minutos -->
                    <div class="bg-base-200/80 backdrop-blur-sm p-4 rounded-xl border-2 border-accent/30">
                        <div id="minutes" class="countdown-number text-accent text-3xl md:text-5xl font-bold">00</div>
                        <div class="text-content-light text-sm mt-2">Minutos</div>
                    </div>

                    <!-- Segundos -->
                    <div class="bg-base-200/80 backdrop-blur-sm p-4 rounded-xl border-2 border-accent/30">
                        <div id="seconds" class="countdown-number text-accent text-3xl md:text-5xl font-bold">00</div>
                        <div class="text-content-light text-sm mt-2">Segundos</div>
                    </div>
                </div>
            </div>

            <!-- Información del evento 
            <div class="bg-base-200/60 backdrop-blur-sm p-6 rounded-2xl border border-base-300 mb-10 text-left">
                <h3 class="text-content-accent text-2xl font-bold mb-4">Eventos de Aniversario</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start">
                        <div class="bg-accent/20 p-2 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-content-light font-semibold">Batalla Épica</h4>
                            <p class="text-content-light text-sm">25 Sept - 20:00 UTC</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-accent/20 p-2 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-content-light font-semibold">Torneo de Recompensas</h4>
                            <p class="text-content-light text-sm">26 Sept - 18:00 UTC</p>
                        </div>
                    </div>
                </div>
            </div>
            -->
            <!-- Botón de acción -->
            <a href="#reclutamiento"
                class="inline-flex items-center bg-accent hover:bg-hover-accent text-base-100 px-8 md:px-12 py-4 md:py-5 rounded-lg text-lg md:text-xl font-bold transition-all hover:scale-105 shadow-lg mt-4 animate-pulse-gold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                ¡Únete a la Celebración!
            </a>
        </div>
    </div>
</section>

<script>
    // Fecha objetivo: 25 de septiembre de 2025 (tercer aniversario)
    const anniversaryDate = new Date('September 25, 2025 00:00:00').getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = anniversaryDate - now;

        // Cálculos de tiempo
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Mostrar resultados
        document.getElementById('days').innerText = days.toString().padStart(2, '0');
        document.getElementById('hours').innerText = hours.toString().padStart(2, '0');
        document.getElementById('minutes').innerText = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').innerText = seconds.toString().padStart(2, '0');

        // Si la cuenta regresiva termina
        if (distance < 0) {
            clearInterval(countdownTimer);
            document.getElementById('days').innerText = '00';
            document.getElementById('hours').innerText = '00';
            document.getElementById('minutes').innerText = '00';
            document.getElementById('seconds').innerText = '00';
        }
    }

    // Actualizar cada segundo
    const countdownTimer = setInterval(updateCountdown, 1000);
    updateCountdown(); // Ejecutar inmediatamente
</script>
