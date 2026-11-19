<?php
$f = 'c:/Users/Jaime/Documents/Mis Documentos/Programacion/SaaS Multi Tienda/mvp/resources/js/sidebar.js';
$content = <<<'JS'
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
        this.closeMobile();
        this.closeBottomNav();
    },
});

// Global helper para acceso fácil
window.sidebar = Alpine.store('sidebar');

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
JS;
file_put_contents($f, $content);
echo "Sidebar.js written successfully\n";