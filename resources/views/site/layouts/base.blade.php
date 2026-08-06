<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('title', 'PlantDB')</title>
        @vite(['resources/css/site.css', 'resources/js/site.js'])
    </head>
    <body class="bg-white text-slate-900 antialiased">
        <main class="mx-auto max-w-3xl px-6 py-16">
            @yield('content')
        </main>
    </body>
</html>
