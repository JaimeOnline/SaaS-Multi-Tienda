@props([
    'name' => '',
    'class' => 'w-5 h-5',
])

@php
    $icons = [
        'layout-dashboard' => 'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z',
        'package' => 'M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z',
        'shopping-bag' => 'M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0',
        'folder-tree' => 'M20 10a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2.5a1 1 0 0 1-.8-.4l-.9-1.2a1 1 0 0 0-1.7 0L9.6 3.6a1 1 0 0 1-.8.4H6a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2.5a1 1 0 0 1 .8.4l.9 1.2a1 1 0 0 0 1.7 0l.3-.4h7.5a1 1 0 0 1 1 1v3a1 1 0 0 0 1 1h2.5a1 1 0 0 1 .8.4l.9 1.2a1 1 0 0 0 1.7 0l.3-.4H20a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-2.5a1 1 0 0 1-.8-.4l-.9-1.2a1 1 0 0 0-1.7 0L10.4 15.4a1 1 0 0 1-.8-.4H6a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h2.5a1 1 0 0 1 .8.4l.9 1.2a1 1 0 0 0 1.7 0l.3-.4h7.5a1 1 0 0 1 1 1v3a1 1 0 0 0 1 1',
        'store' => 'M20.84 4.61a2 2 0 0 0-2.78-.04l-8 6a2 2 0 0 1-2.12 0l-8-6a2 2 0 0 1 0-3.28 2 2 0 0 1 2.78-.04l8 6a2 2 0 0 0 2.12 0zM4 12.69l6.1-4.58a2 2 0 0 1 3.76 0L20 12.69V19a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zM4 12.69l6.1 4.58a2 2 0 0 0 3.76 0L20 12.69',
        'credit-card' => 'M21 11V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5',
        'user-plus' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M16 3.13a4 4 0 0 1 0 7.75M20 21v-2a4 4 0 0 0-3-3.87M12 17h4M14 15v4',
        'more-horizontal' => 'M12 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2zM19 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2zM5 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2z',
        'user' => 'M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2M16 3.13a4 4 0 0 1 0 7.75',
        'x' => 'M18 6L6 18M6 6l12 12',
        'chevron-left' => 'M15 18l-6-6 6-6',
        'chevron-right' => 'M9 18l6-6-6-6',
        'menu' => 'M3 12h18M3 6h18M3 18h18',
        'close' => 'M18 6L6 18M6 6l12 12',
    ];

    $path = $icons[$name] ?? '';
@endphp

@if ($path)
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" />
    </svg>
@else
    <!-- Fallback for unknown icons -->
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
@endif