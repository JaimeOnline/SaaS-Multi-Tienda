@props([
    'columns' => [],
    'data' => [],
    'pagination' => [],
    'sortable' => true,
    'filterable' => true,
    'selectable' => false,
    'bulkActions' => [],
    'emptyState' => 'No hay datos disponibles.',
    'emptyAction' => null,
    'emptyActionUrl' => null,
    'name' => 'datatable',
    'mobilePriority' => 3,
    'loading' => false,
])

@php
    $componentId = $name . '-' . uniqid();

    $processedColumns = collect($columns)
        ->map(function ($col, $index) {
            return array_merge(
                [
                    'key' => $col['key'] ?? "col_{$index}",
                    'label' => $col['label'] ?? ucfirst($col['key'] ?? "Col {$index}"),
                    'sortable' => $col['sortable'] ?? false,
                    'searchable' => $col['searchable'] ?? false,
                    'format' => $col['format'] ?? null,
                    'render' => $col['render'] ?? null,
                    'width' => $col['width'] ?? null,
                    'align' => $col['align'] ?? 'left',
                    'mobilePriority' => $col['mobilePriority'] ?? 999,
                    'class' => $col['class'] ?? '',
                ],
                $col,
            );
        })
        ->values()
        ->all();

    $pagination = array_merge(
        [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 25,
            'total' => 0,
            'from' => 0,
            'to' => 0,
            'path' => request()->url(),
        ],
        $pagination,
    );

    $availableBulkActions = [
        'delete' => [
            'label' => 'Eliminar seleccionados',
            'icon' => 'trash-2',
            'variant' => 'danger',
            'confirm' => '¿Eliminar los elementos seleccionados?',
        ],
        'toggle_availability' => [
            'label' => 'Cambiar disponibilidad',
            'icon' => 'eye-off',
            'variant' => 'secondary',
            'confirm' => '¿Cambiar la disponibilidad de los elementos seleccionados?',
        ],
        'export' => ['label' => 'Exportar CSV', 'icon' => 'download', 'variant' => 'outline', 'confirm' => null],
    ];

    $bulkActionsConfig = collect($bulkActions)
        ->map(fn($key) => $availableBulkActions[$key] ?? null)
        ->filter()
        ->values()
        ->all();
@endphp

<div x-data="datatableComponent('{{ $componentId }}', {{ json_encode($processedColumns) }}, {{ json_encode($pagination) }}, {{ json_encode($bulkActionsConfig) }}, {{ json_encode($selectable) }}, {{ json_encode($mobilePriority) }})" x-init="const stored = localStorage.getItem('datatable-columns-' + '{{ $name }}');
if (stored) { try { this.visibleColumns = JSON.parse(stored); } catch (e) {} }
this.$watch('visibleColumns', val => {
    localStorage.setItem('datatable-columns-' + '{{ $name }}', JSON.stringify(val));
});" class="w-full" :class="{ 'loading': loading }"
    id="{{ $componentId }}">
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full border-collapse" role="grid" aria-label="Tabla de datos">
            <thead class="bg-surface-50 border-b border-surface-200">
                <tr role="row">
                    @if ($selectable)
                        <th scope="col" class="w-12 px-4 py-3 text-center">
                            <input type="checkbox"
                                class="w-4 h-4 rounded border-surface-300 text-brand-600 focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 cursor-pointer"
                                x-model="selectAll" @change="toggleSelectAll($event.target.checked)"
                                aria-label="Seleccionar todas las filas de la página">
                        </th>
                    @endif

                    @foreach ($processedColumns as $col)
                        @if (isset($col['visible']) && !$col['visible'])
                            @continue
                        @endif
                        <th scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider whitespace-nowrap {{ $col['class'] }}"
                            :class="{
                                'cursor-pointer select-none': @js($col['sortable'] && $sortable),
                                'text-center': @js($col['align'] === 'center'),
                                'text-right': @js($col['align'] === 'right'),
                            }"
                            :style="'width: ' + (@js($col['width']) || 'auto')"
                            @if ($col['sortable'] && $sortable) @click="handleSort('{{ $col['key'] }}')"
                                @keydown.enter.prevent="handleSort('{{ $col['key'] }}')"
                                @keydown.space.prevent="handleSort('{{ $col['key'] }}')"
                                tabindex="0"
                                role="columnheader"
                                :aria-sort="sortBy === '{{ $col['key'] }}' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"
                            @else
                                role="columnheader" @endif>
                            <div class="flex items-center gap-1.5">
                                {{ $col['label'] }}
                                @if ($col['sortable'] && $sortable)
                                    <span x-show="sortBy !== '{{ $col['key'] }}'" class="text-surface-300"
                                        aria-hidden="true">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 4h13M3 8h13M3 12h13M3 16h13"></path>
                                        </svg>
                                    </span>
                                    <span x-show="sortBy === '{{ $col['key'] }}' && sortDir === 'asc'"
                                        class="text-brand-600" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    </span>
                                    <span x-show="sortBy === '{{ $col['key'] }}' && sortDir === 'desc'"
                                        class="text-brand-600" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 9l7 7 7-7"></path>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>

                @if ($filterable)
                    <tr role="row" class="bg-surface-50 border-b border-surface-200">
                        @if ($selectable)
                            <th scope="col" class="px-4 py-2"></th>
                        @endif
                        @foreach ($processedColumns as $col)
                            @if (isset($col['visible']) && !$col['visible'])
                                @continue
                            @endif
                            <th scope="col" class="px-4 py-2 {{ $col['class'] }}">
                                @if ($col['searchable'])
                                    <input type="text"
                                        class="w-full px-2 py-1.5 text-xs rounded-lg border border-surface-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-surface-400"
                                        placeholder="Filtrar {{ strtolower($col['label']) }}..."
                                        x-model="columnFilters.{{ $col['key'] }}"
                                        @input.debounce.300ms="applyFilters()"
                                        @keydown.escape="clearColumnFilter('{{ $col['key'] }}')"
                                        aria-label="Filtrar por {{ $col['label'] }}">
                                @endif
                            </th>
                        @endforeach
                    </tr>
                @endif
            </thead>

            <tbody x-ref="tbody" role="rowgroup">
                @if ($loading)
                    @include('components.ui.datatable-skeleton', [
                        'columns' => $processedColumns,
                        'count' => 5,
                        'selectable' => $selectable,
                    ])
                @elseif (empty($data))
                    <tr>
                        <td colspan="{{ count($processedColumns) + ($selectable ? 1 : 0) }}"
                            class="px-4 py-12 text-center text-surface-500" role="status" aria-live="polite">
                            <x-lucide-icon name="inbox" class="mx-auto h-12 w-12 text-surface-300 mb-3"
                                aria-hidden="true" />
                            <p class="text-surface-600">{{ $emptyState }}</p>
                            @if ($emptyAction && $emptyActionUrl)
                                <x-ui.button variant="primary" size="sm" class="mt-3"
                                    href="{{ $emptyActionUrl }}">
                                    {{ $emptyAction }}
                                </x-ui.button>
                            @endif
                        </td>
                    </tr>
                @else
                    @foreach ($data as $rowIndex => $row)
                        @include('components.ui.datatable-row', [
                            'row' => $row,
                            'rowIndex' => $rowIndex,
                            'columns' => $processedColumns,
                            'selectable' => $selectable,
                            'componentId' => $componentId,
                            'isMobile' => false,
                        ])
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="lg:hidden space-y-3 p-4" role="list" aria-label="Lista de elementos">
        @if ($loading)
            @for ($i = 0; $i < 5; $i++)
                @include('components.ui.datatable-skeleton', [
                    'columns' => $processedColumns,
                    'count' => 1,
                    'selectable' => $selectable,
                    'isMobile' => true,
                ])
            @endfor
        @elseif (empty($data))
            <div class="text-center py-12 text-surface-500" role="status" aria-live="polite">
                <x-lucide-icon name="inbox" class="mx-auto h-12 w-12 text-surface-300 mb-3" aria-hidden="true" />
                <p class="text-surface-600">{{ $emptyState }}</p>
                @if ($emptyAction && $emptyActionUrl)
                    <x-ui.button variant="primary" size="sm" class="mt-3" href="{{ $emptyActionUrl }}">
                        {{ $emptyAction }}
                    </x-ui.button>
                @endif
            </div>
        @else
            @foreach ($data as $rowIndex => $row)
                @include('components.ui.datatable-row', [
                    'row' => $row,
                    'rowIndex' => $rowIndex,
                    'columns' => $processedColumns,
                    'selectable' => $selectable,
                    'componentId' => $componentId,
                    'isMobile' => true,
                ])
            @endforeach
        @endif
    </div>

    @if ($pagination['last_page'] > 1)
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-4 py-4 border-t border-surface-200 bg-surface-50"
            role="navigation" aria-label="Paginación">
            <div class="text-sm text-surface-600">
                Mostrando <span class="font-medium">{{ $pagination['from'] }}</span> a
                <span class="font-medium">{{ $pagination['to'] }}</span> de
                <span class="font-medium">{{ $pagination['total'] }}</span> resultados
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                    class="px-3 py-1.5 text-sm rounded-lg border border-surface-300 bg-white hover:bg-surface-50 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-brand-500"
                    aria-label="Página anterior"
                    :aria-disabled="{{ $pagination['current_page'] <= 1 ? 'true' : 'false' }}">
                    <x-lucide-icon name="chevron-left" class="w-4 h-4" aria-hidden="true" />
                </button>

                <template x-for="page in getVisiblePages()" :key="page">
                    @if (is_numeric(page))
                        <button type="button" @click="goToPage(page)"
                            :class="['px-3 py-1.5 text-sm rounded-lg transition-colors', currentPage === page ?
                                'bg-brand-600 text-white' : 'border border-surface-300 bg-white hover:bg-surface-50'
                            ]"
                            :aria-current="currentPage === page ? 'page' : 'false'" :aria-label="'Página ' + page">
                            <span x-text="page"></span>
                        </button>
                    @else
                        <span class="px-2 text-sm text-surface-400" aria-hidden="true">...</span>
                    @endif
                </template>

                <button type="button" @click="goToPage(currentPage + 1)" :disabled="currentPage >= lastPage"
                    class="px-3 py-1.5 text-sm rounded-lg border border-surface-300 bg-white hover:bg-surface-50 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-brand-500"
                    aria-label="Página siguiente"
                    :aria-disabled="{{ $pagination['current_page'] >= $pagination['last_page'] ? 'true' : 'false' }}">
                    <x-lucide-icon name="chevron-right" class="w-4 h-4" aria-hidden="true" />
                </button>
            </div>
        </div>
    @endif

    @if ($selectable && !empty($bulkActionsConfig))
        <div x-show="selectedCount > 0" x-transition:enter="transition ease-out duration-fast"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-fast" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-4 right-4 z-50 lg:static lg:relative lg:bottom-auto lg:right-auto lg:mt-4 lg:mb-0"
            role="toolbar" aria-label="Acciones en lote">
            <div
                class="flex items-center gap-2 bg-white rounded-xl shadow-xl border border-surface-200 p-3 lg:p-2 lg:shadow-none lg:border-0 lg:bg-transparent">
                <span class="text-sm font-medium text-surface-700 hidden lg:inline">
                    <span x-text="selectedCount"></span>
                    <span x-text="selectedCount > 1 ? ' seleccionados' : ' seleccionado'"></span>
                </span>
                <div class="flex items-center gap-1">
                    @foreach ($bulkActionsConfig as $action)
                        <button type="button"
                            @click="confirmBulkAction('{{ $action['label'] }}', '{{ $action['confirm'] ?? '' }}', '{{ $action['key'] ?? $action['label'] }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2
                                    {{ $action['variant'] === 'danger' ? 'text-semantic-error hover:bg-semantic-error-light' : ($action['variant'] === 'outline' ? 'border border-surface-300 text-surface-700 hover:bg-surface-50' : 'bg-surface-100 text-surface-700 hover:bg-surface-200') }}">
                            <x-lucide-icon name="{{ $action['icon'] }}" class="w-4 h-4" aria-hidden="true" />
                            <span class="hidden sm:inline">{{ $action['label'] }}</span>
                        </button>
                    @endforeach
                    <button type="button" @click="clearSelection()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-surface-500 rounded-lg hover:bg-surface-100 transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                        aria-label="Limpiar selección">
                        <x-lucide-icon name="x" class="w-4 h-4" aria-hidden="true" />
                        <span class="hidden sm:inline">Limpiar</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <x-ui.modal name="{{ $componentId }}-bulk-confirm" size="sm" :title="'Confirmar acción'" :show="false">
        <p class="text-surface-600" x-text="bulkConfirmMessage"></p>
        <x-slot name="footer">
            <x-ui.button variant="ghost" @click="closeBulkConfirm()">Cancelar</x-ui.button>
            <x-ui.button variant="primary" @click="executeBulkAction()" x-ref="bulkConfirmBtn">
                <span x-text="bulkConfirmActionLabel"></span>
            </x-ui.button>
        </x-slot>
    </x-ui.modal>

</div>

<script>
    function datatableComponent(componentId, columns, pagination, bulkActions, selectable, mobilePriority) {
        return {
            componentId,
            columns,
            data: @js($data),
            loading: @js($loading),
            selectable,
            mobilePriority,
            bulkActions,
            currentPage: pagination.current_page,
            lastPage: pagination.last_page,
            perPage: pagination.per_page,
            total: pagination.total,
            sortBy: '',
            sortDir: 'asc',
            sortable: @js($sortable),
            globalSearch: '',
            columnFilters: {},
            selectedRows: [],
            selectAll: false,
            visibleColumns: columns.map(c => c.key),
            bulkConfirmMessage: '',
            bulkConfirmActionLabel: '',
            bulkConfirmVariant: 'danger',
            bulkConfirmCallback: null,

            init() {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('sort_by')) {
                    this.sortBy = urlParams.get('sort_by');
                    this.sortDir = urlParams.get('sort_dir') || 'asc';
                }
                if (urlParams.has('search')) {
                    this.globalSearch = urlParams.get('search');
                }
                if (urlParams.has('page')) {
                    this.currentPage = parseInt(urlParams.get('page'), 10);
                }
                for (const [key, value] of urlParams.entries()) {
                    if (key.startsWith('filter_')) this.columnFilters[key.replace('filter_', '')] = value;
                }
            },

            get selectedCount() {
                return this.selectedRows.length;
            },

            getVisiblePages() {
                const pages = [];
                const current = this.currentPage;
                const last = this.lastPage;
                if (last <= 7) {
                    for (let i = 1; i <= last; i++) pages.push(i);
                } else {
                    pages.push(1);
                    if (current > 3) pages.push('...');
                    for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) pages.push(i);
                    if (current < last - 2) pages.push('...');
                    pages.push(last);
                }
                return pages;
            },

            handleSort(key) {
                if (!this.sortable) return;
                if (this.sortBy === key) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                else {
                    this.sortBy = key;
                    this.sortDir = 'asc';
                }
                this.currentPage = 1;
                this.fetchData();
            },

            applyFilters() {
                this.currentPage = 1;
                this.fetchData();
            },
            clearColumnFilter(key) {
                delete this.columnFilters[key];
                this.applyFilters();
            },
            clearAllFilters() {
                this.globalSearch = '';
                this.columnFilters = {};
                this.currentPage = 1;
                this.fetchData();
            },

            goToPage(page) {
                if (page < 1 || page > this.lastPage || page === this.currentPage) return;
                this.currentPage = page;
                this.fetchData();
            },

            toggleSelectAll(checked) {
                this.selectedRows = checked ? this.data.map((_, i) => i) : [];
                this.selectAll = checked;
            },
            toggleRowSelection(index) {
                const idx = this.selectedRows.indexOf(index);
                if (idx === -1) this.selectedRows.push(index);
                else this.selectedRows.splice(idx, 1);
                this.selectAll = this.selectedRows.length === this.data.length && this.data.length > 0;
            },
            clearSelection() {
                this.selectedRows = [];
                this.selectAll = false;
            },

            handleKeydown(event) {
                if (event.key === '/' && !event.ctrlKey && !event.metaKey && !['INPUT', 'TEXTAREA', 'SELECT'].includes(
                        event.target.tagName)) {
                    event.preventDefault();
                    this.$refs.globalSearch?.focus();
                }
                if (event.key === 'Escape' && this.selectedCount > 0) this.clearSelection();
                if (event.key === 'a' && (event.ctrlKey || event.metaKey) && this.selectable) {
                    event.preventDefault();
                    this.toggleSelectAll(true);
                }
            },

            async fetchData() {
                this.loading = true;
                const params = new URLSearchParams();
                params.set('page', this.currentPage);
                if (this.sortBy) {
                    params.set('sort_by', this.sortBy);
                    params.set('sort_dir', this.sortDir);
                }
                if (this.globalSearch) params.set('search', this.globalSearch);
                for (const [key, value] of Object.entries(this.columnFilters)) {
                    if (value) params.set('filter_' + key, value);
                }
                const currentParams = new URLSearchParams(window.location.search);
                for (const [key, value] of currentParams.entries()) {
                    if (!['page', 'sort_by', 'sort_dir', 'search'].includes(key) && !key.startsWith('filter_'))
                        params.set(key, value);
                }
                const url = '{{ request()->url() }}?' + params.toString();
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error('Error en la respuesta');
                    const result = await response.json();
                    this.data = result.data;
                    this.currentPage = result.current_page;
                    this.lastPage = result.last_page;
                    this.perPage = result.per_page;
                    this.total = result.total;
                    window.history.replaceState({}, '', url);
                    this.selectedRows = [];
                    this.selectAll = false;
                    this.announceToScreenReader(`Página ${this.currentPage}, ${this.total} resultados`);
                } catch (error) {
                    console.error('Error fetching data:', error);
                    window.location.href = url;
                } finally {
                    this.loading = false;
                }
            },

            toggleColumn(key) {
                const idx = this.visibleColumns.indexOf(key);
                if (idx === -1) this.visibleColumns.push(key);
                else this.visibleColumns.splice(idx, 1);
            },
            isColumnVisible(key) {
                return this.visibleColumns.includes(key);
            },

            confirmBulkAction(label, confirmMessage, actionKey) {
                this.bulkConfirmMessage = confirmMessage ||
                    `¿Ejecutar "${label}" en ${this.selectedCount} elemento${this.selectedCount > 1 ? 's' : ''}?`;
                this.bulkConfirmActionLabel = label;
                this.bulkConfirmVariant = this.bulkActions.find(a => a.label === label)?.variant || 'danger';
                this.bulkConfirmCallback = actionKey;
                this.$dispatch('open-modal', {
                    detail: `${this.componentId}-bulk-confirm`
                });
            },
            closeBulkConfirm() {
                this.$dispatch('close-modal', {
                    detail: `${this.componentId}-bulk-confirm`
                });
                this.bulkConfirmCallback = null;
            },

            async executeBulkAction() {
                const action = this.bulkConfirmCallback;
                const ids = this.selectedRows.map(i => this.data[i]?.id).filter(Boolean);
                if (!ids.length) {
                    this.closeBulkConfirm();
                    return;
                }
                this.loading = true;
                try {
                    let url, method, body;
                    switch (action) {
                        case 'delete':
                        case 'Eliminar seleccionados':
                            url = this.bulkDeleteUrl || '';
                            method = 'DELETE';
                            body = {
                                ids
                            };
                            break;
                        case 'toggle_availability':
                        case 'Cambiar disponibilidad':
                            url = this.bulkToggleUrl || '';
                            method = 'PATCH';
                            body = {
                                ids
                            };
                            break;
                        default:
                            throw new Error('Acción no implementada');
                    }
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(body),
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error('Error en la acción');
                    const result = await response.json();
                    window.toast?.success(result.message || 'Acción completada');
                    await this.fetchData();
                } catch (error) {
                    console.error('Bulk action error:', error);
                    window.toast?.error('Error al ejecutar la acción');
                } finally {
                    this.loading = false;
                    this.closeBulkConfirm();
                }
            },

            announceToScreenReader(message) {
                const liveRegion = document.getElementById(`${this.componentId}-live-region`);
                if (liveRegion) liveRegion.textContent = message;
            },
        };
    }
    window.datatableComponent = datatableComponent;
</script>

<div id="{{ $componentId }}-live-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>
