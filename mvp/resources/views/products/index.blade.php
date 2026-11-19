<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-brand-600">Gestión del catálogo</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Productos
                </h2>
            </div>

            <x-ui.button variant="primary" size="sm"
                x-on:click="$dispatch('open-modal', { detail: 'product-create' })">
                <x-lucide-icon name="plus" class="w-4 h-4" />
                Nuevo producto
            </x-ui.button>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-ui.datatable name="products-table" :columns="[
                [
                    'key' => 'image_path',
                    'label' => 'Imagen',
                    'sortable' => false,
                    'searchable' => false,
                    'width' => '60px',
                    'align' => 'center',
                    'mobilePriority' => 1,
                ],
                ['key' => 'name', 'label' => 'Nombre', 'sortable' => true, 'searchable' => true, 'mobilePriority' => 1],
                [
                    'key' => 'category.name',
                    'label' => 'Categoría',
                    'sortable' => true,
                    'searchable' => true,
                    'mobilePriority' => 2,
                ],
                [
                    'key' => 'price',
                    'label' => 'Precio',
                    'sortable' => true,
                    'format' => 'currency',
                    'align' => 'right',
                    'mobilePriority' => 2,
                ],
                [
                    'key' => 'is_available',
                    'label' => 'Estado',
                    'sortable' => true,
                    'format' => 'badge',
                    'align' => 'center',
                    'mobilePriority' => 3,
                ],
                [
                    'key' => 'actions',
                    'label' => 'Acciones',
                    'sortable' => false,
                    'searchable' => false,
                    'width' => '140px',
                    'align' => 'center',
                    'mobilePriority' => 1,
                ],
            ]" :data="$products->toArray()" :pagination="$products->toArray()"
                :sortable="true" :filterable="true" :selectable="true" :bulk-actions="['delete', 'toggle_availability']"
                empty-state="Todavía no tienes productos. Crea tu primero." empty-action="Crear producto"
                empty-action-url="{{ route('products.index') }}" />
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('productsTable', {
                    bulkDeleteUrl: '{{ route('products.bulk-delete') }}',
                    bulkToggleUrl: '{{ route('products.bulk-toggle-availability') }}',
                });
            });
        </script>
    @endpush

    @include('dashboard.modals.product-create')
</x-app-layout>
