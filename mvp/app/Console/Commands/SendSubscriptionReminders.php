<?php

namespace App\Console\Commands;

use App\Notifications\SubscriptionExpirationReminder;
use App\Services\SubscriptionReminderService;
use Illuminate\Console\Command;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';

    protected $description = 'Procesa los recordatorios pendientes de suscripciones';

    public function handle(SubscriptionReminderService $reminderService): int
    {
        $processed = 0;

        foreach ($reminderService->pendingTrialReminders() as $subscription) {
            $days = (int) now()->startOfDay()->diffInDays(
                $subscription->expires_at->copy()->startOfDay()
            );

            $subscription->store->user->notify(
                new SubscriptionExpirationReminder($subscription, $days)
            );

            $reminderService->markAsSent($subscription, $days);
            $processed++;
        }

        $this->info("Recordatorios procesados: {$processed}");

        return self::SUCCESS;
    }
}
