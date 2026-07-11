<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Linhir') }}</title>
    <meta name="description" content="{{ $description ?? 'Blog de Linhir - Noticias, guías y novedades del gremio' }}">
    <meta property="og:title" content="{{ $ogTitle ?? $title ?? config('app.name') }}">
    <meta property="og:description" content="{{ $ogDescription ?? $description ?? 'Blog de Linhir' }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('imagenes/escudo_512x512.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/imagenes/favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-base-100 min-h-screen">
    <!-- Menú público -->
    @livewire('componentes.navegacion')

    <main class="pt-16">
        {{ $slot }}
    </main>

    <!-- Footer público -->
    @livewire('componentes.footer')

    @livewireScripts
</body>
</html>