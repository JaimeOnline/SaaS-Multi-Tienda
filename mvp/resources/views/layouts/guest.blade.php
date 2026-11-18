<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <!-- Skip Link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:start-4 z-50 px-4 py-2 bg-brand-600 text-white rounded-xl shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        Saltar al contenido principal
    </a>

    <div class="flex min-h-screen flex-col items-center bg-slate-50 px-4 py-8 sm:px-6">
        <!-- Toast Container -->
        @include('components.ui.toast-container')

        <header class="w-full max-w-md mb-6 text-center">
            <a href="/" aria-label="Ir al inicio">
                <x-application-logo class="h-20 w-20 fill-current text-brand-600 mx-auto" />
            </a>
        </header>

        <main id="main-content" class="w-full rounded-2xl border border-slate-200 bg-white px-5 py-6 shadow-sm sm:px-8 {{ request()->routeIs('store.public', 'cart.index') ? 'max-w-7xl' : 'max-w-md' }}" role="main">
            {{ $slot }}
        </main>
    </div>
</body>

</html>
