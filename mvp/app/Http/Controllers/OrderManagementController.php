<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        $orders = $store->orders()
            ->with('items')
            ->latest()
            ->get();

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
            ->with('status', 'order-updated');
    }
}
