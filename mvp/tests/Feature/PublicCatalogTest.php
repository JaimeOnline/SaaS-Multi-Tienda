<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_filters_available_products_by_category(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda con categorías',
            'slug' => 'tienda-con-categorias',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $desserts = Category::create([
            'store_id' => $store->id,
            'name' => 'Postres',
            'slug' => 'postres',
        ]);

        $drinks = Category::create([
            'store_id' => $store->id,
            'name' => 'Bebidas',
            'slug' => 'bebidas',
        ]);

        Product::create([
            'store_id' => $store->id,
            'category_id' => $desserts->id,
            'name' => 'Torta de chocolate',
            'slug' => 'torta-de-chocolate',
            'price' => 15,
            'is_available' => true,
        ]);

        Product::create([
            'store_id' => $store->id,
            'category_id' => $drinks->id,
            'name' => 'Jugo natural',
            'slug' => 'jugo-natural',
            'price' => 5,
            'is_available' => true,
        ]);

        $response = $this->get(route('store.public', [
            'slug' => $store->slug,
            'category_id' => $desserts->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('Torta de chocolate')
            ->assertDontSee('Jugo natural');
    }

    public function test_public_catalog_does_not_display_an_unpublished_store(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda privada',
            'slug' => 'tienda-privada',
            'whatsapp' => '0000000000',
            'is_published' => false,
        ]);

        $response = $this->get(route('store.public', $store->slug));

        $response->assertNotFound();
    }

    public function test_public_catalog_displays_only_available_products_from_a_published_store(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda pública',
            'slug' => 'tienda-publica',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        Product::create([
            'store_id' => $store->id,
            'name' => 'Producto disponible',
            'slug' => 'producto-disponible',
            'price' => 10,
            'is_available' => true,
        ]);

        Product::create([
            'store_id' => $store->id,
            'name' => 'Producto no disponible',
            'slug' => 'producto-no-disponible',
            'price' => 20,
            'is_available' => false,
        ]);

        $response = $this->get(route('store.public', $store->slug));

        $response
            ->assertOk()
            ->assertSee('Tienda pública')
            ->assertSee('Producto disponible')
            ->assertSee('Agregar al carrito')
            ->assertDontSee('Producto no disponible');
    }
}
