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

<body class="font-sans antialiased text-gray-900" x-data="{}">
    <!-- Skip Link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:start-4 z-50 px-4 py-2 bg-brand-600 text-white rounded-xl shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
        Saltar al contenido principal
    </a>

    <div class="min-h-screen bg-slate-50 flex">
        <!-- Sidebar is included in navigation.blade.php -->
        @include('layouts.navigation')

        <!-- Main content wrapper -->
        <div class="flex-1 min-w-0 lg:ml-72" :class="{ 'lg:ml-18': $store.sidebar.collapsed }">
            <!-- Toast Container -->
            @include('components.ui.toast-container')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-slate-200 bg-white shadow-sm sticky top-0 z-20">
                    <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-4 lg:px-8">
                        <div class="flex items-center justify-between gap-4">
                            <!-- Mobile hamburger button -->
                            <button
                                x-show="$store.sidebar.isMobile"
                                @click="window.sidebar.openMobile()"
                                type="button"
                                class="lg:hidden inline-flex items-center justify-center rounded-lg p-2 text-surface-500 hover:text-surface-700 hover:bg-surface-100 transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-brand-500"
                                aria-label="Abrir menú"
                                aria-expanded="false"
                                aria-controls="sidebar"
                            >
                                <x-lucide-icon name="menu" class="w-6 h-6" aria-hidden="true" />
                            </button>

                            <div class="flex-1 min-w-0">
                                {{ $header }}
                            </div>
                        </div>
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="main-content" class="focus:outline-none">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
