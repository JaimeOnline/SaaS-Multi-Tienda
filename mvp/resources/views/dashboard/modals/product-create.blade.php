<x-ui.modal name="product-create" title="Nuevo producto" size="lg" :footer="false">
    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <x-ui.input label="Nombre" name="name" required autofocus />
        <x-ui.input label="Precio" name="price" type="number" step="0.01" min="0" required />
        <x-ui.input label="Descripción" name="description" type="textarea" rows="3" />

        <div class="flex justify-end gap-3 pt-4 border-t border-surface-200">
            <x-ui.button variant="ghost" @click="$dispatch('close-modal', { detail: 'product-create' })">
                Cancelar
            </x-ui.button>
            <x-ui.button variant="primary" type="submit" :loading="false">
                Crear producto
            </x-ui.button>
        </div>
    </form>
</x-ui.modal>