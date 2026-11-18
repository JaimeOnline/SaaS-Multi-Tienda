<x-guest-layout>
    <div class="min-h-screen bg-slate-50 py-6 sm:py-8">
        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <header class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:mb-8 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-emerald-600">Catálogo en línea</p>
                        <h1 class="mt-1 break-words text-2xl font-bold text-gray-900 sm:text-3xl">
                            {{ $store->name }}
                        </h1>
                    </div>

                    <a href="{{ route('cart.index') }}"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto">
                        Ver carrito
                    </a>
                </div>

                @if ($store->delivery_notes)
                    <p class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm leading-6 text-emerald-900">
                        {{ $store->delivery_notes }}
                    </p>
                @endif
            </header>

            <form method="GET" action="{{ route('store.public', $store->slug) }}"
                class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:mb-8">
                <label for="category_id" class="block text-sm font-medium text-gray-700">
                    Filtrar por categoría
                </label>

                <select id="category_id" name="category_id" onchange="this.form.submit()"
                    class="mt-2 w-full rounded-md border-gray-300 shadow-sm sm:max-w-xs">
                    <option value="">Todas las categorías</option>
                    @foreach ($store->categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:gap-7">
                @forelse ($store->products as $product)
                    <article id="product-{{ $product->id }}"
                        class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        @php
                            $galleryImages = collect();

                            if ($product->image_path) {
                                $galleryImages->push([
                                    'url' => Storage::disk('public')->url($product->image_path),
                                    'alt' => $product->name . ' — imagen principal',
                                ]);
                            }

                            foreach ($product->images as $image) {
                                $galleryImages->push([
                                    'url' => Storage::disk('public')->url($image->path),
                                    'alt' => $product->name . ' — imagen adicional ' . $loop->iteration,
                                ]);
                            }
                        @endphp

                        @if ($galleryImages->isNotEmpty())
                            <div class="product-gallery" data-gallery>
                                <div class="relative">
                                    <img src="{{ $galleryImages[0]['url'] }}" alt="{{ $galleryImages[0]['alt'] }}"
                                        data-gallery-main class="h-52 w-full object-cover sm:h-56">

                                    @if ($galleryImages->count() > 1)
                                        <button type="button" data-gallery-prev
                                            aria-label="Imagen anterior de {{ $product->name }}"
                                            class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-black/60 px-3 py-2 text-lg font-bold text-white hover:bg-black/75">
                                            ‹
                                        </button>

                                        <button type="button" data-gallery-next
                                            aria-label="Siguiente imagen de {{ $product->name }}"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-black/60 px-3 py-2 text-lg font-bold text-white hover:bg-black/75">
                                            ›
                                        </button>
                                    @endif
                                </div>

                                @if ($galleryImages->count() > 1)
                                    <div class="flex gap-2 overflow-x-auto border-t border-slate-100 p-3"
                                        aria-label="Seleccionar imagen de {{ $product->name }}">
                                        @foreach ($galleryImages as $galleryImage)
                                            <button type="button" data-gallery-thumbnail
                                                data-image-url="{{ $galleryImage['url'] }}"
                                                data-image-alt="{{ $galleryImage['alt'] }}"
                                                aria-label="Mostrar {{ $galleryImage['alt'] }}"
                                                class="shrink-0 rounded-lg border-2 border-transparent focus:border-emerald-500 focus:outline-none">
                                                <img src="{{ $galleryImage['url'] }}" alt=""
                                                    class="h-16 w-16 rounded-md object-cover">
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <h2 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h2>

                            @if ($product->category)
                                <p class="mt-1 text-sm text-gray-500">{{ $product->category->name }}</p>
                            @endif

                            @if ($product->description)
                                <p class="mt-3 text-sm text-gray-600">{{ $product->description }}</p>
                            @endif

                            <p class="mt-4 text-xl font-bold text-indigo-600">
                                {{ $store->currency }} {{ number_format((float) $product->price, 2) }}
                            </p>

                            <p data-cart-quantity="{{ $product->id }}"
                                class="mt-2 text-sm font-semibold text-emerald-700 {{ isset($cartItems[$product->id]) ? '' : 'hidden' }}">
                                En carrito: <span>{{ $cartItems[$product->id]['quantity'] ?? 0 }}</span>
                            </p>

                            <form method="POST" action="{{ route('cart.add', $product) }}"
                                data-cart-form="{{ $product->id }}" class="mt-auto pt-4">
                                @csrf
                                <div class="flex items-center gap-2">
                                    <label for="quantity-{{ $product->id }}" class="text-sm text-gray-700">
                                        Cantidad
                                    </label>
                                    <input id="quantity-{{ $product->id }}" name="quantity" type="number"
                                        min="1" value="1" required
                                        class="w-20 rounded-lg border-slate-300 text-center shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>

                                <button type="submit"
                                    class="mt-3 w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Agregar al carrito
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="text-gray-600">Esta tienda todavía no tiene productos disponibles.</p>
                @endforelse
            </section>
        </main>
    </div>
    <script>
        document.querySelectorAll('[data-gallery]').forEach((gallery) => {
            const mainImage = gallery.querySelector('[data-gallery-main]');
            const thumbnails = Array.from(gallery.querySelectorAll('[data-gallery-thumbnail]'));
            const previousButton = gallery.querySelector('[data-gallery-prev]');
            const nextButton = gallery.querySelector('[data-gallery-next]');
            let currentIndex = 0;

            const showImage = (index) => {
                currentIndex = (index + thumbnails.length) % thumbnails.length;

                const thumbnail = thumbnails[currentIndex];

                mainImage.src = thumbnail.dataset.imageUrl;
                mainImage.alt = thumbnail.dataset.imageAlt;

                thumbnails.forEach((item, itemIndex) => {
                    item.classList.toggle('border-emerald-500', itemIndex === currentIndex);
                    item.classList.toggle('border-transparent', itemIndex !== currentIndex);
                    item.setAttribute('aria-current', itemIndex === currentIndex ? 'true' : 'false');
                });
            };

            thumbnails.forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', () => showImage(index));
            });

            previousButton?.addEventListener('click', () => showImage(currentIndex - 1));
            nextButton?.addEventListener('click', () => showImage(currentIndex + 1));

            showImage(0);
        });

        document.querySelectorAll('[data-cart-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const productId = form.dataset.cartForm;
                const quantityInput = form.querySelector('[name="quantity"]');
                const quantity = Number(quantityInput.value);
                const token = form.querySelector('[name="_token"]').value;

                if (!Number.isInteger(quantity) || quantity < 1) {
                    quantityInput.focus();
                    return;
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                if (!response.ok) {
                    window.location.reload();
                    return;
                }

                const quantityElement = document.querySelector(`[data-cart-quantity="${productId}"]`);
                const currentQuantity = Number(quantityElement.querySelector('span').textContent) || 0;

                quantityElement.querySelector('span').textContent = currentQuantity + quantity;
                quantityElement.classList.remove('hidden');
            });
        });
    </script>
</x-guest-layout>
