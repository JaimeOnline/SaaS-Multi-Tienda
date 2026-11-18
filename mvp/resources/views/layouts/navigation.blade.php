<nav x-data="{ open: false }" class="border-b border-surface-200 bg-white shadow-sm" role="navigation" aria-label="Navegación principal">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" aria-label="{{ config('app.name', 'TuBase Express') }} - Inicio">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" aria-hidden="true" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex" role="menubar">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" role="menuitem">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('store.settings.edit')" :active="request()->routeIs('store.settings.*')" role="menuitem">
                        {{ __('Tienda') }}
                    </x-nav-link>

                    <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" role="menuitem">
                        {{ __('Categorías') }}
                    </x-nav-link>

                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" role="menuitem">
                        {{ __('Productos') }}
                    </x-nav-link>

                    <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')" role="menuitem">
                        {{ __('Pedidos') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" type="button" aria-label="Alternar menú de navegación" :aria-expanded="open" aria-controls="mobile-menu"
                    class="inline-flex items-center justify-center rounded-xl p-2.5 text-surface-500 transition duration-fast ease-in-out hover:bg-surface-100 hover:text-surface-700 focus:bg-surface-100 focus:text-surface-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden border-t border-surface-200 bg-surface-50 sm:hidden" id="mobile-menu" role="navigation" aria-label="Menú móvil">
        <div class="space-y-1 px-4 pb-3 pt-3" role="menubar">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" role="menuitem">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('store.settings.edit')" :active="request()->routeIs('store.settings.*')" role="menuitem">
                {{ __('Tienda') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" role="menuitem">
                {{ __('Categorías') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" role="menuitem">
                {{ __('Productos') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')" role="menuitem">
                {{ __('Pedidos') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-surface-200">
            <div class="px-4">
                <div class="font-medium text-base text-surface-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-surface-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1" role="menubar">
                <x-responsive-nav-link :href="route('profile.edit')" role="menuitem">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        role="menuitem">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
