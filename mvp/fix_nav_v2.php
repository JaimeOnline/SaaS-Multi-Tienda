<?php
$f = 'c:/Users/Jaime/Documents/Mis Documentos/Programacion/SaaS Multi Tienda/mvp/resources/views/layouts/navigation.blade.php';
$c = file_get_contents($f);

// Use preg_replace with proper escaping
$patterns = [
    // Pattern 1: Sidebar main nav
    '/:href="\$store\.sidebar\.isMobile \? \'#\' : route\(\$item\[\'route\'\]\)"\s+@click="\$store\.sidebar\.isMobile \? \(\$store\.sidebar\.closeMobile\(\), setTimeout\(\(\) => window\.location\.href = \'\{\{ route\(\$item\[\'route\'\]\) \}\}\', 200\)\) : \'\'"/',
    
    // Pattern 2: Bottom nav
    '/:href="\'#\'"\s+@click="\(\$store\.sidebar\.closeMobile\(\), setTimeout\(\(\) => window\.location\.href = \'\{\{ route\(\$item\[\'route\'\]\) \}\}\', 200\)\)"/',
    
    // Pattern 3: "Más opciones" - closeBottomNav + closeMobile
    '/:href="\'#\'"\s+@click="\(\$store\.sidebar\.closeBottomNav\(\), \$store\.sidebar\.closeMobile\(\), setTimeout\(\(\) => window\.location\.href = \'\{\{ route\(\$item\[\'route\'\]\) \}\}\', 200\)\)"/,
];

$replacement = 'href="{{ route($item[\'route\']) }}" @click="$store.sidebar.isMobile && $store.sidebar.navigate(\'{{ route($item[\'route\']) }}\')"';

foreach ($patterns as $pattern) {
    $c = preg_replace($pattern, $replacement, $c);
}

file_put_contents($f, $c);
echo "Done\n";