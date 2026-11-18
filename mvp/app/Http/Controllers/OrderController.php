<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = $request->session()->get('cart');

        abort_unless(
            is_array($cart)
                && ! empty($cart['store_id'])
                && ! empty($cart['items']),
            422,
            'El carrito está vacío.'
        );

        $products = Product::query()
            ->where('store_id', $cart['store_id'])
            ->where('is_available', true)
            ->whereHas('store', fn($query) => $query->where('is_published', true))
            ->whereIn('id', array_keys($cart['items']))
            ->get()
            ->keyBy('id');

        abort_unless(
            $products->count() === count($cart['items']),
            422,
            'Uno o más productos ya no están disponibles.'
        );

        $order = DB::transaction(function () use ($cart, $products, $validated): Order {
            $items = collect($cart['items'])->map(function (array $item) use ($products): array {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->price;

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $unitPrice * $quantity,
                ];
            });

            $order = Order::create([
                'store_id' => $cart['store_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_notes' => $validated['customer_notes'] ?? null,
                'status' => 'pending',
                'total' => $items->sum('subtotal'),
            ]);

            $order->items()->createMany($items->all());

            return $order;
        });

        $order->load(['store', 'items']);

        $message = "*¡Hola! Quisiera realizar el siguiente pedido en {$order->store->name}:*\n\n";

        foreach ($order->items as $item) {
            $message .= sprintf(
                "• %dx %s - %s %.2f\n",
                $item->quantity,
                $item->product_name,
                $order->store->currency ?? '$',
                (float) $item->subtotal
            );
        }

        $message .= sprintf(
            "\n*Total a pagar:* %s %.2f",
            $order->store->currency ?? '$',
            (float) $order->total
        );

        $message .= "\n\n*Cliente:* {$order->customer_name}";
        $message .= "\n*Teléfono:* {$order->customer_phone}";

        if ($order->customer_notes) {
            $message .= "\n*Notas:* {$order->customer_notes}";
        }

        $whatsappUrl = 'https://wa.me/' . $order->store->whatsapp
            . '?text=' . urlencode($message);

        $request->session()->forget('cart');

        return redirect()
            ->route('cart.index')
            ->with('order_id', $order->id)
            ->with('whatsapp_url', $whatsappUrl);
    }
}
