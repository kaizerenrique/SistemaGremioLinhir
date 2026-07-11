<div>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        @if($post)
            <article class="bg-base-300 rounded-xl p-6 md:p-10 border border-base-300 shadow-lg">
                <!-- Título -->
                <h1 class="text-3xl md:text-5xl font-bold text-content-accent mb-4">
                    {{ $post['title'] }}
                </h1>

                <!-- Metadatos -->
                <div class="flex flex-wrap items-center text-content-light/60 text-sm mb-8 gap-4">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ \Carbon\Carbon::parse($post['published'])->format('d/m/Y H:i') }}
                    </span>
                    @if($post['labels'] ?? [])
                        <span class="flex gap-2">
                            @foreach($post['labels'] as $label)
                                <span class="bg-primary/20 text-primary px-2 py-0.5 rounded-full text-xs">{{ $label }}</span>
                            @endforeach
                        </span>
                    @endif
                    @if(isset($post['author']['displayName']))
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $post['author']['displayName'] }}
                        </span>
                    @endif
                </div>

                <!-- Imagen destacada (si existe) -->
                @if(isset($post['images'][0]['url']))
                    <div class="mb-8 rounded-lg overflow-hidden">
                        <img src="{{ $post['images'][0]['url'] }}" 
                             alt="{{ $post['title'] }}" 
                             class="w-full h-auto max-h-[500px] object-cover">
                    </div>
                @endif

                <!-- Contenido -->
                <div class="prose prose-invert max-w-none">
                    {!! $post['content'] !!}
                </div>

                <!-- Volver -->
                <div class="mt-10 pt-6 border-t border-base-300">
                    <a href="{{ route('blog.index') }}" class="text-primary hover:text-accent transition flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver al blog
                    </a>
                </div>
            </article>
        @else
            <div class="text-center text-content-light/70 py-16">
                <p class="text-xl">Post no encontrado</p>
                <a href="{{ route('blog.index') }}" class="text-primary hover:underline mt-4 inline-block">
                    Volver al blog
                </a>
            </div>
        @endif
    </div>
</div>
