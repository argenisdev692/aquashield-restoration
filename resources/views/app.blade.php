<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts — Inter primary, JetBrains Mono for code -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&family=jetbrains-mono:400,500,700"
        rel="stylesheet" />

    <!--
        Theme init — runs BEFORE first paint to avoid flash.
        Default: dark (AquaShield branding).
        Persisted in localStorage under key 'aq-theme'.
    -->
    <script>
        (function () {
            var saved = localStorage.getItem('aq-theme');
            var theme = saved === 'light' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Scripts -->
    @viteReactRefresh
    @vite(['resources/js/app.tsx'])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
    @include('cookie-consent::index')
</body>

</html>