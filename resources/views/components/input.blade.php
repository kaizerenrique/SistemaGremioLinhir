@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full border rounded-md shadow-sm bg-base-200 text-content-light placeholder:text-content-light/60 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/30 disabled:bg-base-300 disabled:opacity-70 transition-colors px-3 py-2']) !!}>