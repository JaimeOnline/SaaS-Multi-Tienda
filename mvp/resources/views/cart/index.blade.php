<x-guest-layout>
    <div class="min-h-screen bg-gray-100 py-8">
        <main class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <header class="mb-8 rounded-2xl bg-white p-4 shadow-sm sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600">Resumen de compra</p>
                        <h1 class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">Carrito</h1>
                    </div>

                    @if ($store)
                        <a href="{{ route('store.public', $store->slug) }}"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto">
                            Volver a la tienda
                        </a>
                    @endif
                </div>

                @if (session('whatsapp_url'))
                    <div class="mt-4 rounded-lg bg-green-50 p-4">
                        <p class="font-semibold text-green-800">
                            Pedido creado correctamente.
                        </p>

                        <a href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener noreferrer"
                            class="mt-3 inline-block rounded-md bg-green-600 px-4 py-2 font-semibold text-white hover:bg-green-700">
                            Enviar pedido por WhatsApp
                        </a>
                    </div>
                @endif
            </header>

            <section class="rounded-lg bg-white p-6 shadow-sm">
                @forelse ($cart['items'] as $item)
                    <article id="cart-item-{{ $item['product_id'] }}" data-cart-item="{{ $item['product_id'] }}"
                        data-unit-price="{{ $item['price'] }}" class="border-b py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="font-semibold text-gray-900">{{ $item['name'] }}</h2>
                                <p class="text-sm text-gray-600">
                                    {{ number_format((float) $item['price'], 2) }} por unidad
                                </p>
                            </div>

                            <p data-item-subtotal class="font-semibold text-gray-900">
                                {{ number_format((float) $item['price'] * $item['quantity'], 2) }}
                            </p>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <form method="POST" action="{{ route('cart.update', $item['product_id']) }}"
                                class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label for="quantity-{{ $item['product_id'] }}" class="text-sm text-gray-600">
                                    Cantidad
                                </label>
                                <input id="quantity-{{ $item['product_id'] }}" name="quantity" type="number"
                                    min="1" value="{{ $item['quantity'] }}" data-cart-quantity-input
                                    class="w-20 rounded-md border-gray-300 shadow-sm">

                                <span data-update-status class="text-sm text-gray-500" aria-live="polite"></span>
                            </form>

                            <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}"
                                data-cart-remove>
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div
                        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2 2.5A1 1 0 0 0 6 17h11m-8 4h.01M17 21h.01" />
                            </svg>
                        </div>
                        <h2 class="mt-4 text-lg font-bold text-slate-900">Tu carrito está vacío</h2>
                        <p class="mt-2 max-w-sm text-sm leading-6 text-slate-600">
                            Explora la tienda y agrega los productos que quieras solicitar.
                        </p>
                        @if ($store)
                            <a href="{{ route('store.public', $store->slug) }}"
                                class="mt-5 inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Explorar productos
                            </a>
                        @endif
                    </div>
                @endforelse

                <div class="mt-6 text-right">
                    <p class="text-xl font-bold text-gray-900">
                        Total: <span id="cart-total">{{ number_format($total, 2) }}</span>
                    </p>
                </div>

                @if ($cart['items'])
                    <form method="POST" action="{{ route('orders.store') }}" data-order-form
                        class="mt-8 space-y-4 border-t pt-6">
                        @csrf

                        <h2 class="text-xl font-bold text-gray-900">
                            Datos del cliente
                        </h2>

                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">
                                Nombre
                            </label>
                            <input id="customer_name" name="customer_name" type="text"
                                value="{{ old('customer_name') }}" required maxlength="255"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('customer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700">
                                Teléfono
                            </label>
                            <input id="customer_phone" name="customer_phone" type="tel"
                                value="{{ old('customer_phone') }}" required maxlength="50"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('customer_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_notes" class="block text-sm font-medium text-gray-700">
                                Notas opcionales
                            </label>
                            <textarea id="customer_notes" name="customer_notes" rows="3" maxlength="1000"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('customer_notes') }}</textarea>
                            @error('customer_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full rounded-md bg-indigo-600 px-4 py-3 font-semibold text-white hover:bg-indigo-700">
                            Confirmar pedido
                        </button>
                    </form>
                @endif
            </section>
        </main>
    </div>
    <script>
        document.querySelectorAll('[data-cart-remove]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const item = form.closest('[data-cart-item]');
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                if (!response.ok) {
                    window.location.reload();
                    return;
                }

                item.remove();

                const total = document.querySelector('#cart-total');
                const remainingItems = document.querySelectorAll('[data-item-subtotal]');
                const newTotal = [...remainingItems]
                    .reduce((sum, element) => sum + Number(element.textContent.replace(',', '.')), 0);

                total.textContent = newTotal.toFixed(2);

                if (remainingItems.length === 0) {
                    document.querySelector('[data-order-form]')?.remove();
                }
            });
        });

        document.querySelectorAll('[data-cart-item]').forEach((item) => {
            const form = item.querySelector('form[action*="/carrito/productos/"]');
            const input = item.querySelector('[data-cart-quantity-input]');
            const subtotal = item.querySelector('[data-item-subtotal]');
            const status = item.querySelector('[data-update-status]');
            const total = document.querySelector('#cart-total');
            let timeout;

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    input.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }
            });

            input.addEventListener('change', async () => {
                const quantity = Number(input.value);

                if (!Number.isInteger(quantity) || quantity < 1) {
                    input.value = 1;
                    return;
                }

                clearTimeout(timeout);
                status.textContent = 'Guardando...';

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                if (!response.ok) {
                    status.textContent = 'No se pudo actualizar';
                    return;
                }

                const unitPrice = Number(item.dataset.unitPrice);
                const newSubtotal = unitPrice * quantity;

                subtotal.textContent = newSubtotal.toFixed(2);

                const currentTotal = [...document.querySelectorAll('[data-item-subtotal]')]
                    .reduce((sum, element) => sum + Number(element.textContent.replace(',', '.')), 0);

                total.textContent = currentTotal.toFixed(2);
                status.textContent = 'Guardado';

                timeout = setTimeout(() => {
                    status.textContent = '';
                }, 1500);
            });
        });
    </script>
</x-guest-layout>
