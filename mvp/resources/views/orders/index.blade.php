<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-brand-600">Gestión de ventas</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pedidos
                </h2>
            </div>

            <a href="{{ route('dashboard') }}"
                class="inline-flex w-fit items-center rounded-xl bg-surface-100 px-3 py-2 text-sm font-semibold text-surface-700 transition hover:bg-surface-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                Volver al dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-ui.datatable name="orders-table" :columns="[
                ['key' => 'id', 'label' => 'Pedido', 'sortable' => true, 'searchable' => true, 'mobilePriority' => 1],
                [
                    'key' => 'customer_name',
                    'label' => 'Cliente',
                    'sortable' => true,
                    'searchable' => true,
                    'mobilePriority' => 1,
                ],
                [
                    'key' => 'customer_phone',
                    'label' => 'Teléfono',
                    'sortable' => false,
                    'searchable' => true,
                    'mobilePriority' => 2,
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Fecha',
                    'sortable' => true,
                    'format' => 'date',
                    'align' => 'center',
                    'mobilePriority' => 2,
                ],
                [
                    'key' => 'status',
                    'label' => 'Estado',
                    'sortable' => true,
                    'format' => 'badge',
                    'align' => 'center',
                    'mobilePriority' => 3,
                ],
                [
                    'key' => 'total',
                    'label' => 'Total',
                    'sortable' => true,
                    'format' => 'currency',
                    'align' => 'right',
                    'mobilePriority' => 2,
                ],
                [
                    'key' => 'actions',
                    'label' => 'Acciones',
                    'sortable' => false,
                    'searchable' => false,
                    'width' => '120px',
                    'align' => 'center',
                    'mobilePriority' => 1,
                ],
            ]" :data="$orders->toArray()" :pagination="$orders->toArray()" :sortable="true"
                :filterable="true" :selectable="true" :bulk-actions="['delete', 'toggle_availability']" empty-state="No hay pedidos registrados."
                empty-action="Ver tu tienda"
                empty-action-url="{{ route('store.public', auth()->user()->store->slug) }}" />
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('ordersTable', {
                    bulkDeleteUrl: '{{ route('orders.bulk-delete') }}',
                    bulkToggleUrl: '{{ route('orders.bulk-toggle-status') }}',
                });
            });
        </script>
    @endpush
</x-app-layout>
