<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireSubscriptionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_expires_trials_starts_grace_periods_and_suspends_finished_grace_periods(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda del comando',
            'slug' => 'tienda-del-comando',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Emprendedor',
            'slug' => 'emprendedor-comando',
            'monthly_price' => 4.99,
            'max_products' => 20,
            'max_categories' => 20,
            'max_images_per_product' => 10,
            'is_active' => true,
        ]);

        Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'started_at' => now()->subDays(15),
            'expires_at' => now()->subDay(),
        ]);

        $expiredSubscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subDays(30),
            'expires_at' => now()->subDays(8),
        ]);

        $graceSubscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'grace_period',
            'started_at' => now()->subDays(40),
            'expires_at' => now()->subDays(20),
            'grace_period_started_at' => now()->subDays(8),
            'grace_period_ends_at' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:expire')
            ->expectsOutput('Suscripciones expiradas: 1')
            ->assertExitCode(0);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $expiredSubscription->id,
            'status' => 'grace_period',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $graceSubscription->id,
            'status' => 'suspended',
        ]);
    }
}
