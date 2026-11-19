<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$f = 'c:/Users/Jaime/Documents/Mis Documentos/Programacion/SaaS Multi Tienda/mvp/resources/views/layouts/navigation.blade.php';
file_put_contents($f, '');
$c = '<aside x-data="{ init() { this.$watch(\'$store.sidebar.collapsed\', (value) => { this.$nextTick(() => { this.$dispatch(\'sidebar:collapsed-changed\', { detail: value }); }); }); } }" x-init="init()" :class="[\'fixed inset-y-0 start-0 z-40 bg-white border-r border-surface-200 transition-all duration-normal ease-in-out\', $store.sidebar.collapsed ? \'w-18\' : \'w-72\', $store.sidebar.isMobile ? \'-translate-x-full\' : \'translate-x-0\', $store.sidebar.mobileOpen ? \'translate-x-0\' : \'\']" :aria-hidden="$store.sidebar.isMobile && !$store.sidebar.mobileOpen" role="navigation" aria-label="Navegación principal" class="hidden lg:flex lg:flex-col">
    <div class="flex h-16 items-center justify-between px-4 border-b border-surface-200 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route(\'dashboard\') }}" aria-label="{{ config(\'app.name\', \'TuBase Express\') }} - Inicio" class="flex-shrink-0"><x-application-logo class="h-9 w-auto fill-current text-brand-600" aria-hidden="true" /></a>
            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition-opacity duration-fast ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-fast ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="overflow-hidden whitespace-nowrap text-lg font-semibold text-surface-900">TuBase Express</span>
        </div>
        <button x-show="!$store.sidebar.isMobile" @click="$store.sidebar.toggleCollapsed()" type="button" :aria-label="$store.sidebar.collapsed ? \'Expandir sidebar\' : \'Colapsar sidebar\'" :aria-expanded="!$store.sidebar.collapsed" class="flex-shrink-0 inline-flex items-center justify-center rounded-lg p-1.5 text-surface-400 hover:text-surface-600 hover:bg-surface-100 transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-brand-500" aria-hidden="true"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path :class="{ \'rotate-180\': $store.sidebar.collapsed }" class="transition-transform duration-normal" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6" /></svg></button>
    </div>';
file_put_contents($f, $c);
echo "Part 1 done\n";
echo "File size: " . filesize($f) . "\n";