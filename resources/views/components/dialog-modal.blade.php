@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4 bg-base-300">
        <div class="text-lg font-medium text-content-accent">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-content-light">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end px-6 py-4 bg-base-300 text-end border-t border-base-200">
        {{ $footer }}
    </div>
</x-modal>
