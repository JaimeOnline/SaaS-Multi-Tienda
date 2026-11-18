@props([
    'name' => '',
    'size' => 'md',         // sm, md, lg, xl, full
    'variant' => 'default', // default, fullscreen, bottom-sheet
    'closeable' => true,
    'persistent' => false,  // no cerrar con click en backdrop
    'title' => '',
    'footer' => true,
    'show' => false,
])

@php
    $sizeClasses = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        'full' => 'sm:max-w-4xl',
    ];
    
    $variantClasses = [
        'default' => '',
        'fullscreen' => 'inset-0 max-w-full m-0 rounded-none',
        'bottom-sheet' => 'bottom-0 left-0 right-0 top-auto max-h-[80vh] rounded-t-2xl',
    ];
    
    $baseClasses = 'fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50';
    $modalClasses = 'mb-6 bg-white rounded-2xl overflow-hidden shadow-xl transform transition-all sm:w-full mx-auto';
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)].filter(el => !el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1 },
        lastActiveElement: null,
    }"
    x-init="
        $watch('show', value => {
            if (value) {
                this.lastActiveElement = document.activeElement;
                document.body.classList.add('overflow-y-hidden');
                @if($attributes->has('focusable'))
                    setTimeout(() => this.firstFocusable()?.focus(), 100);
                @endif
            } else {
                document.body.classList.remove('overflow-y-hidden');
                this.lastActiveElement?.focus();
            }
        })
    "
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="!{{ $persistent ? 'true' : 'false' }} && show = false"
    x-on:keydown.tab.prevent="$event.shiftKey ? this.prevFocusable().focus() : this.nextFocusable().focus()"
    x-show="show"
    class="{{ $baseClasses }}"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="@if(!$persistent)show = false@endif"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="{{ $modalClasses }} {{ $sizeClasses[$size] }} {{ $variantClasses[$variant] }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        @click.outside="@if(!$persistent)$dispatch('close-modal', { detail: '{{ $name }}' })@endif"
    >
        @if ($title || $closeable)
            <div class="flex items-center justify-between p-4 border-b border-surface-200">
                @if ($title)
                    <h2 class="text-lg font-semibold text-surface-900">{{ $title }}</h2>
                @endif
                @if ($closeable)
                    <button
                        x-on:click="$dispatch('close-modal', { detail: '{{ $name }}' })"
                        type="button"
                        class="rounded-lg p-1.5 text-surface-400 hover:text-surface-600 hover:bg-surface-100 transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-brand-500"
                        aria-label="Cerrar modal"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        <div class="p-4 sm:p-6">
            {{ $slot }}
        </div>

        @if ($footer)
            <div class="flex items-center justify-end gap-3 p-4 border-t border-surface-200 bg-surface-50">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>