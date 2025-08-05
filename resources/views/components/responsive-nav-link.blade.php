@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-medium text-content-light bg-primary/20 focus:outline-none focus:text-content-light focus:bg-primary/30 focus:border-hover-primary transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-content-light/70 hover:text-content-light hover:bg-base-300 hover:border-primary focus:outline-none focus:text-content-light focus:bg-base-300 focus:border-primary transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
