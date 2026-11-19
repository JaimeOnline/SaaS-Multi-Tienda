<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $store = request()->user()->store;

        $subscription = $store->subscriptions()
            ->with('plan')
            ->latest()
            ->first();

        // Métricas
        $ordersToday = $store->orders()->today()->count();
        $revenueThisMonth = $store->orders()->thisMonth()->sum('total');
        $activeProducts = $store->products()->active()->count();
        $pendingOrdersCount = $store->orders()->pending()->count();

        // Sparkline data (últimos 7 días)
        $ordersSparkline = $this->getOrdersSparkline($store);
        $revenueSparkline = $this->getRevenueSparkline($store);

        // Pedidos recientes (últimos 5)
        $recentOrders = $store->orders()
            ->with('items')
            ->latest()
            ->take(5)
            ->get();

        // Alertas
        $unpublishedProductsCount = $store->products()->unpublished()->count();
        $lowStockProductsCount = 0; // Future: cuando haya stock_quantity

        return view('dashboard', compact(
            'store',
            'subscription',
            'ordersToday',
            'revenueThisMonth',
            'activeProducts',
            'pendingOrdersCount',
            'ordersSparkline',
            'revenueSparkline',
            'recentOrders',
            'unpublishedProductsCount',
            'lowStockProductsCount'
        ));
    }

    private function getOrdersSparkline($store): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = $store->orders()
                ->whereBetween('created_at', [$date, $date->copy()->endOfDay()])
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    private function getRevenueSparkline($store): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $sum = $store->orders()
                ->whereBetween('created_at', [$date, $date->copy()->endOfDay()])
                ->sum('total');
            $data[] = (float) $sum;
        }
        return $data;
    }

    private function getOrderStatusVariant(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'preparing' => 'default',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'default',
        };
    }
}