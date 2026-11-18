@props([
    'type' => 'info',           // success, error, warning, info
    'title' => '',
    'description' => '',
    'action' => null,           // ['label' => 'Ver', 'href' => '/url']
    'dismissible' => true,
    'id' => null,
])

@php
    $typeClasses = [
        'success' => 'bg-semantic-success-light border-semantic-success text-semantic-success-dark',
        'error' => 'bg-semantic-error-light border-semantic-error text-semantic-error-dark',
        'warning' => 'bg-semantic-warning-light border-semantic-warning text-semantic-warning-dark',
        'info' => 'bg-semantic-info-light border-semantic-info text-semantic-info-dark',
    ];
    
    $iconSvg = [
        'success' => '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        'error' => '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10 7.293 11.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
        'warning' => '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
        'info' => '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
    ];
    
    $toastId = $id ?? 'toast-' . uniqid();
@endphp

<div
    id="{{ $toastId }}"
    class="flex gap-3 p-4 rounded-xl border {{ $typeClasses[$type] }} shadow-lg animate-slide-in"
    role="alert"
    aria-live="polite"
    {{ $attributes }}
>
    <div class="flex-shrink-0 text-current">
        {!! $iconSvg[$type] !!}
    </div>
    
    <div class="flex-1 min-w-0">
        @if ($title)
            <h4 class="font-semibold text-current">{{ $title }}</h4>
        @endif
        @if ($description)
            <p class="mt-0.5 text-sm text-current/90">{{ $description }}</p>
        @endif
    </div>
    
    @if ($action)
        <a href="{{ $action['href'] }}" class="flex-shrink-0 text-sm font-semibold text-current underline hover:no-underline transition-colors duration-fast">
            {{ $action['label'] }}
        </a>
    @endif
</div>

<style>
    @keyframes slide-in {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>