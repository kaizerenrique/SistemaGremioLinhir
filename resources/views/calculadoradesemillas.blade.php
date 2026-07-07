<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!--  Meta Descripción -->
        <meta name="description" content="Linhir es un gremio activo de Albion Online en el servidor West, ubicado en Fort Sterling. Únete a nuestra comunidad de crafters, farmers y PvP. Ranking semanal y estadísticas en tiempo real.">  
        <meta name="keywords" content="Albion Online, Gremio Linhir, Fort Sterling, Gremio Albion, Reclutamiento Albion, Crafters, Farmers">

        <!-- 👇 ROBOTS (Permitir indexación completa) -->
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- 👇 OPEN GRAPH (Para mejorar el aspecto al compartir en Discord/Facebook) -->
        <meta property="og:title" content="Linhir - Gremio de Albion Online">
        <meta property="og:description" content="Únete al gremio Linhir en Fort Sterling. Participa en rankings semanales, actividades PvE/PvP y construye tu leyenda.">
        <meta property="og:image" content="{{ asset('imagenes/escudo_512x512.png') }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Linhir">

        <!-- 👇 TWITTER CARDS -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Linhir - Gremio de Albion Online">
        <meta name="twitter:description" content="Únete a Linhir, el gremio de crafters y farmers de Fort Sterling. Rankings semanales y comunidad activa.">
        <meta name="twitter:image" content="{{ asset('imagenes/escudo_512x512.png') }}">

        <title>Linhir | Gremio de Albion Online en Fort Sterling</title>
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/imagenes/favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])


        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="bg-base-100 min-h-screen">
        <!-- Nav -->
        @livewire('componentes.navegacion')

        <main class="pt-16">
            

            @livewire('modulo.calculadoras.calculadoradecultivos')
            

            <!-- Footer -->
            @livewire('componentes.footer')
            <!-- / Footer -->

        </main>

        @livewireScripts
    </body>
</html>