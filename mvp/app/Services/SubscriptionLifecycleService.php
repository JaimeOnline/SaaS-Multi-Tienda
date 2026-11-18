<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use Carbon\CarbonInterface;

class SubscriptionLifecycleService
{
    /**
     * Expire trial subscriptions whose expiration date has passed.
     */
    public function expireTrials(): int
    {
        $now = now();
        $reason = 'La prueba gratuita expiró.';

        return Subscription::query()
            ->where('status', 'trialing')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get()
            ->each(function (Subscription $subscription) use ($now, $reason) {
                $fromStatus = $subscription->status;
                $subscription->update([
                    'status' => 'expired',
                    'status_reason' => $reason,
                ]);
                $this->recordHistory($subscription, $fromStatus, 'expired', $reason, 'system', $now);
            })
            ->count();
    }

    /**
     * Start a seven-day grace period for expired subscriptions.
     */
    public function startGracePeriods(): int
    {
        $now = now();
        $reason = 'Periodo de gracia iniciado.';

        return Subscription::query()
            ->where('status', 'expired')
            ->get()
            ->each(function (Subscription $subscription) use ($now, $reason) {
                $fromStatus = $subscription->status;
                $subscription->update([
                    'status' => 'grace_period',
                    'grace_period_started_at' => $now,
                    'grace_period_ends_at' => $now->copy()->addDays(7),
                    'status_reason' => $reason,
                ]);
                $this->recordHistory($subscription, $fromStatus, 'grace_period', $reason, 'system', $now);
            })
            ->count();
    }

    /**
     * Suspend subscriptions whose grace period has ended.
     */
    public function suspendFinishedGracePeriods(): int
    {
        $now = now();
        $reason = 'Periodo de gracia finalizado; suscripción suspendida.';

        return Subscription::query()
            ->where('status', 'grace_period')
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '<=', $now)
            ->get()
            ->each(function (Subscription $subscription) use ($now, $reason) {
                $fromStatus = $subscription->status;
                $subscription->update([
                    'status' => 'suspended',
                    'status_reason' => $reason,
                ]);
                $this->recordHistory($subscription, $fromStatus, 'suspended', $reason, 'system', $now);
            })
            ->count();
    }

    /**
     * Reactivate a suspended subscription.
     */
    public function reactivateSubscription(Subscription $subscription): bool
    {
        if ($subscription->status !== 'suspended') {
            return false;
        }

        $fromStatus = $subscription->status;
        $reason = 'Suscripción reactivada.';
        $now = now();

        $updated = $subscription->update([
            'status' => 'active',
            'grace_period_started_at' => null,
            'grace_period_ends_at' => null,
            'status_reason' => $reason,
        ]);

        if ($updated) {
            $this->recordHistory($subscription, $fromStatus, 'active', $reason, 'user', $now);
        }

        return $updated;
    }

    /**
     * Record a status transition in the subscription history.
     */
    private function recordHistory(
        Subscription $subscription,
        ?string $fromStatus,
        string $toStatus,
        ?string $reason,
        string $origin,
        CarbonInterface $changedAt
    ): void {
        SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'origin' => $origin,
            'changed_at' => $changedAt,
        ]);
    }
}
