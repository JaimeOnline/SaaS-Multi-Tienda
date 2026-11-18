<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GracePeriodDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_grace_period_shows_badge_and_dates(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda gracia prueba',
            'slug' => 'tienda-gracia-prueba',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $plan = Plan::create([
            'name' => 'Plan Emprendedor',
            'slug' => 'emprendedor',
            'monthly_price' => 4.99,
            'max_products' => 20,
            'max_categories' => 20,
            'max_images_per_product' => 5,
            'is_active' => true,
        ]);

        $subscription = $store->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'grace_period',
            'started_at' => now(),
            'expires_at' => now()->addDays(14),
            'grace_period_started_at' => now(),
            'grace_period_ends_at' => now()->addDays(7),
            'status_reason' => 'Prueba gratuita expirada; periodo de gracia activo.',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Periodo de gracia activo');
        $response->assertSee('Vence prueba:');
        $response->assertSee('Gracia hasta:');
    }

    public function test_suspended_shows_badge_and_reason(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda suspendida test',
            'slug' => 'tienda-suspendida-test',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $plan = Plan::create([
            'name' => 'Plan Inicial',
            'slug' => 'inicial',
            'monthly_price' => 0,
            'max_products' => 2,
            'max_categories' => 2,
            'max_images_per_product' => 1,
            'is_active' => true,
        ]);

        $subscription = $store->subscriptions()->create([
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

    public function test_expired_shows_badge_and_date(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda expirada test',
            'slug' => 'tienda-expirada-test',
            'whatsapp' => '0000000000',
            'is_published' => true,
        ]);

        $plan = Plan::create([
            'name' => 'Plan Inicial',
            'slug' => 'inicial',
            'monthly_price' => 0,
            'max_products' => 2,
            'max_categories' => 2,
            'max_images_per_product' => 1,
            'is_active' => true,
        ]);

        $subscription = $store->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subDays(15),
            'expires_at' => now()->subDay(),
            'status_reason' => 'La prueba gratuita expiró.',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Prueba expirada');
        // El template muestra la fecha en formato d/m/Y H:i
        $response->assertSee(now()->subDay()->format('d/m/Y'));
    }
}
