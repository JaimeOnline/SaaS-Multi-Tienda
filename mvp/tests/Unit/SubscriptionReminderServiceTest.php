<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\SubscriptionReminder;
use App\Models\User;
use App\Services\SubscriptionReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_trial_reminder_is_detected_and_marked_once(): void
    {
        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda avisos',
            'slug' => 'tienda-avisos',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan Avisos',
            'slug' => 'plan-avisos',
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
            'expires_at' => now()->startOfDay()->addDays(3),
        ]);

        $service = app(SubscriptionReminderService::class);

        $pending = $service->pendingTrialReminders();

        $this->assertCount(1, $pending);
        $this->assertSame($subscription->id, $pending->first()->id);

        $service->markAsSent($subscription, 3);

        $this->assertDatabaseHas('subscription_reminders', [
            'subscription_id' => $subscription->id,
            'type' => '3_days',
        ]);

        $this->assertCount(0, $service->pendingTrialReminders());
    }
}
