@props([
    'align' => 'right',        // left, right
    'width' => '48',           // 48, 56, 64, etc.
    'trigger' => null,         // slot trigger
    'items' => [],             // array de items: [{label, href, onclick, icon, danger}]
    'class' => '',
])

@php
    $alignmentClasses = match ($align) {
        'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
        'top' => 'origin-top',
        default => 'ltr:origin-top-right rtl:origin-top-left end-0',
    };
    
    $widthClass = match ($width) {
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
        default => $width,
    };
@endphp

<div class="relative {{ $class }} {{ $attributes->class ?? '' }}" {{ $attributes->except(['class']) }} x-data="{ open: false }" x-on:click.outside="open = false" @close.stop="open = false">
    <div x-on:click="open = ! open" x-ref="trigger">
        {{ $trigger ?? $slot }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-fast"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widthClass }} rounded-xl shadow-lg {{ $alignmentClasses }}"
        style="display: none;"
        x-on:click="open = false"
        x-trap="open"
    >
        <div class="rounded-xl ring-1 ring-surface-200 bg-white py-1 divide-y divide-surface-100">
            @foreach ($items as $item)
                @if (isset($item['divider']) && $item['divider'])
                    <div class="h-px bg-surface-100 my-1" role="separator"></div>
                @else
                    @php
                        $itemClasses = 'flex items-center gap-3 px-3 py-2 text-sm transition-colors duration-fast';
                        $itemClasses .= isset($item['danger']) && $item['danger'] 
                            ? ' text-semantic-error hover:bg-semantic-error-light' 
                            : ' text-surface-700 hover:bg-surface-50';
                        $itemClasses .= ' focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-surface-50';
                    @endphp
                    
                    @if (isset($item['href']))
                        <a href="{{ $item['href'] }}" class="{{ $itemClasses }}" {{ isset($item['onclick']) ? 'onclick="' . $item['onclick'] . '"' : '' }}>
                            @if (isset($item['icon']))
                                <x-lucide-icon name="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0" />
                            @endif
                            {{ $item['label'] }}
                        </a>
                    @elseif (isset($item['onclick']))
                        <button type="button" class="{{ $itemClasses }} w-full text-left" onclick="{{ $item['onclick'] }}">
                            @if (isset($item['icon']))
                                <x-lucide-icon name="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0" />
                            @endif
                            {{ $item['label'] }}
                        </button>
                    @else
                        <span class="{{ $itemClasses }} cursor-default">
                            @if (isset($item['icon']))
                                <x-lucide-icon name="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0" />
                            @endif
                            {{ $item['label'] }}
                        </span>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
</div>