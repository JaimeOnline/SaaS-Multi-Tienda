<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_subscription_cannot_access_product_management(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda suspendida productos',
            'slug' => 'tienda-suspendida-productos',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Inicial productos',
            'slug' => 'inicial-productos',
            'monthly_price' => 0,
            'max_products' => 2,
            'max_categories' => 2,
            'max_images_per_product' => 1,
            'is_active' => true,
        ]);

        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'suspended',
            'started_at' => now()->subDays(30),
            'status_reason' => 'Periodo de gracia finalizado; suscripción suspendida.',
        ]);

        $response = $this->actingAs($user)->get('/products');

        $response->assertForbidden();
    }

    public function test_suspended_subscription_can_access_dashboard_to_reactivate(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda suspendida',
            'slug' => 'tienda-suspendida-acceso',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Inicial',
            'slug' => 'inicial-acceso',
            'monthly_price' => 0,
            'max_products' => 2,
            'max_categories' => 2,
            'max_images_per_product' => 1,
            'is_active' => true,
        ]);

        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'suspended',
            'started_at' => now()->subDays(30),
            'status_reason' => 'Periodo de gracia finalizado; suscripción suspendida.',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Suspendida');
        $response->assertSee('Periodo de gracia finalizado');
    }
}
