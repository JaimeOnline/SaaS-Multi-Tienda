<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpirationReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendSubscriptionRemindersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_and_records_pending_reminder(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $store = Store::create([
            'user_id' => $user->id,
            'name' => 'Tienda comando avisos',
            'slug' => 'tienda-comando-avisos',
            'whatsapp' => '0000000000',
        ]);

        $plan = Plan::create([
            'name' => 'Plan comando avisos',
            'slug' => 'plan-comando-avisos',
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

        $this->artisan('subscriptions:send-reminders')
            ->expectsOutput('Recordatorios procesados: 1')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            SubscriptionExpirationReminder::class,
            fn(SubscriptionExpirationReminder $notification): bool =>
            $notification->daysRemaining === 3
        );

        $this->assertDatabaseHas('subscription_reminders', [
            'subscription_id' => $subscription->id,
            'type' => '3_days',
        ]);
    }
}
