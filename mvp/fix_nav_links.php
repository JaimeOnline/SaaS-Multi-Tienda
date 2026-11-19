<?php
$f = 'c:/Users/Jaime/Documents/Mis Documentos/Programacion/SaaS Multi Tienda/mvp/resources/views/layouts/navigation.blade.php';
$c = file_get_contents($f);

// Fix "Más opciones" drawer - moreItems
$old1 = 'href="{{ route($item[\'route\']) }}"
                @click="$store.sidebar.closeBottomNav()"
                @click="$store.sidebar.closeMobile()"
                role="menuitem"
                :aria-current="{{ $isActive ? \'page\' : \'false\' }}"
                class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast
                    {{ $isActive ? \'bg-brand-50 text-brand-700\' : \'text-surface-600 hover:bg-surface-100 hover:text-surface-900\' }}"
                    {{ $comingSoon ? \'opacity-50 cursor-not-allowed\' : \'\' }}';

$new1 = ':href="\'#\'" 
                @click="($store.sidebar.closeBottomNav(), $store.sidebar.closeMobile(), setTimeout(() => window.location.href = \'{{ route($item[\'route\']) }}\', 200))"
                role="menuitem"
                :aria-current="{{ $isActive ? \'page\' : \'false\' }}"
                class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast
                    {{ $isActive ? \'bg-brand-50 text-brand-700\' : \'text-surface-600 hover:bg-surface-100 hover:text-surface-900\' }}"';

// Fix Settings in "Más opciones" drawer
$old2 = 'href="{{ route($item[\'route\']) }}"
                @click="$store.sidebar.closeBottomNav()"
                @click="$store.sidebar.closeMobile()"
                role="menuitem"
                :aria-current="{{ $isActive ? \'page\' : \'false\' }}"
                class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast
                    {{ $isActive ? \'bg-brand-50 text-brand-700\' : \'text-surface-600 hover:bg-surface-100 hover:text-surface-900\' }}"
                    {{ $comingSoon ? \'opacity-50 cursor-not-allowed\' : \'\' }}';

$new2 = ':href="\'#\'" 
                @click="($store.sidebar.closeBottomNav(), $store.sidebar.closeMobile(), setTimeout(() => window.location.href = \'{{ route($item[\'route\']) }}\', 200))"
                role="menuitem"
                :aria-current="{{ $isActive ? \'page\' : \'false\' }}"
                class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast
                    {{ $isActive ? \'bg-brand-50 text-brand-700\' : \'text-surface-600 hover:bg-surface-100 hover:text-surface-900\' }}"';

$c = str_replace($old1, $new1, $c);
$c = str_replace($old2, $new2, $c);

file_put_contents($f, $c);
echo "Done\n";