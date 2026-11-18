<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600">Panel de administración</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Dashboard') }}
                </h2>
            </div>
            <span class="text-sm text-gray-500">Resumen de tu tienda</span>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="p-5 text-gray-900 sm:p-6">
                    <div
                        class="flex flex-col gap-3 border-b border-gray-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-indigo-600">Tu tienda</p>
                            <h3 class="mt-1 break-words text-2xl font-bold text-gray-900">
                                {{ $store->name }}
                            </h3>
                        </div>

                        <div class="flex flex-col items-start gap-3 sm:items-end">
                            <span
                                class="inline-flex w-fit rounded-full px-3 py-1 text-sm font-semibold {{ $store->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ $store->is_published ? 'Publicada' : 'No publicada' }}
                            </span>

                            <div class="flex flex-wrap gap-2">
                                @if ($store->is_published)
                                    <a href="{{ route('store.public', $store->slug) }}" target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Ver tienda
                                    </a>
                                @endif

                                <a href="{{ route('store.settings.edit') }}"
                                    class="inline-flex items-center rounded-xl bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                                    Configurar tienda
                                </a>
                            </div>
                        </div>
                    </div>

                    @if ($subscription?->status === 'active' && $subscription->plan?->slug === 'inicial')
                        <div class="mt-5 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                            <p class="font-semibold text-indigo-900">
                                Estás usando el Plan Inicial
                            </p>
                            <p class="mt-1 text-sm text-indigo-700">
                                Activa tu prueba gratuita de 14 días del Plan Emprendedor para acceder a mayores
                                límites.
                            </p>

                            <form method="POST" action="{{ route('subscriptions.entrepreneur-trial.start') }}"
                                class="mt-3">
                                @csrf
                                <button type="submit"
                                    class="inline-flex rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Activar prueba gratuita
                                </button>
                            </form>
                        </div>
                    @elseif ($subscription?->status === 'trialing')
                        <div class="mt-5 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="font-semibold text-emerald-900">
                                Prueba del Plan Emprendedor activa
                            </p>
                            <p class="mt-1 text-sm text-emerald-700">
                                Vence el {{ $subscription->expires_at?->format('d/m/Y H:i') }}.
                            </p>
                        </div>
                    @endif

                    @if ($subscription?->status === 'grace_period')
                        <div class="mt-5 rounded-xl border border-amber-100 bg-amber-50 p-4">
                            <p class="font-semibold text-amber-900">
                                Periodo de gracia activo
                            </p>
                            <p class="mt-1 text-sm text-amber-700">
                                Vence prueba: {{ $subscription->expires_at?->format('d/m/Y H:i') }}.
                                <br>Gracia hasta: {{ $subscription->grace_period_ends_at?->format('d/m/Y H:i') }}.
                            </p>
                        </div>
                    @endif

                    @if ($subscription?->status === 'suspended')
                        <div class="mt-5 rounded-xl border border-red-100 bg-red-50 p-4">
                            <p class="font-semibold text-red-900">
                                Suspendida
                            </p>
                            <p class="mt-1 text-sm text-red-700">
                                {{ $subscription->status_reason ?: 'Periodo de gracia finalizado; suscripción suspendida.' }}
                            </p>

                            <!-- Formulario de reactivación -->
                            <form method="POST" action="{{ route('subscriptions.reactivate') }}" class="mt-3">
                                @csrf
                                <button type="submit" class="inline-flex rounded-xl bg-green-600 text-slate-900 font-bold text-xs px-4 py-2 uppercase transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <span class="sr-only">Reactivar suscripción</span>
                                    Reactivar
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($subscription?->status === 'expired')
                        <div class="mt-5 rounded-xl border border-gray-100 bg-gray-50 p-4">
                            <p class="font-semibold text-gray-900">
                                Prueba expirada
                            </p>
                            <p class="mt-1 text-sm text-gray-700">
                                {{ $subscription->expires_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif

                    <dl class="mt-5 grid gap-4 text-sm text-gray-600 sm:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="font-medium text-gray-500">Identificador</dt>
                            <dd class="mt-1 break-all font-semibold text-gray-900">{{ $store->slug }}</dd>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="font-medium text-gray-500">WhatsApp</dt>
                            <dd class="mt-1 break-words font-semibold text-gray-900">{{ $store->whatsapp }}</dd>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <dt class="font-medium text-gray-500">Moneda</dt>
                            <dd class="mt-1 font-semibold text-gray-900">{{ $store->currency }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
