@props([
    'columns' => [],
    'count' => 5,
    'selectable' => false,
    'isMobile' => false,
])

@php
    $visibleColumns = collect($columns)->filter(fn($c) => !isset($c['visible']) || $c['visible'])->count();
    $colCount = $visibleColumns + ($selectable ? 1 : 0);
@endphp

@if ($isMobile)
    @for ($i = 0; $i < $count; $i++)
        <article class="rounded-2xl border border-surface-200 bg-white p-4 shadow-sm animate-pulse">
            <div class="space-y-3">
                @for ($j = 0; $j < 4; $j++)
                    <div class="flex items-start gap-3">
                        <div class="h-4 w-24 bg-surface-200 rounded animate-pulse"></div>
                        <div class="h-4 w-full bg-surface-200 rounded animate-pulse"></div>
                    </div>
                @endfor
            </div>
        </article>
    @endfor
@else
    @for ($i = 0; $i < $count; $i++)
        <tr class="border-b border-surface-100 animate-pulse">
            @if ($selectable)
                <td class="w-12 px-4 py-3 text-center">
                    <div class="w-4 h-4 mx-auto bg-surface-200 rounded animate-pulse"></div>
                </td>
            @endif
            @for ($j = 0; $j < $visibleColumns; $j++)
                <td class="px-4 py-3">
                    <div class="h-4 w-3/4 bg-surface-200 rounded animate-pulse"></div>
                </td>
            @endfor
        </tr>
    @endfor
@endif
