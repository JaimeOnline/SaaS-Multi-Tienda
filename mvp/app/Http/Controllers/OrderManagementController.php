<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        $query = $store->orders()->with('items');

        // Global search
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->string('search');
            $q->where(function ($subQ) use ($search) {
                $subQ->where('id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'ilike', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        });

        // Column filters
        $query->when($request->filled('filter_status'), function ($q) use ($request) {
            $q->where('status', $request->string('filter_status'));
        });

        // Sorting
        $sortBy = $request->string('sort_by')->toString();
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc']) ? $request->string('sort_dir')->toString() : 'asc';
        $allowedSorts = ['id', 'customer_name', 'created_at', 'total', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $orders = $query->paginate(25)->withQueryString();

        // Handle AJAX request for datatable
        if ($request->expectsJson()) {
            $data = $orders->getCollection()->map(function ($order) {
                $statusVariants = [
                    'pending' => 'warning',
                    'confirmed' => 'info',
                    'preparing' => 'default',
                    'completed' => 'success',
                    'cancelled' => 'error',
                ];

                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'created_at' => $order->created_at,
                    'status' => $order->status,
                    'status_variant' => $statusVariants[$order->status] ?? 'default',
                    'total' => $order->total,
                    'actions' => [
                        [
                            'label' => 'Ver',
                            'icon' => 'eye',
                            'variant' => 'ghost',
                            'href' => route('orders.show', $order->id),
                        ],
                    ],
                ];
            });

            return response()->json(array_merge($orders->toArray(), [
                'data' => $data,
            ]));
        }

        return view('orders.index', compact('orders'));
    }

    public function update(Request $request, \App\Models\Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_unless($order->store_id === $request->user()->store->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,preparing,completed,cancelled'],
        ]);

        $order->update($validated);

        return redirect()
            ->route('orders.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Pedido actualizado',
                'message' => "El pedido #{$order->id} ha cambiado a «{$validated['status']}».",
            ]);
    }

    /**
     * Bulk delete orders.
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:orders,id']]);

        $store = $request->user()->store;
        $deletedCount = $store->orders()->whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => "Se eliminaron {$deletedCount} pedido" . ($deletedCount !== 1 ? 's' : '') . ' correctamente.',
        ]);
    }

    /**
     * Bulk toggle order status (cycle through statuses).
     */
    public function bulkToggleStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:orders,id']]);

        $store = $request->user()->store;
        $orders = $store->orders()->whereIn('id', $request->ids)->get();

        $statusCycle = ['pending', 'confirmed', 'preparing', 'completed', 'cancelled'];
        $updatedCount = 0;

        foreach ($orders as $order) {
            $currentIndex = array_search($order->status, $statusCycle);
            $nextIndex = ($currentIndex !== false && $currentIndex < count($statusCycle) - 1) ? $currentIndex + 1 : 0;
            $order->update(['status' => $statusCycle[$nextIndex]]);
            $updatedCount++;
        }

        return response()->json([
            'message' => "Se actualizó el estado de {$updatedCount} pedido" . ($updatedCount !== 1 ? 's' : '') . '.',
        ]);
    }
}
