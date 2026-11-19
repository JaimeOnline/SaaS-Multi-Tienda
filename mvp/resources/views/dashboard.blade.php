<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-brand-600">Panel de administraci&oacute;n</p>
                <h1 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Dashboard') }}
                </h1>
            </div>
            <span class="text-sm text-surface-500">Resumen de tu tienda</span>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- 1. SUBSCRIPTION STATUS BANNER -->
            @include('dashboard.partials.subscription-banner')

            <!-- 2. METRICS CARDS (4 cards con sparklines) -->
            <section aria-labelledby="metrics-heading" class="space-y-4">
                <h2 id="metrics-heading" class="sr-only">M&eacute;tricas principales</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @include('dashboard.partials.metric-card', [
                        'label' => 'Pedidos hoy',
                        'value' => $ordersToday,
                        'sparkline' => $ordersSparkline,
                        'icon' => 'shopping-bag',
                        'iconColor' => 'brand',
                        'actionText' => 'Ver pedidos',
                        'actionUrl' => route('orders.index'),
                    ])
                    @include('dashboard.partials.metric-card', [
                        'label' => 'Ingresos este mes',
                        'value' => number_format($revenueThisMonth, 2, ',', '.') . ' ' . $store->currency,
                        'sparkline' => $revenueSparkline,
                        'icon' => 'currency-eur',
                        'iconColor' => 'success',
                        'actionText' => 'Ver reporte',
                        'actionUrl' => '#',
                        'actionDisabled' => true,
                    ])
                    @include('dashboard.partials.metric-card', [
                        'label' => 'Productos activos',
                        'value' => $activeProducts,
                        'sparkline' => null,
                        'icon' => 'package',
                        'iconColor' => 'info',
                        'actionText' => 'Nuevo producto',
                        'actionType' => 'modal',
                        'actionModal' => 'product-create',
                    ])
                    @include('dashboard.partials.metric-card', [
                        'label' => 'Tasa conversi&oacute;n',
                        'value' => '--%',
                        'sparkline' => null,
                        'icon' => 'trending-up',
                        'iconColor' => 'warning',
                        'actionText' => 'Ver tienda',
                        'actionUrl' => route('store.public', $store->slug),
                        'actionTarget' => '_blank',
                    ])
                </div>
            </section>

<!-- 3. QUICK ACTIONS GRID (6 acciones) -->
            <section aria-labelledby="actions-heading" class="space-y-4">
                <h2 id="actions-heading" class="text-lg font-semibold text-surface-900">Acciones r&aacute;pidas</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @include('dashboard.partials.quick-action', [
                        'icon' => 'plus-circle',
                        'label' => 'Nuevo producto',
                        'type' => 'modal',
                        'modal' => 'product-create',
                        'variant' => 'primary',
                    ])
                    @include('dashboard.partials.quick-action', [
                        'icon' => 'clock',
                        'label' => 'Pedidos pendientes',
                        'url' => route('orders.index', ['status' => 'pending']),
                        'badge' => $pendingOrdersCount,
                    ])
                    @include('dashboard.partials.quick-action', [
                        'icon' => 'share-2',
                        'label' => 'Compartir tienda',
                        'type' => 'modal',
                        'modal' => 'share-store',
                        'variant' => 'secondary',
                    ])
                    @include('dashboard.partials.quick-action', [
                        'icon' => 'settings',
                        'label' => 'Configurar tienda',
                        'url' => route('store.settings.edit'),
                        'variant' => 'secondary',
                    ])
                    @include('dashboard.partials.quick-action', [
                        'icon' => 'folder-tree',
                        'label' => 'Gestionar categor&iacute;as',
                        'url' => route('categories.index'),
                        'variant' => 'secondary',
                    ])
                    @include('dashboard.partials.quick-action', [
                        'icon' => 'store',
                        'label' => 'Ver cat&aacute;logo p&uacute;blico',
                        'url' => route('store.public', $store->slug),
                        'target' => '_blank',
                        'variant' => 'outline',
                    ])
                </div>
            </section>

            <!-- 4. RECENT ORDERS TABLE -->
            <section aria-labelledby="recent-orders-heading" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 id="recent-orders-heading" class="text-lg font-semibold text-surface-900">Pedidos recientes</h2>
                    <x-ui.button variant="ghost" size="sm" href="{{ route('orders.index') }}" icon="chevron-right" iconPosition="right">
                        Ver todos
                    </x-ui.button>
                </div>
                @if ($recentOrders->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" role="table">
                            <thead>
                                <tr class="border-b border-surface-200">
                                    <th scope="col" class="text-left p-3 font-medium text-surface-500">Pedido</th>
                                    <th scope="col" class="text-left p-3 font-medium text-surface-500">Cliente</th>
                                    <th scope="col" class="text-left p-3 font-medium text-surface-500">Total</th>
                                    <th scope="col" class="text-left p-3 font-medium text-surface-500">Estado</th>
                                    <th scope="col" class="text-left p-3 font-medium text-surface-500">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-100">
                                @foreach ($recentOrders as $order)
                                    <tr class="hover:bg-surface-50 transition-colors">
                                        <td class="p-3 font-mono text-surface-900">#{{ $order->id }}</td>
                                        <td class="p-3 text-surface-700">{{ $order->customer_name }}</td>
                                        <td class="p-3 font-semibold text-surface-900">{{ number_format($order->total, 2, ',', '.') }} {{ $store->currency }}</td>
                                        <td class="p-3">
                                            <x-ui.badge variant="{{ $this->getOrderStatusVariant($order->status) }}" size="sm">
                                                {{ ucfirst($order->status) }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="p-3 text-surface-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-ui.card variant="bordered" padding="lg" class="text-center py-8">
                        <x-lucide-icon name="inbox" class="mx-auto h-12 w-12 text-surface-300" />
                        <p class="mt-3 text-surface-500">No hay pedidos todav&iacute;a</p>
                        <x-ui.button variant="primary" class="mt-4" href="{{ route('store.public', $store->slug) }}" target="_blank">
                            Ver tu tienda
                        </x-ui.button>
                    </x-ui.card>
                @endif
            </section>
<!-- 5. ALERTAS INTELIGENTES -->
            @if ($unpublishedProductsCount > 0 || $lowStockProductsCount > 0)
                <section aria-labelledby="alerts-heading" class="space-y-3">
                    <h2 id="alerts-heading" class="text-lg font-semibold text-surface-900">Alertas</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @if ($unpublishedProductsCount > 0)
                            <x-ui.card variant="bordered" class="border-semantic-warning-light bg-semantic-warning-light/50" padding="md">
                                <div class="flex items-start gap-3">
                                    <x-lucide-icon name="alert-triangle" class="mt-0.5 h-5 w-5 text-semantic-warning-dark flex-shrink-0" />
                                    <div>
                                        <p class="font-medium text-semantic-warning-dark">Productos sin publicar</p>
                                        <p class="mt-1 text-sm text-semantic-warning-dark/80">{{ $unpublishedProductsCount }} producto{{ $unpublishedProductsCount > 1 ? 's' : '' }} no visible{{ $unpublishedProductsCount > 1 ? 's' : '' }} en tu cat&aacute;logo p&uacute;blico.</p>
                                        <x-ui.button variant="outline" size="sm" class="mt-3" href="{{ route('products.index') }}">
                                            Publicar ahora
                                        </x-ui.button>
                                    </div>
                                </div>
                            </x-ui.card>
                        @endif
                        @if ($lowStockProductsCount > 0)
                            <x-ui.card variant="bordered" class="border-semantic-error-light bg-semantic-error-light/50" padding="md">
                                <div class="flex items-start gap-3">
                                    <x-lucide-icon name="package-minus" class="mt-0.5 h-5 w-5 text-semantic-error-dark flex-shrink-0" />
                                    <div>
                                        <p class="font-medium text-semantic-error-dark">Stock bajo</p>
                                        <p class="mt-1 text-sm text-semantic-error-dark/80">{{ $lowStockProductsCount }} producto{{ $lowStockProductsCount > 1 ? 's' : '' }} con stock bajo.</p>
                                        <x-ui.button variant="outline" size="sm" class="mt-3" href="{{ route('products.index') }}">
                                            Revisar stock
                                        </x-ui.button>
                                    </div>
                                </div>
                            </x-ui.card>
                        @endif
                    </div>
                </section>
            @endif

            <!-- MODALES -->
            @include('dashboard.modals.product-create')
            @include('dashboard.modals.share-store')
        </div>
    </div>
</x-app-layout>