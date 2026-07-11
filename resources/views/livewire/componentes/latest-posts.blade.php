<section class="py-16 bg-base-200" id="ultimas-noticias">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-content-accent text-center mb-12">
            Últimas Noticias
        </h2>

        @if(count($posts) > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                            <h3 class="text-xl font-semibold text-content-light group-hover:text-primary transition line-clamp-2">
                                {{ $post['title'] }}
                            </h3>
                            <p class="text-content-light/70 text-sm mt-2 line-clamp-3">
                                {{ Str::limit(strip_tags($post['content']), 120) }}
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
                                            <span class="bg-primary/10 px-2 py-0.5 rounded-full text-xs">{{ $label }}</span>
                                        @endforeach
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('blog.index') }}" 
                   class="inline-block bg-primary hover:bg-hover-primary text-white px-8 py-3 rounded-lg font-semibold transition-all hover:scale-105">
                    Ver todas las noticias
                </a>
            </div>
        @else
            <div class="text-center text-content-light/70 py-12">
                <svg class="w-16 h-16 mx-auto text-primary/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <p class="text-lg">Próximamente, noticias y novedades.</p>
            </div>
        @endif
    </div>
</section>
