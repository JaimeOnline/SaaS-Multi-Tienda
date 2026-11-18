<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
	public function handle(Request $request, Closure $next): Response
	{
		$subscription = $request->user()
			->store
			?->subscriptions()
			->latest()
			->first();

		// Estados que restringen acceso (requieren lista blanca)
		$restrictedStatuses = ['grace_period', 'suspended'];

		// Si la suscription NO esta en estado restringido à acceso normal
		if (!in_array($subscription?->status, $restrictedStatuses)) {
			return $next($request);
		}

		// esta en grace_period o suspended ->verificar lista blanca
		$allowedRoutes = [
			'dashboard',
			'store.settings.edit',
			'subscriptions.reactivate',
		];

		$currentRoute = $request->route()?->getName();

		// Si la ruta actual esta en la lista blanca à PERMITH acceso
		if (in_array($currentRoute, $allowedRoutes)) {
			return $next($request);
		}

		// Ruta NO permitida   403 con mensaje contextual
		$message = $subscription->status == 'suspended'
			? 'La suscription é está suspendida. Solo puedes acceder al dashboard, configuración o reactivar tu suscription.'
			: 'Estás en periodo de gracia. Solo puedes acceder al Dashboard, configuración o reactivar tu suscription.';

		abort(403, $message);
	}
}
