@props([
    'label',
    'value',
    'sparkline' => null,
    'icon',
    'iconColor' => 'brand',
    'actionText' => '',
    'actionUrl' => '#',
    'actionType' => 'link',
    'actionModal' => null,
    'actionTarget' => '_self',
    'actionDisabled' => false,
    'variant' => 'primary',
])

@php
    $iconColors = [
        'brand' => 'text-brand-600 bg-brand-100',
        'success' => 'text-semantic-success-dark bg-semantic-success-light',
        'warning' => 'text-semantic-warning-dark bg-semantic-warning-light',
        'error' => 'text-semantic-error-dark bg-semantic-error-light',
        'info' => 'text-semantic-info-dark bg-semantic-info-light',
    ];
@endphp

<x-ui.card variant="bordered" padding="md" class="relative">
    <div class="flex items-start justify-between">
        <div class="min-w-0">
            <p class="text-sm font-medium text-surface-500 truncate">{{ $label }}</p>
            <p class="mt-1.5 text-2xl font-bold text-surface-900 tabular-nums truncate">{{ $value }}</p>
        </div>
        <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $iconColors[$iconColor] ?? $iconColors['brand'] }}">
            <x-lucide-icon name="{{ $icon }}" class="h-5 w-5" />
        </div>
    </div>

    @if ($sparkline)
        <div class="mt-4 h-16" aria-hidden="true">
            <svg viewBox="0 0 100 40" class="w-full h-full" preserveAspectRatio="none" role="img"
                aria-label="Gráfico de tendencia de 7 días">
                @php
                    $max = max($sparkline) ?: 1;
                    $points = collect($sparkline)
                        ->map(function ($v, $i) use ($max) {
                            $x = ($i / 6) * 100;
                            $y = 40 - ($v / $max) * 35;
                            return "{$x},{$y}";
                        })
                        ->implode(' ');
                @endphp
                <!-- Área relleno -->
                <polygon points="0,40 {{ $points }} 100,40" fill="currentColor" class="text-brand-500/10" />
                <!-- Línea -->
                <polyline fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" points="{{ $points }}" class="text-brand-500" />
            </svg>
        </div>
    @endif

    <div class="mt-4 pt-4 border-t border-surface-100">
        @if ($actionType === 'modal')
            <x-ui.button variant="{{ $variant }}" size="sm" fullWidth
                @click="$dispatch('open-modal', { detail: '{{ $actionModal }}' })">
                {{ $actionText }}
            </x-ui.button>
        @else
            <x-ui.button variant="{{ $variant }}" size="sm" fullWidth tag="a"
                href="{{ $actionUrl }}" target="{{ $actionTarget ?? '_self' }}" :disabled="$actionDisabled">
                {{ $actionText }}
            </x-ui.button>
        @endif
    </div>
</x-ui.card>
