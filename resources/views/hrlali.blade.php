<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!--  Meta Palabras Clave -->
        <meta name="keywords" content="Somos un Gremio especializado en la recolección de recursos, su procesamiento y posterior fabricación de items.">
        <meta name="article:tag" content="Gremio Linhir">

        <!--  Meta Descripción -->
        <meta name="description" content="Web Oficial del Gremio Linhir">  

        <title>Linhir</title>
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
            <!-- Hero -->
            @livewire('componentes.alianza.hero')
            <!-- / Hero -->

            <!-- Hero -->
            @livewire('componentes.alianza.historia')
            <!-- / Hero -->



            

            <!-- Footer -->
            @livewire('componentes.footer')
            <!-- / Footer -->

        </main>

        @livewireScripts
    </body>
</html>
