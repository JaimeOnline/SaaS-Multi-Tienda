<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-brand-600">Organización del catálogo</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Categorías
                </h2>
            </div>

            <a href="{{ route('dashboard') }}"
                class="inline-flex w-fit items-center rounded-xl bg-surface-100 px-3 py-2 text-sm font-semibold text-surface-700 transition hover:bg-surface-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                Volver al dashboard
            </a>
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

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">
                    Nueva categoría
                </h3>

                <form method="POST" action="{{ route('categories.store') }}"
                    class="mt-4 flex flex-col gap-3 sm:flex-row">
                    @csrf

                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ej. Postres" required
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Crear
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Mis categorías
                    </h3>

                    @forelse ($categories as $category)
                        <div
                            class="mt-4 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ $category->name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $category->slug }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                                <form method="POST" action="{{ route('categories.update', $category) }}"
                                    class="flex flex-col gap-2 sm:flex-row">
                                    @csrf
                                    @method('PATCH')

                                    <input type="text" name="name" value="{{ $category->name }}" required
                                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                                    <button type="submit"
                                        class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Guardar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('categories.destroy', $category) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="mt-4 text-sm text-gray-600">
                            Todavía no tienes categorías creadas.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
