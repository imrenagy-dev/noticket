<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class([
    'dark'             => in_array($appearance ?? 'system', ['dark', 'brown', 'blue', 'green-dark', 'green-dim', 'dark-dim', 'brown-dim', 'blue-dim']),
    'theme-brown'      => ($appearance ?? 'system') === 'brown',
    'theme-blue'       => ($appearance ?? 'system') === 'blue',
    'theme-azure'      => ($appearance ?? 'system') === 'azure',
    'theme-green-dark' => ($appearance ?? 'system') === 'green-dark',
    'theme-green-light'=> ($appearance ?? 'system') === 'green-light',
    'theme-green-dim'  => ($appearance ?? 'system') === 'green-dim',
    'theme-dark-dim'   => ($appearance ?? 'system') === 'dark-dim',
    'theme-brown-dim'  => ($appearance ?? 'system') === 'brown-dim',
    'theme-blue-dim'   => ($appearance ?? 'system') === 'blue-dim',
])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        @php
            $fav = match($appearance ?? 'system') {
                'light'       => ['ico'=>'/favicons/light.ico',       'p32'=>'/favicons/light-32.png',       'svg'=>'/favicons/light.svg',       'apple'=>'/favicons/light-apple.png'],
                'brown'       => ['ico'=>'/favicons/brown.ico',       'p32'=>'/favicons/brown-32.png',       'svg'=>'/favicons/brown.svg',       'apple'=>'/favicons/brown-apple.png'],
                'blue'        => ['ico'=>'/favicons/blue.ico',        'p32'=>'/favicons/blue-32.png',        'svg'=>'/favicons/blue.svg',        'apple'=>'/favicons/blue-apple.png'],
                'azure'       => ['ico'=>'/favicons/azure.ico',       'p32'=>'/favicons/azure-32.png',       'svg'=>'/favicons/azure.svg',       'apple'=>'/favicons/azure-apple.png'],
                'green-dark'  => ['ico'=>'/favicons/green-dark.ico',  'p32'=>'/favicons/green-dark-32.png',  'svg'=>'/favicons/green-dark.svg',  'apple'=>'/favicons/green-dark-apple.png'],
                'green-light' => ['ico'=>'/favicons/green-light.ico', 'p32'=>'/favicons/green-light-32.png', 'svg'=>'/favicons/green-light.svg', 'apple'=>'/favicons/green-light-apple.png'],
                'green-dim'   => ['ico'=>'/favicons/green-dim.ico',   'p32'=>'/favicons/green-dim-32.png',   'svg'=>'/favicons/green-dim.svg',   'apple'=>'/favicons/green-dim-apple.png'],
                'dark-dim'    => ['ico'=>'/favicons/dark-dim.ico',    'p32'=>'/favicons/dark-dim-32.png',    'svg'=>'/favicons/dark-dim.svg',    'apple'=>'/favicons/dark-dim-apple.png'],
                'brown-dim'   => ['ico'=>'/favicons/brown-dim.ico',   'p32'=>'/favicons/brown-dim-32.png',   'svg'=>'/favicons/brown-dim.svg',   'apple'=>'/favicons/brown-dim-apple.png'],
                'blue-dim'    => ['ico'=>'/favicons/blue-dim.ico',    'p32'=>'/favicons/blue-dim-32.png',    'svg'=>'/favicons/blue-dim.svg',    'apple'=>'/favicons/blue-dim-apple.png'],
                default       => ['ico'=>'/favicon.ico',              'p32'=>'/favicon-32x32.png',           'svg'=>'/favicon.svg',              'apple'=>'/apple-touch-icon.png'],
            };
        @endphp
        <link data-fav="ico"   rel="icon"             href="{{ $fav['ico'] }}"   sizes="16x16 32x32 48x48">
        <link data-fav="p32"   rel="icon"             href="{{ $fav['p32'] }}"   type="image/png" sizes="32x32">
        <link data-fav="svg"   rel="icon"             href="{{ $fav['svg'] }}"   type="image/svg+xml">
        <link data-fav="apple" rel="apple-touch-icon" href="{{ $fav['apple'] }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
