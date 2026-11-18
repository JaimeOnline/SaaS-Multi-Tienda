<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionReminder;
use Illuminate\Support\Collection;

class SubscriptionReminderService
{
    /**
     * Get pending trial reminders for 7, 3, and 1 day before expiration.
     */
    public function pendingTrialReminders(): Collection
    {
        $now = now();

        return Subscription::query()
            ->with(['store.user', 'plan'])
            ->where('status', 'trialing')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [
                $now->copy()->addDay()->startOfDay(),
                $now->copy()->addDays(7)->endOfDay(),
            ])
            ->get()
            ->filter(function (Subscription $subscription): bool {
                $timezone = config('app.timezone');

                $expiresAt = $subscription->expires_at
                    ->copy()
                    ->setTimezone($timezone)
                    ->startOfDay();

                $today = now($timezone)->startOfDay();
                $days = (int) $today->diffInDays($expiresAt);

                return in_array($days, [1, 3, 7], true)
                    && ! $subscription->reminders()
                        ->where('type', "{$days}_days")
                        ->exists();
            });
    }

    /**
     * Mark a reminder as sent.
     */
    public function markAsSent(Subscription $subscription, int $days): SubscriptionReminder
    {
        return SubscriptionReminder::firstOrCreate(
            [
                'subscription_id' => $subscription->id,
                'type' => "{$days}_days",
            ],
            [
                'sent_at' => now(),
            ]
        );
    }
}
