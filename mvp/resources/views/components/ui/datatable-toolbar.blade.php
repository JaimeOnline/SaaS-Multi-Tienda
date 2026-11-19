@props([
    'componentId' => '',
    'columns' => [],
    'filterable' => true,
    'selectable' => false,
    'bulkActions' => [],
    'pagination' => [],
])

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-4 py-3 border-b border-surface-200 bg-surface-50 sticky top-0 z-10"
     role="toolbar"
     aria-label="Barra de herramientas de la tabla">

    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
        @if ($filterable)
            <div class="relative w-full sm:w-72">
                <x-lucide-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400 pointer-events-none" aria-hidden="true" />
                <input type="text"
                       x-ref="globalSearch"
                       x-model="globalSearch"
                       @input.debounce.300ms="applyFilters()"
                       @keydown.escape="clearAllFilters()"
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-surface-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-surface-400"
                       placeholder="Buscar en toda la tabla..."
                       aria-label="Búsqueda global">
<template x-if="globalSearch">
                    <button type="button"
                            @click="clearAllFilters()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-surface-400 hover:text-surface-600"
                            aria-label="Limpiar búsqueda">
                        <x-lucide-icon name="x" class="w-4 h-4" aria-hidden="true" />
                    </button>
                </template>
            </div>
        @endif

        <div class="relative" x-data="{ open: false }">
            <button type="button"
                    @click="open = !open"
                    @keydown.escape="open = false"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-surface-700 rounded-lg border border-surface-300 bg-white hover:bg-surface-50 focus:outline-none focus:ring-2 focus:ring-brand-500"
                    aria-label="Columnas visibles"
                    aria-expanded="false"
                    aria-haspopup="listbox">
                <x-lucide-icon name="columns" class="w-4 h-4" aria-hidden="true" />
                <span class="hidden sm:inline">Columnas</span>
                <x-lucide-icon name="chevron-down" class="w-4 h-4" aria-hidden="true" />
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-fast"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="open = false"
                 class="absolute right-0 z-50 mt-2 w-56 rounded-xl shadow-lg ring-1 ring-surface-200 bg-white py-1"
                 style="display: none;"
                 role="listbox"
                 aria-label="Seleccionar columnas visibles">
                <div class="max-h-64 overflow-y-auto">
                    <template x-for="col in columns" :key="col.key">
                        <label class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-surface-50 cursor-pointer"
                               :class="{ 'font-medium': isColumnVisible(col.key) }"
                               role="option"
                               :aria-selected="isColumnVisible(col.key)">
                            <input type="checkbox"
                                   :checked="isColumnVisible(col.key)"
                                   @change="toggleColumn(col.key)"
                                   class="w-4 h-4 rounded border-surface-300 text-brand-600 focus:ring-2 focus:ring-brand-500"
                                   :aria-label="col.label">
                            <span x-text="col.label"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4 text-sm text-surface-600 hidden sm:flex">
        <span x-show="!selectable || selectedCount === 0" x-text="`${pagination.from} - ${pagination.to} de ${pagination.total}`"></span>
        <span x-show="selectable && selectedCount > 0" class="font-medium text-brand-700" x-text="`${selectedCount} seleccionado${selectedCount > 1 ? 's' : ''}`"></span>
    </div>
</div>

<div x-show="filterable" x-transition class="border-b border-surface-200 bg-surface-50">
    <div class="px-4 py-2 overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr role="row">
                    @if ($selectable)
                        <th scope="col" class="w-12 px-4 py-2"></th>
                    @endif
                    @foreach ($columns as $col)
                        @if (isset($col['visible']) && !$col['visible']) @continue @endif
                        <th scope="col" class="px-4 py-2 {{ $col['class'] }}">
                            @if ($col['searchable'])
                                <input type="text"
                                       x-model="columnFilters.{{ $col['key'] }}"
                                       @input.debounce.300ms="applyFilters()"
                                       @keydown.escape="clearColumnFilter('{{ $col['key'] }}')"
                                       class="w-full px-2 py-1 text-xs rounded-lg border border-surface-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-surface-400"
                                       placeholder="Filtrar {{ strtolower($col['label']) }}..."
                                       aria-label="Filtrar por {{ $col['label'] }}">
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
        </table>
    </div>
</div>
