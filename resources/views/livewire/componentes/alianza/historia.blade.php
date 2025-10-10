<div class="min-h-screen bg-base-100 py-16 px-4 sm:px-6 lg:px-8" 
     x-data="{
         activeEvent: 0,
         eventos: @entangle('eventos'),
         currentPair: 0,
         totalPairs: Math.ceil(@json(count($eventos)) / 2),
         
         setActiveEvent(index) {
             if (index >= 0 && index < this.eventos.length) {
                 this.activeEvent = index;
                 this.currentPair = Math.floor(index / 2);
             }
         },
         
         nextPair() {
             if (this.currentPair < this.totalPairs - 1) {
                 this.currentPair++;
                 this.scrollToPair(this.currentPair);
             }
         },
         
         prevPair() {
             if (this.currentPair > 0) {
                 this.currentPair--;
                 this.scrollToPair(this.currentPair);
             }
         },
         
         scrollToPair(pairIndex) {
             const element = this.$el.querySelector(`[data-pair-index='${pairIndex}']`);
             if (element) {
                 element.scrollIntoView({ behavior: 'smooth', block: 'center' });
             }
         },
         
         init() {
             this.$nextTick(() => {
                 this.eventos.forEach((_, index) => {
                     const element = this.$el.querySelector(`[data-event-index='${index}']`);
                     if (element) {
                         const observer = new IntersectionObserver((entries) => {
                             entries.forEach(entry => {
                                 if (entry.isIntersecting) {
                                     this.setActiveEvent(index);
                                 }
                             });
                         }, { threshold: 0.3 });
                         observer.observe(element);
                     }
                 });
             });
         }
     }"
     x-init="init()">

    <!-- Encabezado -->
    <div class="text-center mb-16 animate-fade-in">
        <h1 class="text-4xl md:text-5xl font-bold text-content-light mb-4">
            Nuestra <span class="text-accent">Historia</span>
        </h1>
        <p class="text-xl text-content-light/80 max-w-3xl mx-auto">
            Un viaje a través del tiempo que muestra los hitos más importantes 
            en la evolución de nuestra alianza.
        </p>
    </div>

    <!-- Navegación Principal -->
    <div class="max-w-4xl mx-auto mb-12">
        <!-- Barra de progreso -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-content-light/70">Progreso</span>
                <span class="text-sm font-bold text-accent" 
                      x-text="`Par ${currentPair + 1} de ${totalPairs}`"></span>
            </div>
            <div class="w-full bg-base-300 rounded-full h-2">
                <div class="bg-accent h-2 rounded-full transition-all duration-500" 
                     :style="`width: ${((currentPair + 1) / totalPairs * 100)}%`"></div>
            </div>
        </div>

        <!-- Controles de navegación -->
        <div class="flex justify-between items-center">
            <button @click="prevPair()" 
                    :disabled="currentPair === 0"
                    class="flex items-center px-4 py-2 rounded-lg transition-all"
                    :class="currentPair === 0 ? 
                           'bg-base-300 text-neutro cursor-not-allowed' : 
                           'bg-primary text-white hover:bg-hover-primary'">
                ← Anterior
            </button>
            
            <!-- Navegación rápida por pares -->
            <div class="flex gap-2">
                <template x-for="pair in totalPairs" :key="pair">
                    <button @click="currentPair = pair - 1; scrollToPair(pair - 1);"
                            class="w-8 h-8 rounded-full text-sm font-bold transition-all"
                            :class="currentPair === pair - 1 ? 
                                   'bg-accent text-base-100 scale-110' : 
                                   'bg-base-300 text-content-light hover:bg-base-200'">
                        <span x-text="pair"></span>
                    </button>
                </template>
            </div>
            
            <button @click="nextPair()" 
                    :disabled="currentPair === totalPairs - 1"
                    class="flex items-center px-4 py-2 rounded-lg transition-all"
                    :class="currentPair === totalPairs - 1 ? 
                           'bg-base-300 text-neutro cursor-not-allowed' : 
                           'bg-primary text-white hover:bg-hover-primary'">
                Siguiente →
            </button>
        </div>
    </div>

    <!-- Pares de Eventos -->
    <div class="max-w-6xl mx-auto space-y-12">
        <template x-for="(pair, pairIndex) in Array.from({length: totalPairs}, (_, i) => i)" :key="pairIndex">
            <div class="relative" :data-pair-index="pairIndex">
                <!-- Línea divisoria entre pares -->
                <div class="absolute left-1/2 transform -translate-x-1/2 w-0.5 bg-secondary/20 h-full -z-10"></div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Primer evento del par -->
                    <div class="relative" 
                         :data-event-index="pairIndex * 2"
                         x-show="eventos[pairIndex * 2]">
                        <div class="bg-base-200 rounded-2xl p-6 shadow-xl border border-base-300 hover:border-accent/30 transition-all duration-500 group h-full">
                            <!-- Indicador de posición -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-accent border-4 border-base-100 flex items-center justify-center mr-3">
                                        <span class="text-base-100 text-sm font-bold" x-text="(pairIndex * 2) + 1"></span>
                                    </div>
                                    <span class="text-2xl font-bold text-accent" x-text="eventos[pairIndex * 2].año"></span>
                                </div>
                                <span class="text-3xl" x-text="eventos[pairIndex * 2].icono"></span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-content-light group-hover:text-accent transition-colors mb-3"
                                x-text="eventos[pairIndex * 2].titulo"></h3>
                            
                            <p class="text-content-light/80 leading-relaxed mb-4"
                               x-text="eventos[pairIndex * 2].descripcion"></p>
                            
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-base-300/50">
                                <span class="text-sm text-neutro">Evento histórico</span>
                                <span class="text-sm font-bold text-accent bg-base-300 px-2 py-1 rounded">
                                    #<span x-text="(pairIndex * 2) + 1"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Segundo evento del par -->
                    <div class="relative"
                         :data-event-index="(pairIndex * 2) + 1"
                         x-show="eventos[(pairIndex * 2) + 1]">
                        <div class="bg-base-200 rounded-2xl p-6 shadow-xl border border-base-300 hover:border-accent/30 transition-all duration-500 group h-full">
                            <!-- Indicador de posición -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-accent border-4 border-base-100 flex items-center justify-center mr-3">
                                        <span class="text-base-100 text-sm font-bold" x-text="(pairIndex * 2) + 2"></span>
                                    </div>
                                    <span class="text-2xl font-bold text-accent" x-text="eventos[(pairIndex * 2) + 1].año"></span>
                                </div>
                                <span class="text-3xl" x-text="eventos[(pairIndex * 2) + 1].icono"></span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-content-light group-hover:text-accent transition-colors mb-3"
                                x-text="eventos[(pairIndex * 2) + 1].titulo"></h3>
                            
                            <p class="text-content-light/80 leading-relaxed mb-4"
                               x-text="eventos[(pairIndex * 2) + 1].descripcion"></p>
                            
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-base-300/50">
                                <span class="text-sm text-neutro">Evento histórico</span>
                                <span class="text-sm font-bold text-accent bg-base-300 px-2 py-1 rounded">
                                    #<span x-text="(pairIndex * 2) + 2"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Navegación Móvil -->
    <div class="lg:hidden mt-12 px-4">
        <div class="flex justify-center flex-wrap gap-2 max-w-2xl mx-auto">
            <template x-for="(evento, index) in eventos" :key="index">
                <button class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                        :class="activeEvent === index ? 
                               'bg-accent text-base-100 scale-110' : 
                               'bg-base-300 text-content-light hover:bg-base-200'"
                        @click="const element = $el.closest('[data-event-index]'); 
                                if(element) element.scrollIntoView({ behavior: 'smooth', block: 'center' })">
                    <span x-text="index + 1"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Instrucciones -->
    <div class="text-center mt-12">
        <p class="text-content-light/60 text-sm">
            Explora nuestra historia • 
            <span class="hidden lg:inline">Navega por pares de eventos usando los controles</span>
            <span class="lg:hidden">Toca los números para navegar</span>
        </p>
    </div>
</div>


