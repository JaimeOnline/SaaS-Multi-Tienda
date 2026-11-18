<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', [
            'store_id' => null,
            'items' => [],
        ]);

        $total = collect($cart['items'])
            ->sum(fn(array $item): float => (float) $item['price'] * $item['quantity']);

        $store = $cart['store_id']
            ? Store::whereKey($cart['store_id'])
            ->where('is_published', true)
            ->first()
            : Store::whereKey($request->session()->get('last_store_id'))
            ->where('is_published', true)
            ->first();

        return view('cart.index', compact('cart', 'store', 'total'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        abort_unless(
            $product->is_available && $product->store()->where('is_published', true)->exists(),
            404
        );

        $cart = $request->session()->get('cart', [
            'store_id' => null,
            'items' => [],
        ]);

        abort_unless(
            $cart['store_id'] === $product->store_id
                && isset($cart['items'][$product->id]),
            404
        );

        $cart['items'][$product->id]['quantity'] = $validated['quantity'];

        $request->session()->put('cart', $cart);

        return back();
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', [
            'store_id' => null,
            'items' => [],
        ]);

        abort_unless(
            $cart['store_id'] === $product->store_id
                && isset($cart['items'][$product->id]),
            404
        );

        unset($cart['items'][$product->id]);

        $request->session()->put('cart', $cart);

        return back();
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        abort_unless(
            $product->is_available && $product->store()->where('is_published', true)->exists(),
            404
        );

        $cart = $request->session()->get('cart', [
            'store_id' => null,
            'items' => [],
        ]);

        abort_if(
            $cart['store_id'] !== null && $cart['store_id'] !== $product->store_id,
            422,
            'No se pueden mezclar productos de distintas tiendas.'
        );

        $item = $cart['items'][$product->id] ?? [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (string) $product->price,
            'quantity' => 0,
        ];

        $item['quantity'] += $validated['quantity'];

        $cart['store_id'] = $product->store_id;
        $cart['items'][$product->id] = $item;

        $request->session()->put('cart', $cart);

        return back()->withFragment('product-' . $product->id);
    }
}
