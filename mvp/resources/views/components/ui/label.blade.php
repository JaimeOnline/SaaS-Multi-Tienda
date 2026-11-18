@props([
    'for' => null,
    'value' => null,
    'required' => false,
    'optional' => false,
])

@php
    $classes = 'block text-sm font-medium text-surface-700 mb-1.5';
@endphp

<label {{ $for ? 'for="' . $for . '"' : '' }} class="{{ $classes }} {{ $attributes->class ?? '' }}" {{ $attributes->except(['class']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-semantic-error ml-0.5" aria-hidden="true">*</span>
        <span class="sr-only">(requerido)</span>
    @endif
    @if ($optional)
        <span class="text-surface-400 ml-1 text-xs">(opcional)</span>
    @endif
</label>