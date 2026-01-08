<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FF5F55">
    <meta name="description" content="A fun music quiz application">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Music Quiz">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Music Quiz') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    <!-- Theme initialization script to prevent FOUC -->
    <script>
        (function() {
            try {
                const savedAppearance = localStorage.getItem('appearance') || 'system';
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                const shouldApplyDark = savedAppearance === 'dark' ||
                    (savedAppearance === 'system' && prefersDark);

                if (shouldApplyDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.style.colorScheme = 'dark';
                } else {
                    document.documentElement.style.colorScheme = 'light';
                }
            } catch (e) {
                // Fallback if localStorage is not available
                document.documentElement.style.colorScheme = 'light';
            }
        })();
    </script>

    <!-- Load React app assets -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/main.tsx'])
</head>

<body class="font-sans antialiased">
    <div id="app"></div>
</body>

</html>
