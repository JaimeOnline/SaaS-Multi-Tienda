// resources/js/toast.js
import Alpine from 'alpinejs';

Alpine.store('toast', {
    toasts: [],
    
    add(toast) {
        const id = Date.now() + Math.random();
        const defaults = {
            id,
            type: 'info',
            title: '',
            message: '',
            duration: 4000,
            dismissible: true,
            action: null,
        };
        this.toasts.push({ ...defaults, ...toast });
        
        if (toast.duration !== 0) {
            setTimeout(() => this.dismiss(id), toast.duration || 4000);
        }
    },
    
    dismiss(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    },
    
    // Helpers
    success(message, options = {}) { this.add({ type: 'success', message, ...options }); },
    error(message, options = {}) { this.add({ type: 'error', message, ...options }); },
    warning(message, options = {}) { this.add({ type: 'warning', message, ...options }); },
    info(message, options = {}) { this.add({ type: 'info', message, ...options }); },
});

// Global helpers para uso en blade inline scripts
window.toast = Alpine.store('toast');