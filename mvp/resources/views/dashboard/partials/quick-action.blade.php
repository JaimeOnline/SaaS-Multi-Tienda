@props([
    'icon',
    'label',
    'url' => '#',
    'target' => '_self',
    'type' => 'link',
    'modal' => null,
    'variant' => 'secondary',
    'badge' => null,
])

@if ($type === 'modal')
    <x-ui.button variant="{{ $variant }}" size="md" class="h-24 flex-col gap-2 text-left"
        @click="$dispatch('open-modal', { detail: '{{ $modal }}' })">
        <div class="flex items-center justify-between w-full">
            <x-lucide-icon name="{{ $icon }}" class="h-6 w-6" />
            @if ($badge !== null && $badge > 0)
                <x-ui.badge variant="error" size="sm" dot>{{ $badge }}</x-ui.badge>
            @endif
        </div>
        <span class="text-sm font-medium">{{ $label }}</span>
    </x-ui.button>
@else
    <x-ui.button variant="{{ $variant }}" size="md" tag="a" href="{{ $url }}"
        target="{{ $target ?? '_self' }}" class="h-24 flex-col gap-2 text-left">
        <div class="flex items-center justify-between w-full">
            <x-lucide-icon name="{{ $icon }}" class="h-6 w-6" />
            @if ($badge !== null && $badge > 0)
                <x-ui.badge variant="error" size="sm" dot>{{ $badge }}</x-ui.badge>
            @endif
        </div>
        <span class="text-sm font-medium">{{ $label }}</span>
    </x-ui.button>
@endif
