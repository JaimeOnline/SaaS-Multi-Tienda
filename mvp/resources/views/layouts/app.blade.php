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

<body class="font-sans antialiased text-gray-900">
    <!-- Skip Link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:start-4 z-50 px-4 py-2 bg-brand-600 text-white rounded-xl shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        Saltar al contenido principal
    </a>

    <div class="min-h-screen bg-slate-50">
        @include('layouts.navigation')

        <!-- Toast Container -->
        @include('components.ui.toast-container')

        <!-- Page Heading -->
        @isset($header)
            <header class="border-b border-slate-200 bg-white shadow-sm">
                <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main id="main-content" class="focus:outline-none">
            {{ $slot }}
        </main>
    </div>
</body>

</html>
