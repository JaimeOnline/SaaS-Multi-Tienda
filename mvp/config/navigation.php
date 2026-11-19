<?php

return [
    'main' => [
        [
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'layout-dashboard',
            'permission' => 'view_dashboard',
        ],
        [
            'name' => 'Productos',
            'route' => 'products.index',
            'icon' => 'package',
            'permission' => 'view_products',
            'badge' => 'pending_products_count',
        ],
        [
            'name' => 'Pedidos',
            'route' => 'orders.index',
            'icon' => 'shopping-bag',
            'permission' => 'view_orders',
            'badge' => 'pending_orders_count',
        ],
        [
            'name' => 'Categorías',
            'route' => 'categories.index',
            'icon' => 'folder-tree',
            'permission' => 'view_categories',
        ],
    ],
    'settings' => [
        [
            'name' => 'Mi Tienda',
            'route' => 'store.settings.edit',
            'icon' => 'store',
        ],
        [
            'name' => 'Suscripción',
            'route' => 'subscriptions.entrepreneur-trial.start',
            'icon' => 'credit-card',
            'coming_soon' => true,
        ],
        [
            'name' => 'Equipo',
            'route' => 'profile.edit',
            'icon' => 'user-plus',
            'coming_soon' => true,
        ],
    ],
];