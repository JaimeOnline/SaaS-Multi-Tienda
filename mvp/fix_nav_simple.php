<?php
$f = 'c:/Users/Jaime/Documents/Mis Documentos/Programacion/SaaS Multi Tienda/mvp/resources/views/layouts/navigation.blade.php';
$c = file_get_contents($f);

// Simple string replacements
$c = str_replace(
    ':href="$store.sidebar.isMobile ? \'#\' : route($item[\'route\'])" @click="$store.sidebar.isMobile ? ($store.sidebar.closeMobile(), setTimeout(() => window.location.href = \'{{ route($item[\'route\']) }}\', 200)) : \'\'"',
    'href="{{ route($item[\'route\']) }}" @click="$store.sidebar.isMobile && $store.sidebar.navigate(\'{{ route($item[\'route\']) }}\')"',
    $c
);

$c = str_replace(
    ':href="\'#\'" @click="($store.sidebar.closeMobile(), setTimeout(() => window.location.href = \'{{ route($item[\'route\']) }}\', 200))"',
    'href="{{ route($item[\'route\']) }}" @click="$store.sidebar.isMobile && $store.sidebar.navigate(\'{{ route($item[\'route\']) }}\')"',
    $c
);

$c = str_replace(
    ':href="\'#\'" @click="($store.sidebar.closeBottomNav(), $store.sidebar.closeMobile(), setTimeout(() => window.location.href = \'{{ route($item[\'route\']) }}\', 200))"',
    'href="{{ route($item[\'route\']) }}" @click="$store.sidebar.isMobile && $store.sidebar.navigate(\'{{ route($item[\'route\']) }}\')"',
    $c
);

file_put_contents($f, $c);
echo "Done\n";