<?php
$f = 'c:/Users/Jaime/Documents/Mis Documentos/Programacion/SaaS Multi Tienda/mvp/resources/views/layouts/navigation.blade.php';
$c = file_get_contents($f);

// Fix "Más opciones" drawer - moreItems
$c = preg_replace(
    '/href="\{\{ route\(\$item\[\'route\'\]\) \}\}"\s+@click="\$store\.sidebar\.closeBottomNav\(\)"\s+@click="\$store\.sidebar\.closeMobile\(\)"/',
    ':href="\'#\'" @click="($store.sidebar.closeBottomNav(), $store.sidebar.closeMobile(), setTimeout(() => window.location.href = \'{{ route($item[\'route\']) }}\', 200))"',
    $c
);

// Also fix settings
$c = preg_replace(
    '/href="\{\{ route\(\$item\[\'route\'\]\) \}\}"\s+@click="\$store\.sidebar\.closeBottomNav\(\)"\s+@click="\$store\.sidebar\.closeMobile\(\)"\s+role="menuitem"\s+:aria-current="\{\{ \$isActive \? \'page\' : \'false\' \}\}"\s+class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast\s+{{ \$isActive \? \'bg-brand-50 text-brand-700\' : \'text-surface-600 hover:bg-surface-100 hover:text-surface-900\' }}\s+{{ \$comingSoon \? \'opacity-50 cursor-not-allowed\' : \'\' }}/',
    ':href="\'#\'" @click="($store.sidebar.closeBottomNav(), $store.sidebar.closeMobile(), setTimeout(() => window.location.href = \'{{ route($item[\'route\']) }}\', 200))" role="menuitem" :aria-current="{{ $isActive ? \'page\' : \'false\' }}" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast {{ $isActive ? \'bg-brand-50 text-brand-700\' : \'text-surface-600 hover:bg-surface-100 hover:text-surface-900\' }}',
    $c
);

file_put_contents($f, $c);
echo "Done\n";