@props([
    'variant' => 'primary', // primary, secondary, ghost, danger, outline, success
    'size' => 'md',         // sm, md, lg
    'loading' => false,
    'disabled' => false,
    'icon' => null,         // 'lucide-icon-name' o componente
    'iconPosition' => 'left', // left, right
    'fullWidth' => false,
    'type' => 'button',     // button, submit, reset
    'tag' => 'button',      // button, a
    'href' => null,         // si tag="a"
])

@php
    $variantClasses = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700 active:bg-brand-800 focus:ring-brand-500',
        'secondary' => 'bg-surface-100 text-surface-900 hover:bg-surface-200 active:bg-surface-300 focus:ring-surface-400',
        'ghost' => 'bg-transparent text-surface-700 hover:bg-surface-100 active:bg-surface-200 focus:ring-surface-400',
        'danger' => 'bg-semantic-error text-white hover:bg-semantic-error-dark active:bg-semantic-error-dark focus:ring-semantic-error',
        'outline' => 'border-2 border-surface-300 text-surface-700 hover:bg-surface-50 active:bg-surface-100 focus:ring-surface-400',
        'success' => 'bg-semantic-success text-white hover:bg-semantic-success-dark active:bg-semantic-success-dark focus:ring-semantic-success',
    ];
    
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2.5',
    ];
    
    $baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
@endphp

<{{ $tag }} {{ $tag === 'a' ? 'href="' . $href . '"' : 'type="' . $type . '"' }} 
    class="{{ $baseClasses }} {{ $variantClasses[$variant] }} {{ $sizeClasses[$size] }} {{ $fullWidth ? 'w-full' : '' }} {{ $attributes->class ?? '' }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->except(['class']) }}>
    
    @if ($loading)
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif ($icon)
        <x-lucide-icon name="{{ $icon }}" class="w-4 h-4" />
    @endif
    
    {{ $slot }}
    
    @if ($icon && $iconPosition === 'right' && !$loading)
        <x-lucide-icon name="{{ $icon }}" class="w-4 h-4" />
    @endif
</{{ $tag }}>