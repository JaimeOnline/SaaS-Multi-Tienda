<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-brand-600">Organización del catálogo</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Categorías
                </h2>
            </div>

            <x-ui.button variant="primary" size="sm"
                x-on:click="$dispatch('open-modal', { detail: 'category-create' })">
                <x-lucide-icon name="plus" class="w-4 h-4" />
                Nueva categoría
            </x-ui.button>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-ui.datatable name="categories-table" :columns="[
                ['key' => 'name', 'label' => 'Nombre', 'sortable' => true, 'searchable' => true, 'mobilePriority' => 1],
                ['key' => 'slug', 'label' => 'Slug', 'sortable' => true, 'searchable' => false, 'mobilePriority' => 2],
                [
                    'key' => 'products_count',
                    'label' => 'Productos',
                    'sortable' => true,
                    'searchable' => false,
                    'align' => 'center',
                    'mobilePriority' => 3,
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
            ]" :data="$categories->toArray()" :pagination="$categories->toArray()"
                :sortable="true" :filterable="true" :selectable="true" :bulk-actions="['delete']"
                empty-state="Todavía no tienes categorías. Crea tu primera." empty-action="Crear categoría"
                empty-action-url="{{ route('categories.index') }}" />
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('categoriesTable', {
                    bulkDeleteUrl: '{{ route('categories.bulk-delete') }}',
                });
            });
        </script>
    @endpush

    @include('categories.modals.create')
</x-app-layout>
