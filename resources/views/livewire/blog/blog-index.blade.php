<div>
    <div class="container mx-auto px-4 py-8">
        <!-- Título del blog -->
        <h1 class="text-4xl md:text-5xl font-bold text-content-accent text-center mb-2 pb-8">
            {{ $blogInfo['blog']['name'] ?? 'Blog Linhir' }}
        </h1>
        @if($blogInfo && isset($blogInfo['blog']['description']))
            <p class="text-center text-content-light/70 text-lg mb-12">
                {{ $blogInfo['blog']['description'] }}
            </p>
        @endif

        <!-- Grid de posts -->
        @if(count($posts) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                    <a href="{{ route('blog.show', $post['id']) }}" 
                       class="bg-base-300 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all hover:scale-[1.02] border border-base-300 group">
                        @if(isset($post['images'][0]['url']))
                            <img src="{{ $post['images'][0]['url'] }}" 
                                 alt="{{ $post['title'] }}" 
                                 class="w-full h-48 object-cover group-hover:opacity-90 transition">
                        @else
                            <div class="w-full h-48 bg-base-200 flex items-center justify-center text-content-light/30">
                                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="p-5">
                            <h2 class="text-xl font-semibold text-content-light group-hover:text-primary transition line-clamp-2">
                                {{ $post['title'] }}
                            </h2>
                            <p class="text-content-light/70 text-sm mt-2 line-clamp-3">
                                {{ Str::limit(strip_tags($post['content']), 150) }}
                            </p>
                            <div class="mt-4 flex justify-between items-center text-content-light/60 text-sm">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($post['published'])->format('d/m/Y') }}
                                </span>
                                @if($post['labels'] ?? [])
                                    <span class="flex gap-1">
                                        @foreach(array_slice($post['labels'], 0, 2) as $label)
                                            <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full text-xs">{{ $label }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Paginación -->
            @if($nextPageToken)
                <div class="text-center mt-10">
                    <button wire:click="nextPage" 
                            wire:loading.attr="disabled"
                            class="bg-primary hover:bg-hover-primary text-white px-8 py-3 rounded-lg font-semibold transition-all hover:scale-105 disabled:opacity-50">
                        <span wire:loading.remove>Cargar más</span>
                        <span wire:loading>
                            <svg class="inline w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Cargando...
                        </span>
                    </button>
                </div>
            @endif
        @else
            <div class="text-center text-content-light/70 py-16">
                <svg class="w-20 h-20 mx-auto text-primary/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <p class="text-xl">No hay publicaciones aún.</p>
                <p class="text-sm mt-2">Vuelve pronto para ver las novedades.</p>
            </div>
        @endif
    </div>
</div>