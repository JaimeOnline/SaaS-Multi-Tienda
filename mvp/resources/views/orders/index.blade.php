<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-600">Gestión de ventas</p>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pedidos
                </h2>
            </div>

            <a href="{{ route('dashboard') }}"
                class="inline-flex w-fit items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Volver al dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if (session('status'))
                    <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                        La operación se realizó correctamente.
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-700">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @forelse ($orders as $order)
                    <article
                        class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm last:mb-0 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Pedido #{{ $order->id }}
                                </h3>

                                <p class="text-sm text-gray-600">
                                    Cliente: {{ $order->customer_name }}
                                </p>

                                <p class="text-sm text-gray-600">
                                    Teléfono: {{ $order->customer_phone }}
                                </p>

                                <p class="text-sm text-gray-600">
                                    Fecha: {{ $order->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            <div class="w-full rounded-xl bg-white p-3 sm:w-auto sm:min-w-56 sm:text-right">
                                <form method="POST" action="{{ route('orders.update', $order) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')

                                    <label for="status-{{ $order->id }}" class="block text-sm text-gray-600">
                                        Estado
                                    </label>

                                    <select id="status-{{ $order->id }}" name="status"
                                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm sm:w-auto">
                                        @foreach ([
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmado',
        'preparing' => 'Preparando',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
    ] as $value => $label)
                                            <option value="{{ $value }}" @selected($order->status === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit"
                                        class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                                        Actualizar estado
                                    </button>
                                </form>

                                <p class="text-lg font-bold text-gray-900">
                                    Total: {{ number_format((float) $order->total, 2) }}
                                </p>
                            </div>
                        </div>

                        @if ($order->customer_notes)
                            <p class="mt-3 text-sm text-gray-700">
                                Notas: {{ $order->customer_notes }}
                            </p>
                        @endif

                        <div class="mt-4 rounded-xl bg-white p-4">
                            <h4 class="font-semibold text-gray-900">
                                Productos
                            </h4>

                            <ul class="mt-2 space-y-2 text-sm text-gray-700">
                                @foreach ($order->items as $item)
                                    <li>
                                        {{ $item->quantity }} x {{ $item->product_name }}
                                        — {{ number_format((float) $item->unit_price, 2) }}
                                        (Subtotal: {{ number_format((float) $item->subtotal, 2) }})
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-gray-600">
                        No hay pedidos registrados.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
