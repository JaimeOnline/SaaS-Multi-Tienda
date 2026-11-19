// resources/js/sidebar.js
import Alpine from 'alpinejs';

// Simple debounce helper
function debounce(fn, delay) {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

Alpine.store('sidebar', {
    // Estados
    collapsed: false,        // Desktop: sidebar colapsado (72px)
    mobileOpen: false,       // Mobile/Tablet: drawer abierto
    bottomNavOpen: false,    // Mobile: bottom nav "Más" drawer
    isMobile: false,         // Reactive: true si < 1024px
    showBottomNav: false,    // Reactive: true si < 768px

    // Inicialización desde localStorage
    init() {
        if (typeof window !== 'undefined') {
            const saved = localStorage.getItem('sidebar:collapsed');
            if (saved !== null) {
                this.collapsed = JSON.parse(saved);
            }
            // Inicializar breakpoints reactivos
            this.updateBreakpoints();
            window.addEventListener('resize', this.debouncedUpdateBreakpoints);
        }
    },

    // Cleanup
    destroy() {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', this.debouncedUpdateBreakpoints);
        }
    },

    // Debounced breakpoint update
    debouncedUpdateBreakpoints: debounce(() => {
        Alpine.store('sidebar').updateBreakpoints();
    }, 150),

    updateBreakpoints() {
        const width = window.innerWidth;
        const newIsMobile = width < 1024;
        const newShowBottomNav = width < 768;

        // Solo actualizar si cambió
        if (this.isMobile !== newIsMobile) {
            this.isMobile = newIsMobile;
            // Al pasar a desktop, cerrar mobile drawer
            if (!newIsMobile && this.mobileOpen) {
                this.closeMobile();
            }
        }

        if (this.showBottomNav !== newShowBottomNav) {
            this.showBottomNav = newShowBottomNav;
            // Al pasar de mobile, cerrar bottom nav drawer
            if (!newShowBottomNav && this.bottomNavOpen) {
                this.closeBottomNav();
            }
        }
    },

    // Toggle colapsado (desktop)
    toggleCollapsed() {
        this.collapsed = !this.collapsed;
        if (typeof window !== 'undefined') {
            localStorage.setItem('sidebar:collapsed', JSON.stringify(this.collapsed));
        }
    },

    // Mobile drawer
    openMobile() {
        this.mobileOpen = true;
        document.body.classList.add('overflow-y-hidden');
    },

    closeMobile() {
        this.mobileOpen = false;
        document.body.classList.remove('overflow-y-hidden');
    },

    toggleMobile() {
        this.mobileOpen ? this.closeMobile() : this.openMobile();
    },

    // Bottom nav "Más" drawer (mobile)
    openBottomNav() {
        this.bottomNavOpen = true;
        document.body.classList.add('overflow-y-hidden');
    },

    closeBottomNav() {
        this.bottomNavOpen = false;
        document.body.classList.remove('overflow-y-hidden');
    },

    toggleBottomNav() {
        this.bottomNavOpen ? this.closeBottomNav() : this.openBottomNav();
    },

    // Cerrar todo (útil al navegar)
    closeAll() {
        this.closeBottomNav();  // Primero: cerrar "Más" backdrop/drawer
        this.closeMobile();     // Segundo: cerrar sidebar lateral
    },

    // Navegación: desktop directo, mobile usa navegación nativa (click listener cierra drawers)
    navigate(route) {
        window.location.href = route;
    },
});

// Global helper para acceso fácil
window.sidebar = Alpine.store('sidebar');

// Global click listener: cierra drawers en mobile/tablet al hacer click en enlaces
// Permite navegación nativa (click derecho, nueva pestaña, accesibilidad)
document.addEventListener('click', (e) => {
    const sidebar = Alpine.store('sidebar');

    // No interferir con acciones del navegador: nueva pestaña, ventana o descarga
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
        return;
    }

    // Solo actuar en mobile/tablet
    if (!sidebar.isMobile) return;

    const link = e.target.closest('a[href]');
    if (!link) return;

    // No interceptar enlaces externos, anclas o targets especiales
    const href = link.getAttribute('href');
    if (
        !href ||
        href.startsWith('#') ||
        href.startsWith('http') ||
        link.target === '_blank' ||
        link.hasAttribute('download')
    ) {
        return;
    }

    // Cerrar inmediatamente, manteniendo la navegación nativa del enlace
    sidebar.closeAll();
}, true);

// Keyboard navigation para sidebar
document.addEventListener('keydown', (e) => {
    const sidebar = Alpine.store('sidebar');

    // Escape cierra drawers
    if (e.key === 'Escape') {
        if (sidebar.mobileOpen || sidebar.bottomNavOpen) {
            sidebar.closeAll();
        }
    }
});