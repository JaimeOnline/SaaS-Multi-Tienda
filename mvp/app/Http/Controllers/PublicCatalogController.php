<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $store = Store::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with('categories')
            ->firstOrFail();

        $store->load([
            'products' => fn($query) => $query
                ->where('is_available', true)
                ->when(
                    $request->filled('category_id'),
                    fn($query) => $query->where('category_id', $request->integer('category_id'))
                )
                ->with([
                    'category',
                    'images' => fn($query) => $query->orderBy('sort_order'),
                ])
                ->orderBy('name'),
        ]);

        $request->session()->put('last_store_id', $store->id);

        $cart = $request->session()->get('cart', [
            'store_id' => null,
            'items' => [],
        ]);

        $cartItems = $cart['store_id'] === $store->id
            ? $cart['items']
            : [];

        return view('stores.public', compact('store', 'cartItems'));
    }
}
