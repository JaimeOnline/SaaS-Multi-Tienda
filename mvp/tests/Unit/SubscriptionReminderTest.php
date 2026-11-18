<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionReminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class SubscriptionReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_cannot_have_duplicate_reminder_types(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda recordatorios',
            'slug' => 'tienda-recordatorios',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Recordatorios',
            'slug' => 'plan-recordatorios',
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
            'started_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        SubscriptionReminder::create([
            'subscription_id' => $subscription->id,
            'type' => '7_days',
            'sent_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        SubscriptionReminder::create([
            'subscription_id' => $subscription->id,
            'type' => '7_days',
            'sent_at' => now(),
        ]);
    }

    public function test_subscription_reminder_defines_expected_attributes_and_casts(): void
    {
        $reminder = new SubscriptionReminder();

        $this->assertSame([
            'subscription_id',
            'type',
            'sent_at',
        ], $reminder->getFillable());

        $this->assertSame('datetime', $reminder->getCasts()['sent_at']);
    }

    public function test_subscription_reminder_belongs_to_subscription(): void
    {
        $relation = (new SubscriptionReminder())->subscription();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Subscription::class, $relation->getRelated());
    }
}
