<x-ui.modal name="category-create" size="md" title="Nueva categoría">
    <form method="POST" action="{{ route('categories.store') }}" id="category-create-form" class="space-y-4">
        @csrf

        <x-ui.input
            name="name"
            label="Nombre de la categoría"
            placeholder="Ej. Postres, Bebidas, Entrantes..."
            required
            autofocus
            :value="old('name')" />

        <x-slot name="footer">
            <x-ui.button variant="ghost" @click="$dispatch('close-modal', { detail: 'category-create' })">Cancelar</x-ui.button>
            <x-ui.button variant="primary" type="submit">Crear categoría</x-ui.button>
        </x-slot>
    </form>

    <script>
        document.getElementById('category-create-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creando...';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (response.ok) {
                    window.toast?.success(data.message || 'Categoría creada correctamente');
                    form.reset();
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'category-create' }));
                    // Recargar datatable si existe
                    const dt = document.querySelector('[x-data*="datatableComponent"]');
                    if (dt && dt.__alpine_components_) {
                        dt.__alpine_components_[0].fetchData();
                    }
                } else {
                    const errors = data.errors || { general: data.message || 'Error al crear' };
                    Object.entries(errors).forEach(([field, messages]) => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.setAttribute('aria-invalid', 'true');
                            input.classList.add('border-semantic-error', 'focus:ring-semantic-error');
                            let errorEl = input.parentElement.querySelector('[role="alert"]');
                            if (!errorEl) {
                                errorEl = document.createElement('p');
                                errorEl.setAttribute('role', 'alert');
                                errorEl.className = 'mt-1.5 text-sm text-semantic-error';
                                input.parentElement.appendChild(errorEl);
                            }
                            errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;
                        } else {
                            window.toast?.error(Array.isArray(messages) ? messages[0] : messages);
                        }
                    });
                }
            } catch (err) {
                console.error(err);
                window.toast?.error('Error de conexión');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    </script>
</x-ui.modal>
