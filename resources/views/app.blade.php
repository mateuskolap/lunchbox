<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
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
            background-color: hsl(30 40% 98%);
        }

        html.dark {
            background-color: hsl(20 15% 8%);
        }
    </style>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/icon-192x192.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Meta tags PWA -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Ale Marmitas">
    <meta name="theme-color" content="#BD5A27">

    <link rel="manifest" href="/build/manifest.webmanifest">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    <x-inertia::head>
        <title>{{ config('app.name', 'Laravel') }}</title>
    </x-inertia::head>
</head>
<body class="font-sans antialiased">
<x-inertia::app />
</body>
</html>
