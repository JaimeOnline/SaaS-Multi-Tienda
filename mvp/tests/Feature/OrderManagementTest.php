<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_view_only_orders_from_own_store(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Mi tienda',
            'slug' => 'mi-tienda',
            'whatsapp' => '584121234567',
            'is_published' => true,
        ]);

        $otherStore = Store::create([
            'user_id' => $otherUser->id,
            'name' => 'Otra tienda',
            'slug' => 'otra-tienda',
            'whatsapp' => '584121234568',
            'is_published' => true,
        ]);

        Order::create([
            'store_id' => $store->id,
            'customer_name' => 'Cliente propio',
            'customer_phone' => '584140000001',
            'status' => 'pending',
            'total' => 25,
        ]);

        Order::create([
            'store_id' => $otherStore->id,
            'customer_name' => 'Cliente ajeno',
            'customer_phone' => '584140000002',
            'status' => 'pending',
            'total' => 35,
        ]);

        $response = $this->actingAs($user)
            ->get(route('orders.index'));

        $response
            ->assertOk()
            ->assertSee('Cliente propio')
            ->assertDontSee('Cliente ajeno');
    }

    public function test_authenticated_admin_can_update_the_status_of_own_order(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Mi tienda',
            'slug' => 'mi-tienda',
            'whatsapp' => '584121234567',
            'is_published' => true,
        ]);

        $order = Order::create([
            'store_id' => $store->id,
            'customer_name' => 'Cliente propio',
            'customer_phone' => '584140000001',
            'status' => 'pending',
            'total' => 25,
        ]);

        $response = $this->actingAs($user)
            ->patch(route('orders.update', $order), [
                'status' => 'confirmed',
            ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('status', 'order-updated');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'store_id' => $store->id,
            'status' => 'confirmed',
        ]);
    }
}
