<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('title', 'PlantDB') — Die offene Pflanzendatenbank</title>
        <meta name="description" content="@yield('description', 'PlantDB ist eine offene, community-gepflegte Pflanzendatenbank für Garten-Apps.')">
        @vite(['resources/css/site.css', 'resources/js/site.js'])
    </head>
    <body>
        <x-site.header />
        <main class="mx-auto px-6 py-12" style="max-width: var(--container-max)">
            @yield('content')
        </main>
        <x-site.footer />
    </body>
</html>
