@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'text-sm text-error mt-1']) }}>{{ $message }}</p>
@enderror
