<x-ui.modal name="share-store" title="Compartir tu tienda" size="md">
    <div class="space-y-4">
        <div class="text-center">
            <p class="text-surface-600">Comparte el enlace de tu tienda:</p>
            <div class="mt-2 flex gap-2">
                <input
                    type="text"
                    value="{{ route('store.public', $store->slug) }}"
                    readonly
                    class="flex-1 rounded-xl border border-surface-300 bg-surface-50 px-3 py-2 text-sm font-mono text-surface-700"
                    id="store-url"
                />
                <x-ui.button variant="secondary" @click="navigator.clipboard.writeText(document.getElementById('store-url').value); toast.success('Enlace copiado')">
                    <x-lucide-icon name="copy" class="h-4 w-4" />
                </x-ui.button>
            </div>
        </div>

        <div class="rounded-xl border border-surface-200 p-4 text-center">
            <p class="text-sm text-surface-500">Código QR</p>
            <div id="store-qr" class="mt-3 flex justify-center"></div>
        </div>

        <div class="flex gap-2">
            <x-ui.button variant="primary" @click="shareViaWhatsApp('{{ route('store.public', $store->slug) }}')">
                <x-lucide-icon name="message-square" class="h-4 w-4" />
                WhatsApp
            </x-ui.button>
            <x-ui.button variant="secondary" @click="shareNative('{{ route('store.public', $store->slug) }}')">
                <x-lucide-icon name="share-2" class="h-4 w-4" />
                Compartir
            </x-ui.button>
        </div>

        <script>
            // Generar QR code si la librería está disponible
            if (typeof QRCode !== 'undefined') {
                new QRCode(document.getElementById('store-qr'), {
                    text: "{{ route('store.public', $store->slug) }}",
                    width: 128,
                    height: 128,
                });
            }

            function shareViaWhatsApp(url) {
                const message = encodeURIComponent('¡Mira mi tienda en TuBase Express! ' + url);
                window.open('https://wa.me/?text=' + message, '_blank');
            }

            function shareNative(url) {
                if (navigator.share) {
                    navigator.share({
                        title: '{{ $store->name }}',
                        text: 'Mi tienda en TuBase Express',
                        url: url,
                    }).catch(() => {});
                } else {
                    navigator.clipboard.writeText(url);
                    toast.success('Enlace copiado al portapapeles');
                }
            }
        </script>
    </div>
</x-ui.modal>