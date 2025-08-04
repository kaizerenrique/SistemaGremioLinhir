<footer class="bg-base-100 border-t border-primary/20">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Logo y Nombre -->
            <div class="flex flex-col items-center md:items-start space-y-4">
                <div class="flex items-center gap-3 hover:scale-110 transition-transform">
                    <img src="{{ asset('imagenes/escudo_512x512.png') }}" alt="Linhir Logo" class="h-12 w-12">
                    <span class="text-2xl font-bold text-primary">LINHIR</span>
                </div>
                <p class="text-content-light text-sm text-center md:text-left">
                    Gremio de Farmers & Crafters<br>
                    Fort Sterling - Albion Online
                </p>
            </div>
            <!-- Redes Sociales -->
            <div class="flex space-x-6">
                <a href="https://discord.gg/tu-enlace" target="_blank" rel="noopener"
                    class="text-content-light hover:text-[#5865F2] transition-colors">
                    <i class="fab fa-discord text-3xl"></i>
                </a>
                <a href="https://youtube.com/tu-canal" target="_blank" rel="noopener"
                    class="text-content-light hover:text-[#FF0000] transition-colors">
                    <i class="fab fa-youtube text-3xl"></i>
                </a>
            </div>
        </div>

        <!-- Derechos -->
        <div class="mt-8 pt-6 border-t border-primary/10">
            <p class="text-center text-content-light text-sm">
                &copy; {{ now()->year }} Linhir. Todos los derechos reservados.<br>
            </p>
        </div>
    </div>
</footer>
