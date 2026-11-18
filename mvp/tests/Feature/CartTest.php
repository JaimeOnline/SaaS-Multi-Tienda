<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_product_from_published_store_can_be_added_to_cart(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto disponible',
            'slug' => 'producto-disponible',
            'price' => 10,
            'is_available' => true,
        ]);

        $response = $this->post(route('cart.add', $product), [
            'quantity' => 2,
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('cart.items.' . $product->id . '.quantity', 2)
            ->assertSessionHas('cart.store_id', $store->id);
    }

    public function test_unavailable_product_cannot_be_added_to_cart(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto no disponible',
            'slug' => 'producto-no-disponible',
            'price' => 10,
            'is_available' => false,
        ]);

        $response = $this->post(route('cart.add', $product), [
            'quantity' => 1,
        ]);

        $response
            ->assertNotFound()
            ->assertSessionMissing('cart');
    }

    public function test_adding_the_same_product_accumulates_quantity(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto disponible',
            'slug' => 'producto-disponible',
            'price' => 10,
            'is_available' => true,
        ]);

        $this->post(route('cart.add', $product), ['quantity' => 2])
            ->assertRedirect();

        $this->post(route('cart.add', $product), ['quantity' => 3])
            ->assertRedirect()
            ->assertSessionHas('cart.items.' . $product->id . '.quantity', 5);
    }

    public function test_cart_displays_items_and_calculates_total(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto disponible',
            'slug' => 'producto-disponible',
            'price' => 12.50,
            'is_available' => true,
        ]);

        $this->withSession([
            'cart' => [
                'store_id' => $store->id,
                'items' => [
                    $product->id => [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => '12.50',
                        'quantity' => 2,
                    ],
                ],
            ],
        ])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Producto disponible')
            ->assertSee('25.00');
    }

    public function test_empty_cart_uses_the_last_visited_published_store(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda recordada',
            'slug' => 'tienda-recordada',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $response = $this->withSession([
            'last_store_id' => $store->id,
        ])->get(route('cart.index'));

        $response
            ->assertOk()
            ->assertSee('Volver a la tienda')
            ->assertSee(route('store.public', $store->slug));
    }

    public function test_cart_quantity_can_be_updated(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto disponible',
            'slug' => 'producto-disponible',
            'price' => 10,
            'is_available' => true,
        ]);

        $this->withSession([
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
        ])
            ->patch(route('cart.update', $product), ['quantity' => 5])
            ->assertRedirect()
            ->assertSessionHas('cart.items.' . $product->id . '.quantity', 5);
    }

    public function test_product_can_be_removed_from_cart(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto disponible',
            'slug' => 'producto-disponible',
            'price' => 10,
            'is_available' => true,
        ]);

        $this->withSession([
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
        ])
            ->delete(route('cart.remove', $product))
            ->assertRedirect()
            ->assertSessionMissing('cart.items.' . $product->id);
    }

    public function test_cart_rejects_invalid_quantity(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'name' => 'Producto disponible',
            'slug' => 'producto-disponible',
            'price' => 10,
            'is_available' => true,
        ]);

        $response = $this->post(route('cart.add', $product), [
            'quantity' => 0,
        ]);

        $response
            ->assertSessionHasErrors('quantity')
            ->assertRedirect();
    }

    public function test_cart_cannot_mix_products_from_different_stores(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $firstStore = Store::create([
            'user_id' => $firstUser->id,
            'name' => 'Primera tienda',
            'slug' => 'primera-tienda',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $secondStore = Store::create([
            'user_id' => $secondUser->id,
            'name' => 'Segunda tienda',
            'slug' => 'segunda-tienda',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $firstProduct = Product::create([
            'store_id' => $firstStore->id,
            'name' => 'Primer producto',
            'slug' => 'primer-producto',
            'price' => 10,
            'is_available' => true,
        ]);

        $secondProduct = Product::create([
            'store_id' => $secondStore->id,
            'name' => 'Segundo producto',
            'slug' => 'segundo-producto',
            'price' => 20,
            'is_available' => true,
        ]);

        $this->post(route('cart.add', $firstProduct), ['quantity' => 1])
            ->assertRedirect();

        $this->post(route('cart.add', $secondProduct), ['quantity' => 1])
            ->assertStatus(422)
            ->assertSessionHas('cart.store_id', $firstStore->id)
            ->assertSessionMissing('cart.items.' . $secondProduct->id);
    }
}
