<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;
    public function test_subscription_defines_expected_fillable_attributes(): void
    {
        $subscription = new Subscription();

        $this->assertSame([
            'store_id',
            'plan_id',
            'status',
            'started_at',
            'expires_at',
            'next_renewal_at',
            'grace_period_started_at',
            'grace_period_ends_at',
            'cancelled_at',
            'status_reason',
        ], $subscription->getFillable());
    }

    public function test_subscription_defines_expected_casts(): void
    {
        $subscription = new Subscription();

        $this->assertSame('datetime', $subscription->getCasts()['started_at']);
        $this->assertSame('datetime', $subscription->getCasts()['expires_at']);
        $this->assertSame('datetime', $subscription->getCasts()['next_renewal_at']);
        $this->assertSame('datetime', $subscription->getCasts()['grace_period_started_at']);
        $this->assertSame('datetime', $subscription->getCasts()['grace_period_ends_at']);
        $this->assertSame('datetime', $subscription->getCasts()['cancelled_at']);
    }

    public function test_expired_trials_are_marked_as_expired(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de prueba',
            'slug' => 'tienda-prueba',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Emprendedor',
            'slug' => 'emprendedor',
            'monthly_price' => 4.99,
            'max_products' => 20,
            'max_categories' => 20,
            'max_images_per_product' => 10,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'started_at' => now()->subDays(15),
            'expires_at' => now()->subDay(),
        ]);

        $updated = app(SubscriptionLifecycleService::class)->expireTrials();

        $this->assertSame(1, $updated);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'expired',
            'status_reason' => 'La prueba gratuita expiró.',
        ]);
    }

    public function test_expired_subscriptions_enter_a_seven_day_grace_period(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda de gracia',
            'slug' => 'tienda-gracia',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Emprendedor',
            'slug' => 'emprendedor-gracia',
            'monthly_price' => 4.99,
            'max_products' => 20,
            'max_categories' => 20,
            'max_images_per_product' => 10,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subDays(20),
            'expires_at' => now()->subDays(6),
        ]);

        $updated = app(SubscriptionLifecycleService::class)->startGracePeriods();

        $subscription->refresh();

        $this->assertSame(1, $updated);
        $this->assertSame('grace_period', $subscription->status);
        $this->assertNotNull($subscription->grace_period_started_at);
        $this->assertNotNull($subscription->grace_period_ends_at);
        $this->assertTrue(
            $subscription->grace_period_ends_at->equalTo(
                $subscription->grace_period_started_at->copy()->addDays(7)
            )
        );
        $this->assertSame(
            'Periodo de gracia iniciado.',
            $subscription->status_reason
        );
    }

    public function test_finished_grace_periods_are_suspended(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda suspendida',
            'slug' => 'tienda-suspendida',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Emprendedor',
            'slug' => 'emprendedor-suspendida',
            'monthly_price' => 4.99,
            'max_products' => 20,
            'max_categories' => 20,
            'max_images_per_product' => 10,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'grace_period',
            'started_at' => now()->subDays(30),
            'expires_at' => now()->subDays(14),
            'grace_period_started_at' => now()->subDays(8),
            'grace_period_ends_at' => now()->subDay(),
        ]);

        $updated = app(SubscriptionLifecycleService::class)->suspendFinishedGracePeriods();

        $subscription->refresh();

        $this->assertSame(1, $updated);
        $this->assertSame('suspended', $subscription->status);
        $this->assertSame(
            'Periodo de gracia finalizado; suscripción suspendida.',
            $subscription->status_reason
        );
    }

    public function test_suspended_subscription_can_be_reactivated(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda reactivada',
            'slug' => 'tienda-reactivada',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Emprendedor',
            'slug' => 'emprendedor-reactivada',
            'monthly_price' => 4.99,
            'max_products' => 20,
            'max_categories' => 20,
            'max_images_per_product' => 10,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'suspended',
            'started_at' => now()->subDays(40),
            'expires_at' => now()->subDays(20),
            'grace_period_started_at' => now()->subDays(19),
            'grace_period_ends_at' => now()->subDays(12),
            'status_reason' => 'Periodo de gracia finalizado; suscripción suspendida.',
        ]);

        $updated = app(SubscriptionLifecycleService::class)
            ->reactivateSubscription($subscription);

        $subscription->refresh();

        $this->assertTrue($updated);
        $this->assertSame('active', $subscription->status);
        $this->assertNull($subscription->grace_period_started_at);
        $this->assertNull($subscription->grace_period_ends_at);
        $this->assertSame('Suscripción reactivada.', $subscription->status_reason);
    }

    public function test_grace_period_start_does_not_reprocess_existing_grace_periods(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda sin reprocesar',
            'slug' => 'tienda-sin-reprocesar',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Emprendedor',
            'slug' => 'emprendedor-sin-reprocesar',
            'monthly_price' => 4.99,
            'max_products' => 20,
            'max_categories' => 20,
            'max_images_per_product' => 10,
            'is_active' => true,
        ]);

        $originalStartedAt = now()->subDays(2);
        $originalEndsAt = now()->addDays(5);

        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'grace_period',
            'started_at' => now()->subDays(25),
            'grace_period_started_at' => $originalStartedAt,
            'grace_period_ends_at' => $originalEndsAt,
            'status_reason' => 'Periodo de gracia iniciado.',
        ]);

        $subscription->refresh();

        $persistedStartedAt = $subscription->grace_period_started_at->timestamp;
        $persistedEndsAt = $subscription->grace_period_ends_at->timestamp;

        $updated = app(SubscriptionLifecycleService::class)->startGracePeriods();

        $subscription->refresh();

        $this->assertSame(0, $updated);
        $this->assertSame(
            $persistedStartedAt,
            $subscription->grace_period_started_at->timestamp
        );
        $this->assertSame(
            $persistedEndsAt,
            $subscription->grace_period_ends_at->timestamp
        );
    }

    public function test_active_grace_periods_are_not_suspended(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda vigente',
            'slug' => 'tienda-vigente',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Emprendedor',
            'slug' => 'emprendedor-vigente',
            'monthly_price' => 4.99,
            'max_products' => 20,
            'max_categories' => 20,
            'max_images_per_product' => 10,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'grace_period',
            'started_at' => now()->subDays(25),
            'grace_period_started_at' => now()->subDay(),
            'grace_period_ends_at' => now()->addDays(6),
            'status_reason' => 'Periodo de gracia iniciado.',
        ]);

        $updated = app(SubscriptionLifecycleService::class)
            ->suspendFinishedGracePeriods();

        $subscription->refresh();

        $this->assertSame(0, $updated);
        $this->assertSame('grace_period', $subscription->status);
        $this->assertSame(
            'Periodo de gracia iniciado.',
            $subscription->status_reason
        );
    }

    public function test_subscription_belongs_to_store_and_plan(): void
    {
        $subscription = new Subscription();

        $storeRelation = $subscription->store();
        $this->assertInstanceOf(BelongsTo::class, $storeRelation);
        $this->assertInstanceOf(Store::class, $storeRelation->getRelated());

        $planRelation = $subscription->plan();
        $this->assertInstanceOf(BelongsTo::class, $planRelation);
        $this->assertInstanceOf(Plan::class, $planRelation->getRelated());
    }
}
