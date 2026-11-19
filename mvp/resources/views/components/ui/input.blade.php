@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'prefix' => null,
    'suffix' => null,
    'type' => 'text',
    'required' => false,
    'autofocus' => false,
    'id' => null,
    'name' => null,
    'value' => null,
])

@php
    $id = $id ?? 'input-' . uniqid();
    $inputClasses =
        'block w-full rounded-xl border border-surface-300 bg-white px-4 py-2.5 text-surface-900 placeholder-surface-400 transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent disabled:bg-surface-50 disabled:cursor-not-allowed';

    if ($error) {
        $inputClasses .= ' border-semantic-error focus:ring-semantic-error';
    } elseif (old($name ?? $attributes->get('name'))) {
        $inputClasses .= ' border-semantic-success focus:ring-semantic-success';
    }
@endphp

<div {{ $attributes->except(['class']) }}>
    @if ($label)
        <x-ui.label :for="$id" :required="$required">{{ $label }}</x-ui.label>
    @endif

    <div class="relative">
        @if ($prefix)
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 text-surface-500 pointer-events-none">
                {{ $prefix }}
            </div>
        @endif

        <input id="{{ $id }}" name="{{ $name ?? $attributes->get('name') }}" type="{{ $type }}"
            value="{{ $value ?? old($name ?? $attributes->get('name')) }}"
            class="{{ $inputClasses }} {{ $prefix ? 'ps-10' : '' }} {{ $suffix ? 'pe-10' : '' }}"
            {{ $required ? 'required' : '' }} {{ $autofocus ? 'autofocus' : '' }}
            {{ $attributes->except(['class', 'name']) }} aria-invalid="{{ $error ? 'true' : 'false' }}"
            {{ $error ? 'aria-describedby="' . $id . '-error"' : '' }}
            {{ $hint ? 'aria-describedby="' . $id . '-hint"' : '' }} />

        @if ($suffix)
            <div class="absolute inset-y-0 end-0 flex items-center pe-3 text-surface-500 pointer-events-none">
                {{ $suffix }}
            </div>
        @endif
    </div>

    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-semantic-error" role="alert">{{ $error }}
        </p>
    @elseif ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-sm text-surface-500">{{ $hint }}</p>
    @endif
</div>
