<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Activate the Entrepreneur plan trial for the authenticated user's store.
     */
    public function startEntrepreneurTrial(Request $request): RedirectResponse
    {
        $store = $request->user()->store;

        abort_unless($store, 404);

        $entrepreneurPlan = Plan::query()
            ->where('slug', 'emprendedor')
            ->where('is_active', true)
            ->firstOrFail();

        $existingTrial = $store->subscriptions()
            ->where('plan_id', $entrepreneurPlan->id)
            ->where('status', 'trialing')
            ->exists();

        if ($existingTrial) {
            return back()->with('toast', [
                'type' => 'warning',
                'title' => 'Prueba ya activa',
                'message' => 'Ya tienes una prueba del Plan Emprendedor en curso.',
            ]);
        }

        $now = now();
        $reason = 'Prueba gratuita de 14 días iniciada manualmente.';

        $subscription = Subscription::create([
            'store_id' => $store->id,
            'plan_id' => $entrepreneurPlan->id,
            'status' => 'trialing',
            'started_at' => $now,
            'expires_at' => $now->addDays(14),
            'status_reason' => $reason,
        ]);

        SubscriptionHistory::create([
            'subscription_id' => $subscription->id,
            'from_status' => null,
            'to_status' => 'trialing',
            'reason' => $reason,
            'origin' => 'user',
            'changed_at' => $now,
        ]);

        return back()->with('toast', [
                'type' => 'success',
                'title' => 'Prueba activada',
                'message' => 'Tu prueba gratuita de 14 días del Plan Emprendedor ha comenzado.',
            ]);
    }

    /**
     * Reactivate a suspended subscription.
     */
    public function reactivateSubscription(Request $request): RedirectResponse
    {
        $store = $request->user()->store;

        abort_unless($store, 404);

        $subscription = $store->subscriptions()->latest()->first();

        if ($subscription->status !== 'suspended') {
            return back()->with('error', 'No se puede reactivar: la suscripción no está suspendida.');
        }

        $service = app(SubscriptionLifecycleService::class);
        $result = $service->reactivateSubscription($subscription);

        if ($result) {
            return back()->with('toast', [
                'type' => 'success',
                'title' => 'Suscripción reactivada',
                'message' => 'Tu suscripción ha sido reactivada correctamente.',
            ]);
        } else {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Error al reactivar',
                'message' => 'No se pudo reactivar la suscripción. Inténtalo de nuevo.',
            ]);
        }
    }
}
