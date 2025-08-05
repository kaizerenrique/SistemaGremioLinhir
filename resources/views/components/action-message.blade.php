@props(['on'])

<div x-data="{ shown: false, timeout: null }"
    x-init="@this.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000); })"
    x-show.transition.out.opacity.duration.1500ms="shown"
    x-transition:leave.opacity.duration.1500ms
    style="display: none;"
    {{ $attributes->merge(['class' => 'fixed top-4 right-4 px-4 py-2 rounded-md bg-base-300 border border-base-300 shadow-lg text-sm text-primary font-medium']) }}>
    {{ $slot->isEmpty() ? 'Guardado.' : $slot }}
</div>
