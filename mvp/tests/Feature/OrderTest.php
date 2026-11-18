<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_an_order_from_a_valid_cart(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '584121234567',
            'is_published' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto disponible',
            'slug' => 'producto-disponible',
            'price' => 10,
            'is_available' => true,
        ]);

        $response = $this->withSession([
            'cart' => [
                'store_id' => $store->id,
                'items' => [
                    $product->id => [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => '10.00',
                        'quantity' => 2,
                    ],
                ],
            ],
        ])->post(route('orders.store'), [
            'customer_name' => 'Carlos Mendoza',
            'customer_phone' => '584149998877',
            'customer_notes' => 'Entregar en la tarde',
        ]);

        $response
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('whatsapp_url', function (string $url) use ($store): bool {
                return str_starts_with($url, 'https://wa.me/' . $store->whatsapp . '?text=')
                    && str_contains($url, urlencode('Carlos Mendoza'))
                    && str_contains($url, urlencode('Producto disponible'));
            });

        $this->assertDatabaseHas('orders', [
            'store_id' => $store->id,
            'customer_name' => 'Carlos Mendoza',
            'customer_phone' => '584149998877',
            'customer_notes' => 'Entregar en la tarde',
            'total' => '20.00',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_name' => 'Producto disponible',
            'quantity' => 2,
            'unit_price' => '10.00',
            'subtotal' => '20.00',
        ]);
    }
}
