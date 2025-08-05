@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-content-light mb-1 transition-colors']) }}>
    {{ $value ?? $slot }}
</label>
