<div
    x-data="{ toasts: [] }"
    x-init="
        // Sync with Alpine store if available
        if (window.toast && window.toast.toasts) {
            this.toasts = window.toast.toasts;
            window.toast.add = (toast) => {
                const id = Date.now() + Math.random();
                const defaults = { id, type: 'info', title: '', message: '', duration: 4000, dismissible: true, action: null };
                this.toasts.push({ ...defaults, ...toast });
                if (toast.duration !== 0) {
                    setTimeout(() => this.dismiss(id), toast.duration || 4000);
                }
            };
            window.toast.dismiss = (id) => { this.toasts = this.toasts.filter(t => t.id !== id); };
            window.toast.success = (msg, opts) => window.toast.add({ type: 'success', message: msg, ...opts });
            window.toast.error = (msg, opts) => window.toast.add({ type: 'error', message: msg, ...opts });
            window.toast.warning = (msg, opts) => window.toast.add({ type: 'warning', message: msg, ...opts });
            window.toast.info = (msg, opts) => window.toast.add({ type: 'info', message: msg, ...opts });
        }
    "
    class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-96 max-w-[calc(100vw-2rem)]"
    role="region"
    aria-label="Notificaciones"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            :id="toast.id"
            class="flex gap-3 p-4 rounded-xl border shadow-lg animate-slide-in"
            :class="{
                'bg-semantic-success-light border-semantic-success text-semantic-success-dark': toast.type === 'success',
                'bg-semantic-error-light border-semantic-error text-semantic-error-dark': toast.type === 'error',
                'bg-semantic-warning-light border-semantic-warning text-semantic-warning-dark': toast.type === 'warning',
                'bg-semantic-info-light border-semantic-info text-semantic-info-dark': toast.type === 'info',
            }"
            role="alert"
            aria-live="polite"
        >
            <div class="flex-shrink-0 text-current" x-html="toastIcons[toast.type]"></div>
            
            <div class="flex-1 min-w-0">
                <h4 x-show="toast.title" class="font-semibold text-current" x-text="toast.title"></h4>
                <p x-show="toast.message" class="mt-0.5 text-sm text-current/90" x-text="toast.message"></p>
            </div>
            
            <a x-show="toast.action" :href="toast.action.href" class="flex-shrink-0 text-sm font-semibold text-current underline hover:no-underline transition-colors duration-fast" x-text="toast.action.label"></a>
            
            <button
                x-show="toast.dismissible"
                type="button"
                x-on:click="toasts = toasts.filter(t => t.id !== toast.id)"
                class="flex-shrink-0 p-1 rounded-lg text-current/50 hover:text-current/70 hover:bg-current/10 transition-colors duration-fast focus:outline-none focus:ring-2 focus:ring-current"
                aria-label="Cerrar notificación"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>

<script>
    const toastIcons = {
        success: '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        error: '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10 7.293 11.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
        warning: '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
        info: '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
    };
</script>

<style>
    @keyframes slide-in {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>