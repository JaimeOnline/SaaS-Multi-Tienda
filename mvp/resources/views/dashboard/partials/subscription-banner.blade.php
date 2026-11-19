@php
    $statusConfig = [
        'trialing' => [
            'label' => 'Prueba gratuita activa',
            'icon' => 'gift',
            'variant' => 'info',
            'bg' => 'bg-semantic-info-light',
            'border' => 'border-semantic-info',
            'text' => 'text-semantic-info-dark',
            'description' =>
                'Tu prueba de 14 días expira el ' . ($subscription->expires_at?->format('d/m/Y') ?? '') . '.',
        ],
        'active' => [
            'label' => 'Suscripción activa',
            'icon' => 'check-circle',
            'variant' => 'success',
            'bg' => 'bg-semantic-success-light',
            'border' => 'border-semantic-success',
            'text' => 'text-semantic-success-dark',
            'description' =>
                'Plan ' .
                ($subscription->plan->name ?? '') .
                '. Renueva el ' .
                ($subscription->next_renewal_at?->format('d/m/Y') ?? '') .
                '.',
        ],
        'grace_period' => [
            'label' => 'Periodo de gracia',
            'icon' => 'alert-triangle',
            'variant' => 'warning',
            'bg' => 'bg-semantic-warning-light',
            'border' => 'border-semantic-warning',
            'text' => 'text-semantic-warning-dark',
            'description' =>
                'Vence prueba: ' .
                ($subscription->expires_at?->format('d/m/Y') ?? '') .
                '. Gracia hasta: ' .
                ($subscription->grace_period_ends_at?->format('d/m/Y') ?? '') .
                '.',
        ],
        'suspended' => [
            'label' => 'Suscripción suspendida',
            'icon' => 'x-circle',
            'variant' => 'error',
            'bg' => 'bg-semantic-error-light',
            'border' => 'border-semantic-error',
            'text' => 'text-semantic-error-dark',
            'description' => $subscription->status_reason ?? 'Periodo de gracia finalizado; suscripción suspendida.',
        ],
        'expired' => [
            'label' => 'Prueba expirada',
            'icon' => 'clock',
            'variant' => 'default',
            'bg' => 'bg-surface-100',
            'border' => 'border-surface-300',
            'text' => 'text-surface-700',
            'description' =>
                'Tu prueba expiró el ' .
                ($subscription->expires_at?->format('d/m/Y') ?? '') .
                '. Activa un plan para continuar.',
        ],
    ];

    $config = $statusConfig[$subscription->status ?? 'expired'] ?? $statusConfig['expired'];
@endphp

@if ($subscription)
    <x-ui.card variant="bordered" class="{{ $config['bg'] }} {{ $config['border'] }}" padding="lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $config['bg'] }}/50 {{ $config['text'] }}">
                    <x-lucide-icon name="{{ $config['icon'] }}" class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="font-semibold {{ $config['text'] }}">{{ $config['label'] }}</h3>
                    <p class="mt-1 text-sm {{ $config['text'] }}/80">{{ $config['description'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:ml-4">
                @if ($subscription->status === 'suspended')
                    <form method="POST" action="{{ route('subscriptions.reactivate') }}">
                        @csrf
                        <x-ui.button variant="success" size="sm" type="submit" icon="rotate-ccw">
                            Reactivar
                        </x-ui.button>
                    </form>
                @elseif ($subscription->status === 'trialing' && $subscription->plan->slug === 'inicial')
                    <form method="POST" action="{{ route('subscriptions.entrepreneur-trial.start') }}">
                        @csrf
                        <x-ui.button variant="primary" size="sm" type="submit" icon="sparkles">
                            Activar prueba Emprendedor
                        </x-ui.button>
                    </form>
                @elseif ($subscription->status === 'expired')
                    <x-ui.button variant="primary" size="sm" href="{{ route('store.settings.edit') }}"
                        icon="credit-card">
                        Elegir plan
                    </x-ui.button>
                @endif
            </div>
        </div>
    </x-ui.card>
@endif
