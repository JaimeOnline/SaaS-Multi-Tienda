@props([
    'variant' => 'default', // default, success, warning, error, info, outline
    'size' => 'md',         // sm, md
    'dot' => false,
    'class' => '',
])

@php
    $variantClasses = [
        'default' => 'bg-surface-100 text-surface-700',
        'success' => 'bg-semantic-success-light text-semantic-success-dark',
        'warning' => 'bg-semantic-warning-light text-semantic-warning-dark',
        'error' => 'bg-semantic-error-light text-semantic-error-dark',
        'info' => 'bg-semantic-info-light text-semantic-info-dark',
        'outline' => 'bg-transparent border border-surface-300 text-surface-700',
    ];
    
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
    ];
    
    $baseClasses = 'inline-flex items-center gap-1 font-medium rounded-full';
@endphp

<span class="{{ $baseClasses }} {{ $variantClasses[$variant] }} {{ $sizeClasses[$size] }} {{ $class }} {{ $attributes->class ?? '' }}" {{ $attributes->except(['class']) }}>
    @if ($dot)
        <span class="relative top-[1px] h-1.5 w-1.5 rounded-full bg-current opacity-70" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>