<aside x-cloak x-data="{
    init() {
        this.$watch('$store.sidebar.collapsed', (value) => {
            this.$dispatch('sidebar:collapsed-changed', { detail: value });
        });
    }
}" x-init="init()"
    x-show="!$store.sidebar.isMobile || $store.sidebar.mobileOpen"
    :class="[
        'fixed inset-y-0 start-0 z-40 bg-white border-r border-surface-200 transition-all duration-normal ease-in-out',
        $store.sidebar.collapsed ? 'w-18' : 'w-72',
        $store.sidebar.isMobile && !$store.sidebar.mobileOpen ? '-translate-x-full' : 'translate-x-0',
    ]"
    :aria-hidden="$store.sidebar.isMobile && !$store.sidebar.mobileOpen" role="navigation"
    aria-label="Navegación principal" class="lg:flex lg:flex-col">

    <!-- Sidebar Header / Logo -->
    <div class="flex h-16 items-center justify-between px-4 border-b border-surface-200 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('dashboard') }}" aria-label="{{ config('app.name', 'TuBase Express') }} - Inicio"
                class="flex-shrink-0">
                <x-application-logo class="h-9 w-auto fill-current text-brand-600" aria-hidden="true" />
            </a>

            <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition-opacity duration-fast ease-out"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-fast ease-in" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="overflow-hidden whitespace-nowrap text-lg font-semibold text-surface-900">
                TuBase Express
            </span>
        </div>

        <button x-show="!$store.sidebar.isMobile" @click="$store.sidebar.toggleCollapsed()" type="button"
            :aria-label="$store.sidebar.collapsed ? 'Expandir sidebar' : 'Colapsar sidebar'"
            :aria-expanded="!$store.sidebar.collapsed"
            class="flex-shrink-0 inline-flex items-center justify-center rounded-lg p-1.5 text-surface-400 hover:text-surface-600 hover:bg-surface-100 transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-brand-500"
            aria-hidden="true">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path :class="{ 'rotate-180': $store.sidebar.collapsed }" class="transition-transform duration-normal"
                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6" />
            </svg>
        </button>
    </div>

    <!-- Navegación Principal -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" role="menubar">
        @php
            $mainNav = config('navigation.main', []);
        @endphp

        @foreach ($mainNav as $item)
            @php
                $isActive = request()->routeIs($item['route'] . '*') || request()->routeIs($item['route']);
                $hasBadge = isset($item['badge']) && $item['badge'];
                $comingSoon = isset($item['coming_soon']) && $item['coming_soon'];
            @endphp

            @if ($hasBadge)
                @php
                    $badgeCount = 0;
                    $storeId = auth()->user()?->store_id;
                    if ($storeId) {
                        if ($item['badge'] === 'pending_orders_count') {
                            $badgeCount = \App\Models\Order::where('store_id', $storeId)
                                ->where('status', 'pending')
                                ->count();
                        } elseif ($item['badge'] === 'pending_products_count') {
                            $badgeCount = \App\Models\Product::where('store_id', $storeId)
                                ->where('is_available', false)
                                ->count();
                        }
                    }
                @endphp
            @endif

            <a href="{{ route($item['route']) }}" role="menuitem" :aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-fast
                    {{ $isActive ? 'bg-brand-50 text-brand-700' : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900' }}
                    {{ $comingSoon ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ $comingSoon ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                <span class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10" aria-hidden="true">
                    <x-lucide-icon name="{{ $item['icon'] }}" class="w-5 h-5" :class="$isActive ? 'text-brand-600' : 'text-surface-400 group-hover:text-surface-600'" />
                </span>

                <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition-opacity duration-fast ease-out"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-fast ease-in" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="truncate">
                    {{ $item['name'] }}
                </span>

                @if ($hasBadge && $badgeCount > 0)
                    <span x-show="!$store.sidebar.collapsed"
                        x-transition:enter="transition-opacity duration-fast ease-out"
                        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition-opacity duration-fast ease-in"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
                        class="ml-auto inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold bg-brand-100 text-brand-700"
                        aria-label="{{ $badgeCount }} {{ $item['name'] }} pendientes">
                        {{ $badgeCount }}
                    </span>
                @elseif ($hasBadge && $badgeCount > 0)
                    <span class="absolute -top-1 -end-1 w-4 h-4 flex items-center justify-center"
                        aria-label="{{ $badgeCount }} {{ $item['name'] }} pendientes">
                        <span class="w-2 h-2 rounded-full bg-brand-600" aria-hidden="true"></span>
                    </span>
                @endif

                <div x-show="$store.sidebar.collapsed && !$store.sidebar.isMobile"
                    x-transition:enter="transition-opacity duration-fast ease-out"
                    x-transition:enter-start="opacity-0 transform translate-x-1"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    x-transition:leave="transition-opacity duration-fast ease-in"
                    x-transition:leave-start="opacity-100 transform translate-x-0"
                    x-transition:leave-end="opacity-0 transform translate-x-1"
                    class="absolute left-full top-1/2 -translate-y-1/2 ml-3 w-48 rounded-xl bg-surface-900 px-3 py-2 text-sm font-medium text-white shadow-xl opacity-0 pointer-events-none z-50"
                    role="tooltip">
                    {{ $item['name'] }}
                    @if ($comingSoon)
                        <span
                            class="ml-2 inline-flex items-center rounded-full bg-surface-700 px-1.5 py-0.5 text-xs text-surface-300">Próximamente</span>
                    @endif
                </div>
            </a>
        @endforeach
    </nav>

    <!-- Divisor -->
    <div class="border-t border-surface-200 mx-3" aria-hidden="true"></div>

    <!-- Sección Configuración -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" role="menubar" aria-label="Configuración">
        @php
            $settingsNav = config('navigation.settings', []);
        @endphp

        @foreach ($settingsNav as $item)
            @php
                $isActive = request()->routeIs($item['route'] . '*') || request()->routeIs($item['route']);
                $comingSoon = isset($item['coming_soon']) && $item['coming_soon'];
            @endphp

            <a href="{{ route($item['route']) }}" role="menuitem" :aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-fast
                    {{ $isActive ? 'bg-brand-50 text-brand-700' : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900' }}
                    {{ $comingSoon ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ $comingSoon ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                <span class="flex-shrink-0 inline-flex items-center justify-center w-10 h-10" aria-hidden="true">
                    <x-lucide-icon name="{{ $item['icon'] }}" class="w-5 h-5" :class="$isActive ? 'text-brand-600' : 'text-surface-400 group-hover:text-surface-600'" />
                </span>

                <span x-show="!$store.sidebar.collapsed" x-transition:enter="transition-opacity duration-fast ease-out"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-fast ease-in" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="truncate">
                    {{ $item['name'] }}
                </span>

                @if ($comingSoon)
                    <span x-show="!$store.sidebar.collapsed"
                        class="ml-auto inline-flex items-center rounded-full bg-surface-100 px-2 py-0.5 text-xs text-surface-500">
                        Próximamente
                    </span>
                @endif

                <div x-show="$store.sidebar.collapsed && !$store.sidebar.isMobile"
                    x-transition:enter="transition-opacity duration-fast ease-out"
                    x-transition:enter-start="opacity-0 transform translate-x-1"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    x-transition:leave="transition-opacity duration-fast ease-in"
                    x-transition:leave-start="opacity-100 transform translate-x-0"
                    x-transition:leave-end="opacity-0 transform translate-x-1"
                    class="absolute left-full top-1/2 -translate-y-1/2 ml-3 w-48 rounded-xl bg-surface-900 px-3 py-2 text-sm font-medium text-white shadow-xl opacity-0 pointer-events-none z-50"
                    role="tooltip">
                    {{ $item['name'] }}
                    @if ($comingSoon)
                        <span
                            class="ml-2 inline-flex items-center rounded-full bg-surface-700 px-1.5 py-0.5 text-xs text-surface-300">Próximamente</span>
                    @endif
                </div>
            </a>
        @endforeach
    </nav>

    <!-- Footer: Usuario actual (solo cuando expandido) -->
    <div x-show="!$store.sidebar.collapsed" x-transition:enter="transition-opacity duration-fast ease-out"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-fast ease-in" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="shrink-0 border-t border-surface-200 p-3">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center"
                aria-hidden="true">
                <x-lucide-icon name="user" class="w-5 h-5 text-brand-600" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-surface-900 truncate">{{ Auth::user()?->name ?? '' }}</p>
                <p class="text-xs text-surface-500 truncate">{{ Auth::user()?->email ?? '' }}</p>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Drawer Overlay -->
<div x-cloak x-show="$store.sidebar.mobileOpen" @click.outside="$store.sidebar.closeMobile()"
    x-transition:enter="transition-opacity duration-normal ease-out" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="" class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
    aria-hidden="true" tabindex="-1"></div>

<!-- Mobile Bottom Navigation -->
<nav x-cloak x-show="$store.sidebar.showBottomNav" x-transition:enter="transition-transform duration-normal ease-out"
    x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
    x-transition:leave="transition-transform duration-normal ease-in" x-transition:leave-start="translate-y-0"
    x-transition:leave-end="translate-y-full"
    class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-surface-200 shadow-xl lg:hidden"
    role="navigation" aria-label="Navegación principal móvil">
    <div class="grid grid-cols-5 gap-1 px-2 py-2">
        @php
            $mainNav = config('navigation.main', []);
            $bottomNavItems = array_slice($mainNav, 0, 4);
            $moreItems = array_slice($mainNav, 4);
        @endphp

        @foreach ($bottomNavItems as $item)
            @php
                $isActive = request()->routeIs($item['route'] . '*') || request()->routeIs($item['route']);
            @endphp
            <a href="{{ route($item['route']) }}" role="menuitem" :aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="relative flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-xs font-medium transition-colors duration-fast
                    {{ $isActive ? 'text-brand-600' : 'text-surface-500 hover:text-surface-700 hover:bg-surface-100' }}">
                <x-lucide-icon name="{{ $item['icon'] }}" class="w-6 h-6" aria-hidden="true" />
                <span>{{ $item['name'] }}</span>
            </a>
        @endforeach

        <!-- Botón "Más" -->
        <button @click="$store.sidebar.openBottomNav()" type="button" :aria-expanded="$store.sidebar.bottomNavOpen"
            aria-controls="bottom-nav-drawer" aria-label="Más opciones"
            class="relative flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-xs font-medium text-surface-500 hover:text-surface-700 hover:bg-surface-100 transition-colors duration-fast">
            <x-lucide-icon name="more-horizontal" class="w-6 h-6" aria-hidden="true" />
            <span>Más</span>
        </button>
    </div>
</nav>
<!-- Bottom Nav "Más" Drawer -->
<div x-cloak x-show="$store.sidebar.bottomNavOpen" @click.outside="$store.sidebar.closeBottomNav()"
    x-transition:enter="transition-opacity duration-normal ease-out" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="" class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
    aria-hidden="true" tabindex="-1"></div>

<div id="bottom-nav-drawer" x-cloak x-show="$store.sidebar.bottomNavOpen"
    x-transition:enter="transition-transform duration-normal ease-out" x-transition:enter-start="translate-y-full"
    x-transition:enter-end="translate-y-0" x-transition:leave="transition-transform duration-normal ease-in"
    x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
    class="fixed bottom-0 left-0 right-0 z-40 bg-white rounded-t-2xl shadow-xl lg:hidden max-h-[70vh] overflow-hidden flex flex-col"
    role="dialog" aria-label="Más opciones de navegación" aria-modal="true">
    <div class="flex items-center justify-between px-4 py-3 border-b border-surface-200 shrink-0">
        <h2 class="text-lg font-semibold text-surface-900">Más opciones</h2>
        <button @click="$store.sidebar.closeBottomNav()" type="button"
            class="inline-flex items-center justify-center rounded-lg p-2 text-surface-400 hover:text-surface-600 hover:bg-surface-100 transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-brand-500"
            aria-label="Cerrar">
            <x-lucide-icon name="x" class="w-5 h-5" aria-hidden="true" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" role="menubar">
        @foreach ($moreItems as $item)
            @php
                $isActive = request()->routeIs($item['route'] . '*') || request()->routeIs($item['route']);
                $comingSoon = isset($item['coming_soon']) && $item['coming_soon'];
            @endphp
            <a href="{{ route($item['route']) }}" role="menuitem" :aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast
                    {{ $isActive ? 'bg-brand-50 text-brand-700' : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900' }}
                    {{ $comingSoon ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ $comingSoon ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                <x-lucide-icon name="{{ $item['icon'] }}" class="w-6 h-6 flex-shrink-0" :class="$isActive ? 'text-brand-600' : 'text-surface-400'"
                    aria-hidden="true" />
                <span class="flex-1">{{ $item['name'] }}</span>
                @if ($comingSoon)
                    <span
                        class="inline-flex items-center rounded-full bg-surface-100 px-2 py-0.5 text-xs text-surface-500">Próximamente</span>
                @endif
            </a>
        @endforeach

        <div class="border-t border-surface-200 my-3" aria-hidden="true"></div>

        @foreach (config('navigation.settings', []) as $item)
            @php
                $isActive = request()->routeIs($item['route'] . '*') || request()->routeIs($item['route']);
                $comingSoon = isset($item['coming_soon']) && $item['coming_soon'];
            @endphp
            <a href="{{ route($item['route']) }}" role="menuitem"
                :aria-current="{{ $isActive ? 'page' : 'false' }}"
                class="group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-fast
                    {{ $isActive ? 'bg-brand-50 text-brand-700' : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900' }}
                    {{ $comingSoon ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ $comingSoon ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                <x-lucide-icon name="{{ $item['icon'] }}" class="w-6 h-6 flex-shrink-0" :class="$isActive ? 'text-brand-600' : 'text-surface-400'"
                    aria-hidden="true" />
                <span class="flex-1">{{ $item['name'] }}</span>
                @if ($comingSoon)
                    <span
                        class="inline-flex items-center rounded-full bg-surface-100 px-2 py-0.5 text-xs text-surface-500">Próximamente</span>
                @endif
            </a>
        @endforeach
    </nav>
</div>
