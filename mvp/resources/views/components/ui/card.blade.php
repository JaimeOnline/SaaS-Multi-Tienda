@props([
    'variant' => 'default', // default, bordered, elevated, interactive
    'padding' => 'md',      // none, sm, md, lg
    'hover' => false,
    'class' => '',
])

@php
    $variantClasses = [
        'default' => 'bg-white',
        'bordered' => 'bg-white border border-surface-200',
        'elevated' => 'bg-white shadow-lg',
        'interactive' => 'bg-white border border-surface-200 transition-all duration-fast hover:shadow-md hover:-translate-y-0.5 hover:border-surface-300 cursor-pointer',
    ];
    
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-3',
        'md' => 'p-5',
        'lg' => 'p-6',
    ];
    
    $baseClasses = 'rounded-2xl';
@endphp

<div class="{{ $baseClasses }} {{ $variantClasses[$variant] }} {{ $paddingClasses[$padding] }} {{ $class }} {{ $attributes->class ?? '' }}" {{ $attributes->except(['class']) }}>
    {{ $slot }}
</div>