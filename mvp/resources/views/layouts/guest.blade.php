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
    <div class="flex min-h-screen flex-col items-center bg-slate-50 px-4 py-8 sm:px-6">
        <div>
            <a href="/" aria-label="Ir al inicio">
                <x-application-logo class="h-20 w-20 fill-current text-indigo-500" />
            </a>
        </div>

        <div
            class="mt-6 w-full rounded-2xl border border-slate-100 bg-white px-5 py-6 shadow-sm sm:px-8 {{ request()->routeIs('store.public', 'cart.index') ? 'max-w-7xl' : 'max-w-md' }}">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
