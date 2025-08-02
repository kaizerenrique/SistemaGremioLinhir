<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Linhir</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])


        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="bg-dark min-h-screen">
        <!-- Nav -->
        @livewire('componentes.navegacion')

        <main class="pt-16">
            <!-- Hero -->
            @livewire('componentes.hero')
            <!-- / Hero -->

            <!-- Team -->
            @livewire('componentes.team')
            <!-- / Team -->

            <!-- CTA -->
            @livewire('componentes.cta')
            <!-- / CTA -->

            <!-- Nosotros -->
            @livewire('componentes.nosotros')
            <!-- / Nosotros -->

            <!-- Reclutamiento -->
            @livewire('componentes.reclutamiento')
            <!-- / Reclutamiento -->

            <!-- Footer -->
            @livewire('componentes.footer')
            <!-- / Footer -->


        </main>

        @livewireScripts
    </body>
</html>
