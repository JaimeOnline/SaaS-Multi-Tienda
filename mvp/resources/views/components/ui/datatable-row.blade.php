@props([
    'row' => [],
    'rowIndex' => 0,
    'columns' => [],
    'selectable' => false,
    'componentId' => '',
    'isMobile' => false,
])

@php
    $rowIndex = (int) $rowIndex;
    $rowId = $row['id'] ?? ($row['uuid'] ?? "row-{$rowIndex}");
@endphp

@if ($isMobile)
    <article x-data="{ isSelected: false }"
        :class="[
            'rounded-2xl border border-surface-200 bg-white p-4 shadow-sm transition-all duration-fast',
            isSelected ? 'ring-2 ring-brand-500 bg-brand-50' : 'hover:shadow-md'
        ]"
        :aria-selected="isSelected" role="listitem" tabindex="0" @click="isSelected = !isSelected"
        @keydown.space.prevent="isSelected = !isSelected" @keydown.enter.prevent="isSelected = !isSelected"
        x-init="const parent = $el.closest('[x-data*=\"datatableComponent\"]'); if (parent) {
        parent.__alpine_components_[0].$watch('selectedRows', val=> {
        isSelected = val.includes({{ $rowIndex }});
        });
        }
        ">

        @if ($selectable)
            <div class="absolute top-3 right-3 z-10">
                <input type="checkbox" x-model="isSelected" @click.stop
                    class="w-5 h-5 rounded border-surface-300 text-brand-600 focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 cursor-pointer"
                    aria-label="Seleccionar esta fila">
            </div>
        @endif

        <div class="space-y-3">
            @foreach ($columns as $col)
                @if (isset($col['visible']) && !$col['visible'])
                    @continue
                @endif
                @if ($col['mobilePriority'] > $mobilePriority)
                    @continue
                @endif

                @php
                    $value = data_get($row, $col['key']);
                    $label = $col['label'];
                @endphp

                <div class="flex items-start gap-3 min-w-0" :class="{ 'opacity-60': @js($value === null || $value === '') }">
                    <dt class="flex-shrink-0 w-28 text-xs font-medium text-surface-500 truncate">{{ $label }}
                    </dt>
                    <dd class="flex-1 min-w-0 text-sm text-surface-900">
                        @if ($col['render'] === 'badge' || ($col['format'] === 'badge' && is_bool($value)))
                            <x-ui.badge :variant="$value ? 'success' : 'default'" :dot="true" size="sm">
                                {{ $value ? 'Activo' : 'Inactivo' }}
                            </x-ui.badge>
                        @elseif ($col['format'] === 'currency')
                            {{ number_format((float) ($value ?? 0), 2, ',', '.') }} {{ $store->currency ?? '$' }}
                        @elseif ($col['format'] === 'date' && $value)
                            {{ \Carbon\Carbon::parse($value)->format('d/m/Y H:i') }}
                        @elseif ($col['render'] && is_callable($col['render']))
                            {{ $col['render']($value, $row) }}
                        @else
                            {{ $value ?? '—' }}
                        @endif
                    </dd>
                </div>
            @endforeach

            <div class="pt-2 border-t border-surface-100 flex items-center justify-end gap-2">
                @if (isset($row['actions']))
                    @foreach ($row['actions'] as $action)
                        @if (isset($action['href']))
                            <x-ui.button variant="{{ $action['variant'] ?? 'ghost' }}" size="sm" tag="a"
                                href="{{ $action['href'] }}" target="{{ $action['target'] ?? '_self' }}"
                                class="gap-1">
                                @if (isset($action['icon']))
                                    <x-lucide-icon name="{{ $action['icon'] }}" class="w-4 h-4" />
                                @endif
                                {{ $action['label'] }}
                            </x-ui.button>
                        @else
                            <x-ui.button variant="{{ $action['variant'] ?? 'ghost' }}" size="sm"
                                @click="{{ $action['onclick'] ?? '' }}" class="gap-1">
                                @if (isset($action['icon']))
                                    <x-lucide-icon name="{{ $action['icon'] }}" class="w-4 h-4" />
                                @endif
                                {{ $action['label'] }}
                            </x-ui.button>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </article>
@else
    <tr x-data="{ isSelected: false }"
        :class="[
            'border-b border-surface-100 transition-colors duration-fast',
            isSelected ? 'bg-brand-50' : 'hover:bg-surface-50',
            @js($rowIndex % 2 === 0 ? '' : 'bg-surface-50/50')
        ]"
        :aria-selected="isSelected" role="row" @click="isSelected = !isSelected"
        @keydown.space.prevent="isSelected = !isSelected" @keydown.enter.prevent="isSelected = !isSelected"
        x-init="const parent = $el.closest('[x-data*=datatableComponent]');
        if (parent && parent.__x) {
            parent.__x.$watch('selectedRows', (val) => {
                isSelected = val.includes(@js($rowIndex));
            });
        }">

        @if ($selectable)
            <td class="w-12 px-4 py-3 text-center">
                <input type="checkbox" x-model="isSelected" @click.stop
                    class="w-4 h-4 rounded border-surface-300 text-brand-600 focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 cursor-pointer"
                    aria-label="Seleccionar fila {{ $rowIndex + 1 }}">
            </td>
        @endif

        @foreach ($columns as $col)
            @if (isset($col['visible']) && !$col['visible'])
                @continue
            @endif

            @php
                $value = data_get($row, $col['key']);
                $alignClass = match ($col['align']) {
                    'center' => 'text-center',
                    'right' => 'text-right',
                    default => 'text-left',
                };
            @endphp

            <td class="px-4 py-3 {{ $alignClass }} {{ $col['class'] }}"
                :style="'width: ' + (@js($col['width']) || 'auto')" role="gridcell">
                @if ($col['render'] === 'badge' || ($col['format'] === 'badge' && is_bool($value)))
                    <div class="inline-flex items-center justify-center">
                        <x-ui.badge :variant="$value ? 'success' : 'default'" :dot="true" size="sm">
                            {{ $value ? 'Activo' : 'Inactivo' }}
                        </x-ui.badge>
                    </div>
                @elseif ($col['format'] === 'currency')
                    <span class="font-mono font-medium">{{ number_format((float) ($value ?? 0), 2, ',', '.') }}
                        {{ $store->currency ?? '$' }}</span>
                @elseif ($col['format'] === 'date' && $value)
                    <span class="whitespace-nowrap">{{ \Carbon\Carbon::parse($value)->format('d/m/Y H:i') }}</span>
                @elseif ($col['render'] && is_callable($col['render']))
                    {{ $col['render']($value, $row) }}
                @elseif ($col['key'] === 'actions' && isset($row['actions']))
                    <div
                        class="flex items-center justify-{{ $col['align'] === 'right' ? 'end' : ($col['align'] === 'center' ? 'center' : 'start') }} gap-1.5">
                        @foreach ($row['actions'] as $action)
                            @if (isset($action['href']))
                                <a href="{{ $action['href'] }}"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg transition-colors
                                       {{ $action['variant'] === 'danger' ? 'text-semantic-error hover:bg-semantic-error-light' : 'text-surface-600 hover:bg-surface-100' }}"
                                    @if (isset($action['target'])) target="{{ $action['target'] }}" @endif>
                                    @if (isset($action['icon']))
                                        <x-lucide-icon name="{{ $action['icon'] }}" class="w-4 h-4" />
                                    @endif
                                    <span class="hidden sm:inline">{{ $action['label'] }}</span>
                                </a>
                            @else
                                <button type="button" @click="{{ $action['onclick'] }}"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg transition-colors
                                            {{ $action['variant'] === 'danger' ? 'text-semantic-error hover:bg-semantic-error-light' : 'text-surface-600 hover:bg-surface-100' }}">
                                    @if (isset($action['icon']))
                                        <x-lucide-icon name="{{ $action['icon'] }}" class="w-4 h-4" />
                                    @endif
                                    <span class="hidden sm:inline">{{ $action['label'] }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                @else
                    {{ $value ?? '—' }}
                @endif
            </td>
        @endforeach
    </tr>
@endif
