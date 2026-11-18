<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_history_defines_expected_fillable_attributes(): void
    {
        $history = new SubscriptionHistory();
        $fillable = $history->getFillable();

        $this->assertEquals([
            'subscription_id',
            'from_status',
            'to_status',
            'reason',
            'origin',
            'changed_at',
        ], $fillable);
    }

    public function test_subscription_history_defines_expected_casts(): void
    {
        $history = new SubscriptionHistory();
        $casts = $history->getCasts();

        $this->assertArrayHasKey('changed_at', $casts);
        $this->assertEquals('datetime', $casts['changed_at']);
    }

    public function test_subscription_history_belongs_to_subscription(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);
        $plan = Plan::create([
            'name' => 'Plan Test',
            'slug' => 'test-plan',
            'monthly_price' => 9.99,
            'max_products' => 100,
            'max_categories' => 10,
            'max_images_per_product' => 5,
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'started_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $history = SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'to_status' => 'expired',
            'changed_at' => now(),
        ]);

        $this->assertInstanceOf(Subscription::class, $history->subscription);
        $this->assertEquals($subscription->id, $history->subscription->id);
    }

    public function test_subscription_can_have_multiple_history_entries(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);
        $plan = Plan::create([
            'name' => 'Plan Test',
            'slug' => 'test-plan-2',
            'monthly_price' => 9.99,
            'max_products' => 100,
            'max_categories' => 10,
            'max_images_per_product' => 5,
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'started_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'to_status' => 'trialing',
            'changed_at' => now()->subDays(2),
        ]);
        SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'to_status' => 'expired',
            'changed_at' => now()->subDay(),
        ]);
        SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'to_status' => 'grace_period',
            'changed_at' => now(),
        ]);

        $this->assertCount(3, $subscription->history);
    }

    public function test_subscription_history_is_ordered_by_changed_at(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);
        $plan = Plan::create([
            'name' => 'Plan Test',
            'slug' => 'test-plan-3',
            'monthly_price' => 9.99,
            'max_products' => 100,
            'max_categories' => 10,
            'max_images_per_product' => 5,
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'started_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $first = SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'changed_at' => now()->subDays(2),
            'to_status' => 'trialing',
        ]);

        $second = SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'changed_at' => now()->subDay(),
            'to_status' => 'expired',
        ]);

        $third = SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'changed_at' => now(),
            'to_status' => 'grace_period',
        ]);

        $history = $subscription->history;

        $this->assertEquals($first->id, $history[0]->id);
        $this->assertEquals($second->id, $history[1]->id);
        $this->assertEquals($third->id, $history[2]->id);
    }

    public function test_subscription_history_records_creation_with_null_from_status(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);
        $plan = Plan::create([
            'name' => 'Plan Test',
            'slug' => 'test-plan-4',
            'monthly_price' => 9.99,
            'max_products' => 100,
            'max_categories' => 10,
            'max_images_per_product' => 5,
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'started_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $history = SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'from_status' => null,
            'to_status' => 'trialing',
            'reason' => 'Prueba gratuita iniciada.',
            'origin' => 'user',
            'changed_at' => now(),
        ]);

        $this->assertNull($history->from_status);
        $this->assertEquals('trialing', $history->to_status);
        $this->assertEquals('user', $history->origin);
    }

    public function test_subscription_history_records_transition_with_reason_and_origin(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $user->id]);
        $plan = Plan::create([
            'name' => 'Plan Test',
            'slug' => 'test-plan-5',
            'monthly_price' => 9.99,
            'max_products' => 100,
            'max_categories' => 10,
            'max_images_per_product' => 5,
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $plan->id,
            'status' => 'trialing',
            'started_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $history = SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'from_status' => 'trialing',
            'to_status' => 'expired',
            'reason' => 'La prueba gratuita expiró.',
            'origin' => 'system',
            'changed_at' => now(),
        ]);

        $this->assertEquals('trialing', $history->from_status);
        $this->assertEquals('expired', $history->to_status);
        $this->assertEquals('La prueba gratuita expiró.', $history->reason);
        $this->assertEquals('system', $history->origin);
    }
}
