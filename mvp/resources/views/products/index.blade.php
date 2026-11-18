<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600">Gestión del catálogo</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Productos
                </h2>
            </div>

            <a href="{{ route('dashboard') }}"
                class="inline-flex w-fit items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Volver al dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    La operación se realizó correctamente.
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">
                    Nuevo producto
                </h3>

                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data"
                    class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf

                    <input name="name" value="{{ old('name') }}" required placeholder="Nombre del producto"
                        class="rounded-md border-gray-300 shadow-sm">

                    <input name="price" value="{{ old('price') }}" type="number" min="0" step="0.01"
                        required placeholder="Precio" class="rounded-md border-gray-300 shadow-sm">

                    <select name="category_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Sin categoría</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <textarea name="description" placeholder="Descripción" class="rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>

                    <label class="text-sm text-gray-700">
                        Imagen principal del producto
                        <input type="file" name="image_path" accept="image/jpeg,image/png,image/webp"
                            class="mt-1 block w-full text-sm">
                    </label>

                    <label class="text-sm text-gray-700">
                        Imágenes adicionales
                        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                            class="mt-1 block w-full text-sm">
                        <span class="mt-1 block text-xs text-gray-500">
                            Puedes seleccionar hasta 5 imágenes adicionales.
                        </span>
                    </label>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="is_available" value="0">
                        <input type="checkbox" name="is_available" value="1" checked>
                        Disponible
                    </label>

                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Crear producto
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('products.index') }}" class="grid gap-3 md:grid-cols-3">
                    <input name="search" value="{{ request('search') }}" placeholder="Buscar por nombre"
                        class="rounded-md border-gray-300 shadow-sm">

                    <select name="category_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Todas las categorías</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-900">
                        Filtrar
                    </button>
                </form>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">
                    Mis productos
                </h3>

                <div class="mt-4 space-y-4">
                    @forelse ($products as $product)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                            @if ($product->image_path)
                                <img src="{{ Storage::disk('public')->url($product->image_path) }}"
                                    alt="{{ $product->name }}"
                                    class="mb-3 h-28 w-28 rounded-xl border border-slate-200 object-cover shadow-sm">
                            @endif

                            <form method="POST" action="{{ route('products.update', $product) }}"
                                enctype="multipart/form-data"
                                class="grid gap-4 rounded-xl bg-white p-4 shadow-sm md:grid-cols-6 md:items-center">
                                @csrf
                                @method('PATCH')

                                <input name="name" value="{{ $product->name }}" required
                                    class="rounded-md border-gray-300 shadow-sm">

                                <input name="price" value="{{ $product->price }}" type="number" min="0"
                                    step="0.01" required class="rounded-md border-gray-300 shadow-sm">

                                <select name="category_id" class="rounded-md border-gray-300 shadow-sm">
                                    <option value="">Sin categoría</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($product->category_id == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input name="description" value="{{ $product->description }}" placeholder="Descripción"
                                    class="rounded-md border-gray-300 shadow-sm">

                                <label class="min-w-0 text-sm text-gray-700 md:col-span-2">
                                    Imagen principal
                                    <input type="file" name="image_path" accept="image/jpeg,image/png,image/webp"
                                        class="mt-1 block w-full min-w-0 text-sm">
                                </label>

                                <label class="text-sm text-gray-700 min-w-0 md:col-span-2">
                                    Imágenes adicionales
                                    <input type="file" name="images[]" multiple
                                        accept="image/jpeg,image/png,image/webp"
                                        class="mt-1 block w-full min-w-0 text-sm">
                                    <span class="mt-1 block text-xs text-gray-500">
                                        Puedes seleccionar hasta 5 imágenes adicionales.
                                    </span>
                                </label>

                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="hidden" name="is_available" value="0">
                                    <input type="checkbox" name="is_available" value="1"
                                        @checked($product->is_available)>
                                    Disponible
                                </label>

                                <button type="submit"
                                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                    Guardar
                                </button>
                            </form>

                            <form method="POST" action="{{ route('products.destroy', $product) }}" class="mt-3">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600">
                            Todavía no tienes productos creados.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
