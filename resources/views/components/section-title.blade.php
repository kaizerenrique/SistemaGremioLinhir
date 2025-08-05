<div class="md:col-span-1 flex justify-between items-start">
    <div class="px-4 sm:px-0">
        <h3 class="text-xl font-semibold text-content-light tracking-tight">{{ $title }}</h3>

        @if(isset($description))
            <p class="mt-2 text-content-light text-sm max-w-prose">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(isset($aside))
        <div class="px-4 sm:px-0 flex-shrink-0">
            {{ $aside }}
        </div>
    @endif
</div>
