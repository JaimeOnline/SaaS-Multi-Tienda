<?php
$f = 'c:/Users/Jaime/Documents/Mis Documentos/Programacion/SaaS Multi Tienda/mvp/resources/views/layouts/navigation.blade.php';
$c = file_get_contents($f);

// Replace all local state references with global store
$replacements = [
    ':class="{ \'rotate-180\': collapsed }"' => ':class="{ \'rotate-180\': $store.sidebar.collapsed }"',
    'x-show="!collapsed"' => 'x-show="!$store.sidebar.collapsed"',
    'x-show="collapsed && !isMobile"' => 'x-show="$store.sidebar.collapsed && !$store.sidebar.isMobile"',
    'x-show="collapsed && !$store.sidebar.isMobile"' => 'x-show="$store.sidebar.collapsed && !$store.sidebar.isMobile"',
    '@click="mobileOpen = false"' => '@click="$store.sidebar.closeMobile()"',
    '@click="bottomNavOpen = true"' => '@click="$store.sidebar.openBottomNav()"',
    '@click="bottomNavOpen = false"' => '@click="$store.sidebar.closeBottomNav()"',
    'x-show="mobileOpen"' => 'x-show="$store.sidebar.mobileOpen"',
    'x-show="bottomNavOpen"' => 'x-show="$store.sidebar.bottomNavOpen"',
    'x-show="showBottomNav"' => 'x-show="$store.sidebar.showBottomNav"',
    ':aria-expanded="bottomNavOpen"' => ':aria-expanded="$store.sidebar.bottomNavOpen"',
];

foreach ($replacements as $search => $replace) {
    $c = str_replace($search, $replace, $c);
}

file_put_contents($f, $c);
echo "Replacements done\n";