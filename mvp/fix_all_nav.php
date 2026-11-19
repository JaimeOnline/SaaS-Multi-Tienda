<?php
$f = 'c:/Users/Jaime/Documents/Mis Documentos/Programacion/SaaS Multi Tienda/mvp/resources/views/layouts/navigation.blade.php';
$c = file_get_contents($f);

// Pattern 1: Sidebar main nav (mobile/tablet) - complex pattern
$c = preg_replace(
    '/:href="\$store\.sidebar\.isMobile \? \'#\' : route\(\$item\[\'route\'\]\)"\s+@click="\$store\.sidebar\.isMobile \? \(\$store\.sidebar\.closeMobile\(\), setTimeout\(\(\) => window\.location\.href = \'\{\{ route\(\$item\[\'route\'\]\) \}\}\', 200\)\) : \'\'"/',
    'href="{{ route($item[\'route\']) }}" @click="$store.sidebar.isMobile && $store.sidebar.navigate(\'{{ route($item[\'route\']) }}\')"',
    $c
);

// Pattern 2: Bottom nav - :href="'#'" @click="(closeMobile, setTimeout...)"
$c = preg_replace(
    '/:href="\'#\'"\s+@click="\(\$store\.sidebar\.closeMobile\(\), setTimeout\(\(\) => window\.location\.href = \'\{\{ route\(\$item\[\'route\'\]\) \}\}\'\}, 200\)\)"/',
    'href="{{ route($item[\'route\']) }}" @click="$store.sidebar.isMobile && $store.sidebar.navigate(\'{{ route($item[\'route\']) }}\')"',
    $c
);

// Pattern 3: "Más opciones" - moreItems - :href="'#'" @click="(closeBottomNav, closeMobile, setTimeout...)"
$c = preg_replace(
    '/:href="\'#\'"\s+@click="\(\$store\.sidebar\.closeBottomNav\(\), \$store\.sidebar\.closeMobile\(\), setTimeout\(\(\) => window\.location\.href = \'\{\{ route\(\$item\[\'route\'\]\) \}\}\'\}, 200\)\)"/,
    'href="{{ route($item[\'route\']) }}" @click="$store.sidebar.isMobile && $store.sidebar.navigate(\'{{ route($item[\'route\']) }}\')"',
    $c
);

// Pattern 4: Settings in "Más opciones" - same as above
$c = preg_replace(
    '/:href="\'#\'"\s+@click="\(\$store\.sidebar\.closeBottomNav\(\), \$store\.sidebar\.closeMobile\(\), setTimeout\(\(\) => window\.location\.href = \'\{\{ route\(\$item\[\'route\'\]\) \}\}\'\}, 200\)\)"\s+role="menuitem"\s+:aria-current="\{\{ \$isActive \? \'page\' : \'false\' \}\}"\s+class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast\s+{{ \$isActive \? \'bg-brand-50 text-brand-700\' : \'text-surface-600 hover:bg-surface-100 hover:text-surface-900\' }}"/',
    'href="{{ route($item[\'route\']) }}" @click="$store.sidebar.isMobile && $store.sidebar.navigate(\'{{ route($item[\'route\']) }}\')" role="menuitem" :aria-current="{{ $isActive ? \'page\' : \'false\' }}" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast {{ $isActive ? \'bg-brand-50 text-brand-700\' : \'text-surface-600 hover:bg-surface-100 hover:text-surface-900\' }}"',
    $c
);

file_put_contents($f, $c);
echo "Done\n";