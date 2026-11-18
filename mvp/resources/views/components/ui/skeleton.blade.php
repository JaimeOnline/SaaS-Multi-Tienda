@props([
    'variant' => 'text',     // text, circular, rectangular
    'animation' => 'pulse',  // pulse, wave
    'class' => '',
    'count' => 1,            // para variant text: número de líneas
    'width' => '100%',       // para rectangular
    'height' => '1rem',      // para rectangular
    'size' => '4rem',        // para circular
])

@php
    $animationClass = $animation === 'wave' ? 'animate-skeleton-wave' : 'animate-pulse';
    
    $variantClasses = [
        'text' => '',
        'circular' => 'rounded-full',
        'rectangular' => 'rounded-xl',
    ];
    
    $baseClasses = 'bg-surface-200 ' . $animationClass;
@endphp

@if ($variant === 'text')
    <div class="space-y-3 {{ $class }} {{ $attributes->class ?? '' }}" {{ $attributes->except(['class']) }}>
        @for ($i = 0; $i < $count; $i++)
            <div class="h-4 {{ $baseClasses }} {{ $variantClasses[$variant] }}" style="width: {{ $i === $count - 1 ? '60%' : '100%' }}"></div>
        @endfor
    </div>
@elseif ($variant === 'circular')
    <div class="{{ $baseClasses }} {{ $variantClasses[$variant] }} {{ $class }} {{ $attributes->class ?? '' }}" style="width: {{ $size }}; height: {{ $size }};" {{ $attributes->except(['class']) }}></div>
@else
    <div class="{{ $baseClasses }} {{ $variantClasses[$variant] }} {{ $class }} {{ $attributes->class ?? '' }}" style="width: {{ $width }}; height: {{ $height }};" {{ $attributes->except(['class']) }}></div>
@endif

<style>
    @keyframes skeleton-wave {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .animate-skeleton-wave {
        background: linear-gradient(90deg, #e5e5e5 25%, #d4d4d4 50%, #e5e5e5 75%);
        background-size: 200% 100%;
        animation: skeleton-wave 1.5s ease-in-out infinite;
    }
</style>