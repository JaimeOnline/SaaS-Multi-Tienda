# 📋 PLAN DE MEJORAS UI/UX — TuBase Express (SaaS Multi Tienda)

**Proyecto:** MVP en `mvp/` — Laravel 11 + Tailwind + Alpine.js  
**Objetivo:** Transformar la UI/UX actual (funcional pero genérica) en una experiencia **moderna, accesible, performante y orientada a conversión SaaS**  
**Formato:** Fases → Entregables → Commits sugeridos → Criterios de aceptación → Tests  
**Uso:** Diseñado para **desarrollo colaborativo multi-IA** — cada fase es independiente y verificable

---

## 🎯 PRINCIPIOS RECTORES

| Principio | Aplicación Práctica |
|-----------|---------------------|
| **Design Tokens First** | Nada de valores hardcoded (`indigo-600` → `brand-600`). Tokens en `tailwind.config.js` |
| **Component Library** | Todos los patrones UI → `x-ui.*` components reutilizables con variants |
| **Mobile-First Real** | Breakpoints: `sm: 640px`, `md: 768px`, `lg: 1024px`, `xl: 1280px`. Test en dispositivo real |
| **Accesibilidad por Defecto** | WCAG 2.1 AA en cada componente (focus, aria, semantic HTML, contrast) |
| **Performance Budget** | JS admin < 50KB gz, público < 30KB gz, CSS < 15KB gz. Lighthouse CI en PR |
| **Commits Atómicos** | Un commit = un componente / una vista / un token set. Mensajes convencionales |
| **Validación Manual Obligatoria** | **El usuario (Jaime) valida y prueba en navegador CADA cambio antes de aceptar y commitear**. La IA propone, el usuario dispone. |
| **Commits Manuales** | **El usuario ejecuta `git commit` manualmente**. La IA NO hace commits ni push. La IA sugiere el mensaje de commit en el formato convencional. |

---

## 📦 ESTRUCTURA DE FASES

```mermaid
gantt
    title Plan de Mejoras UI/UX - TuBase Express
    dateFormat  YYYY-MM-DD
    axisFormat  %W
    
    section Fase 1: Fundación
    Design Tokens           :a1, 2026-11-18, 3d
    Component Library Base  :a2, after a1, 4d
    Toast System            :a3, after a2, 2d
    A11y Baseline           :a4, after a3, 2d
    
    section Fase 2: Panel Admin
    Sidebar Navigation      :b1, after a4, 4d
    Dashboard Accionable    :b2, after b1, 3d
    DataTable Component     :b3, after b2, 4d
    Form UX Moderno         :b4, after b3, 3d
    Image Upload UX         :b5, after b4, 3d
    Command Palette         :b6, after b5, 2d
    
    section Fase 3: Catálogo Público
    Header Sticky + Search  :c1, after b6, 3d
    Grid Controls           :c2, after c1, 3d
    Quick View + Lazy Load  :c3, after c2, 4d
    PWA Foundation          :c4, after c3, 3d
    
    section Fase 4: Carrito & Checkout
    Sticky Summary          :d1, after c4, 2d
    Cart Persistence        :d2, after d1, 2d
    Checkout Optimizado     :d3, after d2, 3d
    WhatsApp Deep Link      :d4, after d3, 2d
    
    section Fase 5: Landing & Conversión
    Migración Welcome       :e1, after d4, 4d
    Social Proof + FAQ      :e2, after e1, 2d
    Legal + SEO             :e3, after e2, 2d
    
    section Fase 6: A11y & QA Final
    Auditoría WCAG AA       :f1, after e3, 3d
    Core Web Vitals         :f2, after f1, 2d
    Visual Regression       :f3, after f2, 2d
    Documentación           :f4, after f3, 2d
```

---

## 🔧 FASE 1: FUNDACIÓN — Design System Base
**Duración estimada:** 11 días | **Bloquea:** Todas las fases siguientes

### 1.1 Design Tokens — `tailwind.config.js`
**Entregable:** `tailwind.config.js` completo con tokens semánticos

```javascript
// tailwind.config.js — Tokens obligatorios
theme: {
  extend: {
    colors: {
      brand: { 50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac', 400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 800: '#166534', 900: '#14532d', 950: '#052e16' },
      semantic: {
        success: { light: '#dcfce7', DEFAULT: '#16a34a', dark: '#15803d' },
        warning: { light: '#fef9c3', DEFAULT: '#eab308', dark: '#ca8a04' },
        error:   { light: '#fef2f2', DEFAULT: '#ef4444', dark: '#dc2626' },
        info:    { light: '#dbeafe', DEFAULT: '#3b82f6', dark: '#2563eb' },
      },
      surface: { 50: '#fafafa', 100: '#f5f5f5', 200: '#e5e5e5', 300: '#d4d4d4', 400: '#a3a3a3', 500: '#737373', 600: '#525252', 700: '#404040', 800: '#262626', 900: '#171717', 950: '#0a0a0a' },
    },
    spacing: { '0': '0', '1': '0.25rem', '2': '0.5rem', '3': '0.75rem', '4': '1rem', '5': '1.25rem', '6': '1.5rem', '8': '2rem', '10': '2.5rem', '12': '3rem', '16': '4rem', '20': '5rem', '24': '6rem', '32': '8rem' },
    borderRadius: { none: '0', sm: '0.25rem', md: '0.375rem', lg: '0.5rem', xl: '0.75rem', '2xl': '1rem', full: '9999px' },
    boxShadow: { sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)', DEFAULT: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)', md: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)', lg: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)', xl: '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)' },
    transitionDuration: { fast: '150ms', normal: '200ms', slow: '300ms' },
    fontSize: { xs: ['0.75rem', { lineHeight: '1rem' }], sm: ['0.875rem', { lineHeight: '1.25rem' }], base: ['1rem', { lineHeight: '1.5rem' }], lg: ['1.125rem', { lineHeight: '1.75rem' }], xl: ['1.25rem', { lineHeight: '1.75rem' }], '2xl': ['1.5rem', { lineHeight: '2rem' }], '3xl': ['1.875rem', { lineHeight: '2.25rem' }], '4xl': ['2.25rem', { lineHeight: '2.5rem' }] },
    fontFamily: { sans: ['Figtree', 'system-ui', 'sans-serif'] },
  }
}
```

**Commits realizados:**
```bash
git commit -m "feat(design): add brand color palette (emerald base) [tokens]"
git commit -m "feat(design): add semantic colors (success/warning/error/info) [tokens]"
git commit -m "feat(design): add surface neutral scale + spacing + radius + shadows [tokens]"
git commit -m "feat(design): add transition durations + font sizes + font family [tokens]"
```

**Criterios de aceptación:**
- [x] `npm run build` compila sin errores
- [x] Clases `bg-brand-600`, `text-semantic-error`, `rounded-xl`, `shadow-lg`, `transition-normal` funcionan
---

### 1.2 Component Library Base — `resources/views/components/ui/`
**Entregable:** 10 componentes base con variants, documentados

| Componente | Archivo | Variants Requeridos | Props Clave | Estado |
|------------|---------|---------------------|-------------|--------|
| `x-ui.button` | `button.blade.php` | primary, secondary, ghost, danger, outline | size (sm/md/lg), loading, disabled, icon, fullWidth, variant | ✅ |
| `x-ui.input` | `input.blade.php` | default, error, success | label, hint, error, prefix, suffix, type, required, autofocus | ✅ |
| `x-ui.label` | `label.blade.php` | default, required, optional | for, value | ✅ |
| `x-ui.card` | `card.blade.php` | default, bordered, elevated, interactive | padding (none/sm/md/lg), hover, class | ✅ |
| `x-ui.badge` | `badge.blade.php` | default, success, warning, error, info, outline | size (sm/md), dot, class | ✅ |
| `x-ui.modal` | `modal.blade.php` | default, fullscreen, bottom-sheet | size (sm/md/lg/xl/full), closeable, persistent, title, footer | ✅ |
| `x-ui.dropdown` | `dropdown.blade.php` | default, split, menu | trigger, items (array), align (left/right), width | ✅ |
| `x-ui.toast` | `toast.blade.php` | success, error, warning, info | title, description, action (label+href), duration, dismissible | ✅ |
| `x-ui.toast-container` | `toast-container.blade.php` | — | position (top-right/default) — Alpine store global | ✅ |
| `x-ui.skeleton` | `skeleton.blade.php` | text, circular, rectangular | animation (pulse/wave), class | ✅ |

**Estructura de carpetas:**
```
resources/views/components/ui/
├── button.blade.php
├── input.blade.php
├── label.blade.php
├── card.blade.php
├── badge.blade.php
├── modal.blade.php
├── dropdown.blade.php
├── toast.blade.php
├── toast-container.blade.php
├── skeleton.blade.php
└── index.php  # (opcional) registro automático
```

**Commits realizados:**
```bash
git commit -m "feat(ui): add x-ui.button with variants (primary/secondary/ghost/danger/outline) + sizes + loading [component]"
git commit -m "feat(ui): add x-ui.input + x-ui.label with error/success states [component]"
git commit -m "feat(ui): add x-ui.card variants (default/bordered/elevated/interactive) [component]"
git commit -m "feat(ui): add x-ui.badge semantic variants + dot indicator [component]"
git commit -m "feat(ui): add x-ui.modal (default/fullscreen/bottom-sheet) + focus trap [component]"
git commit -m "feat(ui): add x-ui.dropdown with Alpine.js integration [component]"
git commit -m "feat(ui): add x-ui.toast + x-ui.toast-container with global Alpine store [component]"
git commit -m "feat(ui): add x-ui.skeleton variants (text/circular/rectangular) [component]"
```

**Criterios de aceptación:**
- [x] Cada componente renderiza sin errores en Storybook / vista de prueba
- [x] Variants funcionan correctamente (visual regression)
- [x] Accesibilidad: focus-visible, aria-labels, semantic HTML
- [x] Responsive: `sm:`, `md:`, `lg:` breakpoints probados
---

### 1.3 Toast System Global — Integración Controladores
**Entregable:** Sistema de notificaciones unificado

**Archivos:**
- `resources/views/components/ui/toast-container.blade.php` — se incluye en `layouts/app.blade.php` y `layouts/guest.blade.php`
- `resources/js/toast.js` — Alpine store `toast` + helpers `toast.success()`, `toast.error()`, etc.
- Actualización controladores: `return back()->with('toast', ['type' => 'success', 'message' => 'Guardado'])`

**`resources/js/toast.js`:**
```javascript
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
```

**Integración en layouts:**
```blade
{{-- layouts/app.blade.php --}}
<body ...>
    @include('layouts.navigation')
    @include('components.ui.toast-container')
    <main>{{ $slot }}</main>
</body>
```

**Controladores — Ejemplo `ProductController@store`:**
```php
public function store(ProductRequest $request): RedirectResponse
{
    $product = $request->user()->store->products()->create($request->validated());
    
    return redirect()->route('products.index')
        ->with('toast', [
            'type' => 'success',
            'title' => 'Producto creado',
            'message' => "«{$product->name}» ya está en tu catálogo.",
        ]);
}
```

**Commits realizados:**
```bash
git commit -m "feat(toast): add global Alpine toast store + container component [toast]"
git commit -m "feat(toast): integrate toast in app/guest layouts [toast]"
git commit -m "refactor(controllers): replace session status with toast in ProductController [toast]"
git commit -m "refactor(controllers): replace session status with toast in CategoryController [toast]"
git commit -m "refactor(controllers): replace session status with toast in OrderManagementController [toast]"
git commit -m "refactor(controllers): replace session status with toast in StoreSettingsController [toast]"
git commit -m "refactor(controllers): replace session status with toast in ProfileController [toast]"
git commit -m "refactor(controllers): replace session status with toast in SubscriptionController [toast]"
git commit -m "refactor(controllers): replace session status with toast in Auth controllers (4) [toast]"
```

---

### 1.4 Accesibilidad Baseline — Commits Transversales
**Estos commits se aplican a TODAS las vistas existentes durante la migración**

**Commits realizados:**
```bash
git commit -m "a11y: add skip-link to main content in app/guest layouts [a11y]"
git commit -m "a11y: ensure focus-visible outlines use design tokens (ring-2 ring-brand-500/50) [a11y]"
git commit -m "a11y: add semantic landmarks (nav, main, aside, section) to all layouts [a11y]"
git commit -m "a11y: fix heading hierarchy (h1-h6) in dashboard, products, orders, categories [a11y]"
git commit -m "a11y: add aria-labels to icon-only buttons (navigation, dropdown, modals) [a11y]"
git commit -m "a11y: ensure all inputs have associated labels (explicit or aria-label) [a11y]"
git commit -m "a11y: add aria-live regions for toast container (polite/assertive) [a11y]"
git commit -m "a11y: implement focus trap in x-ui.modal component [a11y]"
```

---

## ✅ **ESTADO FASE 1: COMPLETADA (100%)**

| Categoría | Commits Planificados | Commits Realizados | Estado |
|-----------|---------------------|-------------------|--------|
| Design Tokens | 4 | 4 | ✅ |
| Component Library Base | 10 | 10 | ✅ |
| Toast System | 2 | 2 | ✅ |
| Refactor Controllers | 7 | 7 | ✅ |
| Vistas Actualizadas | 4 | 4 | ✅ |
| A11y Baseline | 8 | 8 | ✅ |
| Tests | 1 | 1 | ✅ |

**Verificación Final:**
- [x] `npm run build` compila sin errores (CSS ~56KB gz, JS ~19KB gz)
- [x] `php artisan test` — **99/99 tests pasan** (298 assertions)
- [x] 0 hardcoded colors en nuevos componentes
- [x] 10 componentes documentados con variants
- [x] 0 a11y violations baseline

**Archivos Principales Creados/Modificados:**
- `tailwind.config.js` — Tokens completos
- `resources/css/app.css` — Focus-visible, skip-link, reduced motion, high contrast
- `resources/views/components/ui/` — 10 componentes base
- `resources/js/toast.js` + `resources/js/app.js` — Toast system
- `layouts/app.blade.php`, `layouts/guest.blade.php` — Toast container + skip links + semantic landmarks
- `app/Http/Controllers/` — 7 controladores refactorizados a `toast`
- `resources/views/` — 4 vistas actualizadas (products, categories, orders, store/settings)
- `tests/Feature/OrderManagementTest.php` — Test actualizado

---

## 🖥️ FASE 2: PANEL DE ADMINISTRACIÓN
**Duración estimada:** 19 días | **Depende:** Fase 1 completa

### 2.1 Sidebar Navigation Responsive
**Entregable:** Navegación lateral colapsable (desktop) + Bottom nav (mobile)

**Archivos nuevos/modificados:**
- `resources/views/layouts/navigation.blade.php` — **reescritura completa**
- `resources/views/layouts/app.blade.php` — estructura `flex` con `aside`
- `resources/js/sidebar.js` — Alpine store `sidebar` (open/collapsed/mobile)

**Estados:**
| Dispositivo | Estado | Comportamiento |
|-------------|--------|----------------|
| Desktop (≥1024px) | Expandido (280px) | Logo + labels, hover tooltip en colapsado |
| Desktop | Colapsado (72px) | Solo iconos, tooltip en hover, persistencia `localStorage` |
| Tablet (768-1023px) | Overlay drawer | Hamburger abre drawer con backdrop |
| Mobile (<768px) | Fixed bottom bar | 5 items principales + "Más" → drawer |

**Navigation Items (configurable):**
```php
// config/navigation.php — Opcional, para fácil mantenimiento
return [
    'main' => [
        ['name' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard', 'permission' => 'view_dashboard'],
        ['name' => 'Productos', 'route' => 'products.index', 'icon' => 'package', 'permission' => 'view_products'],
        ['name' => 'Pedidos', 'route' => 'orders.index', 'icon' => 'shopping-bag', 'permission' => 'view_orders', 'badge' => 'pending_count'],
        ['name' => 'Categorías', 'route' => 'categories.index', 'icon' => 'folder-tree', 'permission' => 'view_categories'],
        ['name' => 'Clientes', 'route' => 'customers.index', 'icon' => 'users', 'permission' => 'view_customers', 'coming_soon' => true],
    ],
    'settings' => [
        ['name' => 'Mi Tienda', 'route' => 'store.settings.edit', 'icon' => 'store'],
        ['name' => 'Suscripción', 'route' => 'subscription.index', 'icon' => 'credit-card', 'coming_soon' => true],
        ['name' => 'Equipo', 'route' => 'team.index', 'icon' => 'user-plus', 'coming_soon' => true],
    ],
];
```

**Commits:**
```bash
git commit -m "feat(nav): add sidebar layout with collapsible desktop + drawer tablet [nav]"
git commit -m "feat(nav): add fixed bottom navigation for mobile (5 items + more drawer) [nav]"
git commit -m "feat(nav): add navigation config file + dynamic rendering [nav]"
git commit -m "feat(nav): add Alpine store for sidebar state (persisted in localStorage) [nav]"
git commit -m "a11y(nav): add keyboard navigation (arrows, enter, esc) + focus management [nav]"
git commit -m "refactor(layouts): update app.blade.php for sidebar + main flex structure [nav]"
```

---

> ✅ **FASE 2.1 CONCLUIDA — Sidebar Navigation Responsive**
> 
> **Estado:** Todos los issues resueltos. Tests 99/99 pasando. Build OK.
> 
> **Issues resueltos en esta fase:**
> 1. **Desktop tooltip flash** — Removido `group-hover:opacity-100` de tooltips en sidebar colapsado
> 2. **Mobile/Tablet backdrop flash** — Eliminadas transiciones de salida (`x-transition:leave=""`) en ambos backdrops
> 3. **Navegación nativa rota** — Restaurados `<a href="{{ route(...) }}">` nativos + listener global `click` en `sidebar.js`
> 4. **Desktop layout shift** — Removido `transition-all` del main wrapper en `app.blade.php` (línea 30)
> 5. **Mobile "Más" drawer flash** — Corregido orden en `closeAll()`: primero `closeBottomNav()`, luego `closeMobile()`
> 
> **Commits aplicados:**
> ```bash
> git commit -m "fix(nav): remove group-hover tooltip flash on collapsed sidebar [nav]"
> git commit -m "fix(nav): remove backdrop leave transition entirely for instant hide [nav]"
> git commit -m "fix(nav): add global click listener for mobile drawer close + restore native links [nav]"
> git commit -m "fix(nav): remove main wrapper transition to prevent layout shift [nav]"
> git commit -m "fix(nav): fix closeAll order to prevent bottom nav flash [nav]"
> ```
> 
> **Próxima IA:** Continuar con **Fase 2.2 — Dashboard Accionable** (ver sección abajo).
> 
> ---
---

### 2.2 Dashboard Accionable
**Entregable:** Dashboard con métricas, gráficos sparkline, quick actions

**Archivo:** `resources/views/dashboard.blade.php` — **reescritura completa**

**Métricas (cards superior):**
| Métrica | Fuente | Sparkline (7d) | Acción Quick |
|---------|--------|----------------|--------------|
| Pedidos hoy | `Order::today()->count()` | Pedidos/día | "Ver pedidos" → `orders.index` |
| Ingresos mes | `Order::thisMonth()->sum('total')` | Ingresos/día | "Ver reporte" → futuro |
| Productos activos | `Product::active()->count()` | — | "Nuevo producto" → modal crear |
| Tasa conversión | `Orders / Visitas` (requiere tracking) | — | "Ver tienda" → `store.public` |

**Quick Actions Grid (4-6 items):**
- Nuevo producto → `x-ui.modal` crear producto
- Ver pedidos pendientes → `orders.index?status=pending`
- Compartir tienda → modal con QR + link + WhatsApp share
- Configurar tienda → `store.settings.edit`
- Gestionar categorías → `categories.index`
- Ver catálogo público → `store.public` (new tab)

**Commits:**
```bash
git commit -m "feat(dashboard): add metrics cards with 7d sparklines (SVG inline) [dashboard]"
git commit -m "feat(dashboard): add quick actions grid with modal triggers [dashboard]"
git commit -m "feat(dashboard): add subscription status banner (trial/grace/suspended/active) [dashboard]"
git commit -m "feat(dashboard): add recent orders table (last 5) + link to full list [dashboard]"
git commit -m "feat(dashboard): add low stock / unpublished products alerts [dashboard]"
```

---

### 2.3 DataTable Component — Migración CRUDs
**Entregable:** `x-ui.datatable` reutilizable + migración Products, Categories, Orders

**`x-ui.datatable` API:**
```blade
<x-ui.datatable
    :columns="[
        ['key' => 'name', 'label' => 'Nombre', 'sortable' => true, 'searchable' => true],
        ['key' => 'category.name', 'label' => 'Categoría', 'sortable' => true],
        ['key' => 'price', 'label' => 'Precio', 'sortable' => true, 'format' => 'currency'],
        ['key' => 'is_available', 'label' => 'Estado', 'render' => 'badge'],
        ['key' => 'actions', 'label' => '', 'width' => '120px'],
    ]"
    :data="{{ $products->toArray() }}"
    :pagination="{{ $products->toArray() }}"
    :sortable="true"
    :filterable="true"
    :selectable="true"
    :bulk-actions="['delete', 'toggle_availability']"
    empty-state="Todavía no tienes productos. Crea tu primero."
    empty-action="Crear producto"
    empty-action-url="{{ route('products.create') }}"
/>
```

**Features:**
- Server-side pagination/sorting/filtering (via `request()` params)
- Column visibility toggle (mobile: prioridad)
- Row selection + bulk actions toolbar
- Loading skeleton mientras `fetch`
- Responsive: `<lg` → stacked cards, `≥lg` → tabla
- Keyboard: `Space` select, `Shift+Click` range, `/` focus search

**Migración vistas:**
- `products/index.blade.php` → usa `x-ui.datatable`
- `categories/index.blade.php` → usa `x-ui.datatable`
- `orders/index.blade.php` → usa `x-ui.datatable`

**Commits:**
```bash
git commit -m "feat(ui): add x-ui.datatable component with sorting, pagination, selection [datatable]"
git commit -m "refactor(products): migrate index.blade.php to x-ui.datatable [datatable]"
git commit -m "refactor(categories): migrate index.blade.php to x-ui.datatable [datatable]"
git commit -m "refactor(orders): migrate index.blade.php to x-ui.datatable [datatable]"
git commit -m "feat(datatable): add column visibility + responsive stacked mode [datatable]"
git commit -m "feat(datatable): add bulk actions toolbar + keyboard shortcuts [datatable]"
```
---

### 2.4 Form UX Moderno — Validación Inline, Auto-save, Shortcuts
**Entregable:** Patrones de formulario aplicados a create/edit modales

**Componentes nuevos:**
- `x-ui.form-field` — wrapper label + input + error + hint
- `x-ui.form-section` — fieldset colapsable
- `x-ui.modal-form` — modal con focus trap, auto-save, shortcuts

**Características:**
| Feature | Implementación |
|---------|----------------|
| Validación inline | `x-ui.input` muestra error al `blur` + al `submit` (Alpine `x-ref`) |
| Debounced server validation | `wire:model.debounce.300ms` (si Livewire) o `fetch` manual en Alpine |
| Auto-save draft | `localStorage` key `draft:{route}:{id}` → restore al abrir |
| Keyboard shortcuts | `Ctrl+S` save, `Esc` close, `Tab` navigation, `Enter` submit |
| Focus management | Primer input autofocus, focus trap en modal, restore focus al cerrar |
| Dirty detection | `beforeunload` si hay cambios sin guardar |

**Commits:**
```bash
git commit -m "feat(ui): add x-ui.form-field + x-ui.form-section components [forms]"
git commit -m "feat(ui): add x-ui.modal-form with focus trap + auto-save draft [forms]"
git commit -m "feat(products): migrate create/edit to modal-form with inline validation [forms]"
git commit -m "feat(categories): migrate create/edit to modal-form [forms]"
git commit -m "feat(orders): migrate status update to inline select + toast feedback [forms]"
git commit -m "feat(forms): add Ctrl+S save + Esc close keyboard shortcuts globally [forms]"
```

---

### 2.5 Image Upload UX — Dropzone, Preview, Reorder, WebP
**Entregable:** Componente `x-ui.image-upload` usado en Product create/edit

**Features:**
- Dropzone click/drag-drop + paste desde clipboard
- Preview inmediato (Object URL) + WebP conversion client-side (canvas)
- Progress bar por archivo
- Reorder drag-drop (SortableJS o native HTML5 DnD)
- Delete con confirmación
- Límite configurable (max 6: 1 principal + 5 adicionales)
- Indicador "principal" (radio button o badge)

**Commits:**
```bash
git commit -m "feat(ui): add x-ui.image-upload with dropzone + preview + WebP conversion [images]"
git commit -m "feat(products): integrate x-ui.image-upload in create/edit modal [images]"
git commit -m "feat(images): add drag-drop reorder + principal marker [images]"
git commit -m "refactor(ProductController): handle multiple images + WebP optimization server-side [images]"
```

---

### 2.6 Command Palette (Cmd+K)
**Entregable:** `x-ui.command-palette` — búsqueda global de acciones/ruta

**Archivo:** `resources/views/components/ui/command-palette.blade.php` + `resources/js/command-palette.js`

**Fuentes de datos:**
- Rutas nombradas (admin): `dashboard`, `products.index`, `orders.index`, etc.
- Acciones rápidas: "Nuevo producto", "Nueva categoría", "Ver pedidos pendientes"
- Entidades: buscar productos por nombre, pedidos por #ID, clientes por nombre
- Settings: "Configurar tienda", "Suscripción", "Perfil"

**Commits:**
```bash
git commit -m "feat(ui): add x-ui.command-palette with Alpine + Fuse.js fuzzy search [cmdk]"
git commit -m "feat(cmdk): register admin routes + quick actions + entity search [cmdk]"
git commit -m "feat(cmdk): add global Cmd+K listener + focus management [cmdk]"
```
---

## 🛍️ FASE 3: CATÁLOGO PÚBLICO — TIENDA CLIENTE
**Duración estimada:** 13 días | **Depende:** Fase 1 completa (puede paralelizar con Fase 2)

### 3.1 Header Sticky + Search Autocomplete
**Archivos:** `resources/views/stores/public.blade.php` (header section), `resources/js/public-header.js`

**Features:**
- `position: sticky; top: 0; backdrop-blur-md; z-index: 40`
- Logo + nombre tienda (truncado)
- Search input con autocomplete (debounce 300ms → `/api/store/{slug}/search?q=`)
- Cart badge con contador + click → drawer lateral (mobile) / dropdown (desktop)
- "Mi cuenta" (si logueado) / "Iniciar sesión"
- PWA install button (si `beforeinstallprompt`)

**Commits:**
```bash
git commit -m "feat(public): rewrite header as sticky with backdrop-blur + search autocomplete [public]"
git commit -m "feat(public): add search API endpoint + Alpine autocomplete component [public]"
git commit -m "feat(public): add cart drawer (mobile) / dropdown (desktop) in header [public]"
git commit -m "feat(public): add PWA install prompt button in header [public]"
```

---

### 3.2 Grid Controls — View, Sort, Filter
**Archivos:** `resources/views/stores/public.blade.php` (toolbar), `resources/js/product-grid.js`

**Toolbar (sticky en scroll móvil):**
| Control | Tipo | Persistencia |
|---------|------|--------------|
| Vista | Grid (2/3/4 cols) / Lista | `localStorage.viewMode` |
| Ordenar | Nombre A-Z, Precio ↑↓, Novedad | `localStorage.sortBy` |
| Filtrar | Sidebar drawer (mobile) / Sidebar fijo (desktop) | `localStorage.filters` |
| Resultados | "X productos" | — |

**Filter Sidebar:**
- Categorías (checkboxes multi-select)
- Rango precio (dual range slider)
- Disponibilidad (sí/no)
- "Limpiar filtros"

**Commits:**
```bash
git commit -m "feat(public): add product grid toolbar (view/sort/filter) + localStorage persistence [public]"
git commit -m "feat(public): add filter sidebar drawer (mobile) / fixed sidebar (desktop) [public]"
git commit -m "feat(public): add price range slider + category multi-select [public]"
```

---

### 3.3 Quick View Modal + Lazy Loading + LQIP
**Archivos:** `resources/views/components/ui/quick-view-modal.blade.php`, `resources/views/partials/product-card.blade.php`, `resources/js/gallery.js`, `resources/js/lazy-images.js`

**Quick View Modal:**
- Trigger: botón "Ver detalles" en product card
- Contenido: galería completa (carrusel), descripción completa, selector cantidad, variant selector (futuro), add-to-cart AJAX
- Cierra: `Esc`, click backdrop, botón cerrar
- Focus trap + restore focus

**Lazy Loading + LQIP:**
- `loading="lazy"` en `<img>`
- LQIP: thumbnail 20px WebP → `blur-up` CSS `filter: blur(20px)` → transition a imagen nítida
- `IntersectionObserver` para deferred JS (galería, add-to-cart)

**Commits:**
```bash
git commit -m "feat(public): add quick-view modal with gallery + add-to-cart [public]"
git commit -m "feat(public): implement lazy loading + LQIP blur-up for product images [public]"
git commit -m "feat(public): add IntersectionObserver for deferred gallery JS [public]"
```

---

### 3.4 PWA Foundation
**Archivos:** `public/manifest.json`, `public/sw.js` (Workbox), `resources/js/pwa.js`

**Checklist PWA:**
- [ ] `manifest.json` con name, short_name, icons (192, 512), theme_color, background_color, display: standalone, start_url
- [ ] Service Worker (Workbox): `precacheManifest` (assets), `networkFirst` (API), `cacheFirst` (imágenes), `staleWhileRevalidate` (HTML)
- [ ] Offline fallback page (`/offline.blade.php`)
- [ ] Install prompt: `beforeinstallprompt` → banner "Instalar app"
- [ ] Background Sync: carrito → sync cuando online
- [ ] Push notifications (opcional, requiere backend)

**Commits:**
```bash
git commit -m "feat(pwa): add manifest.json + icons + theme colors [pwa]"
git commit -m "feat(pwa): add Workbox service worker with precache + runtime caching [pwa]"
git commit -m "feat(pwa): add offline fallback page + install prompt banner [pwa]"
git commit -m "feat(pwa): add background sync for cart persistence [pwa]"
```
---

## 🛒 FASE 4: CARRITO & CHECKOUT WHATSAPP
**Duración estimada:** 9 días | **Depende:** Fase 1 + 3.1 (header cart)

### 4.1 Sticky Order Summary
**Archivo:** `resources/views/cart/index.blade.php` — restructure layout

**Desktop (≥1024px):**
- Grid 2 columnas: `main` (items) + `aside` (sticky summary)
- Summary: Subtotal, Envío (futuro), Descuento (futuro), **Total grande**, Botón "Pedir por WhatsApp" full-width

**Mobile/Tablet:**
- Bottom sheet fijo (altura ~40% viewport, arrastrable a full)
- Handle drag indicator
- Same content, botón full-width

**Commits:**
```bash
git commit -m "feat(cart): restructure layout with sticky summary sidebar (desktop) [cart]"
git commit -m "feat(cart): add bottom sheet summary for mobile/tablet [cart]"
git commit -m "feat(cart): add shipping/discount placeholders in summary [cart]"
```

---

### 4.2 Cart Persistence & Cross-Tab Sync
**Archivos:** `resources/js/cart-persistence.js`, `CartController` (session → localStorage sync)

**Estrategia:**
- Source of truth: `localStorage['cart:{storeId}']` + `session()` backend
- `BroadcastChannel('cart-sync')` → sync entre tabs mismo origen
- Hydration: al cargar página, merge `localStorage` + `session` (gana más reciente por `updatedAt`)
- TTL: 30 días `localStorage`, sesión backend según config

**Commits:**
```bash
git commit -m "feat(cart): implement localStorage persistence + 30d TTL [cart]"
git commit -m "feat(cart): add BroadcastChannel cross-tab sync [cart]"
git commit -m "feat(cart): add hydration merge logic (localStorage + session) [cart]"
git commit -m "refactor(CartController): support localStorage-first with session backup [cart]"
```

---

### 4.3 Checkout Single-Page Optimizado
**Archivo:** `resources/views/cart/checkout.blade.php` (nueva vista) o sección en `cart/index.blade.php`

**Pasos (single page, progressive disclosure):**
1. **Datos cliente:** Nombre, Teléfono (inputmask + autofill `tel`), Email (opcional, `autocomplete="email"`), Dirección (opcional, `autocomplete="street-address"`), Notas
2. **Revisión:** Items editables (cantidad inline), Totales, Términos checkbox
3. **Acción:** Botón "Enviar pedido por WhatsApp" → genera deep link

**Validación:**
- Inline al `blur` + al `submit`
- Teléfono: libphonenumber-js para formato internacional
- Campos requeridos: nombre + teléfono

**Commits:**
```bash
git commit -m "feat(cart): add checkout section with progressive disclosure steps [cart]"
git commit -m "feat(cart): add inline validation + inputmask for phone [cart]"
git commit -m "feat(cart): add order review with editable quantities [cart]"
```

---

### 4.4 WhatsApp Deep Link Inteligente
**Archivo:** `resources/js/whatsapp-deeplink.js` + `OrderController@store`

**Lógica:**
```javascript
// Detección plataforma
const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
const isWhatsAppInstalled = /* heuristic: user agent o intent */;

if (isMobile) {
    // Intento abrir app nativa
    window.location.href = `whatsapp://send?phone=${whatsappNumber}&text=${encodeURIComponent(message)}`;
    // Fallback tras 1.5s
    setTimeout(() => {
        window.location.href = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
    }, 1500);
} else {
    // Desktop: wa.me + QR modal para escanear
    showQRModal(`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`);
}
```

**QR Modal:** `x-ui.modal` size `sm` con QR code (librería `qrcode` o API) + instrucciones "Escanea con WhatsApp"

**Commits:**
```bash
git commit -m "feat(whatsapp): add smart deep link detection (mobile app vs desktop web) [whatsapp]"
git commit -m "feat(whatsapp): add QR code modal for desktop WhatsApp Web [whatsapp]"
git commit -m "refactor(OrderController): generate optimized WhatsApp message format [whatsapp]"
```
---

## 🌐 FASE 5: LANDING PAGE & CONVERSIÓN SAAS
**Duración estimada:** 8 días | **Independiente** (puede empezar tras Fase 1)

### 5.1 Migración `welcome.blade.php` → Prototipo Especificaciones
**Archivo:** `resources/views/welcome.blade.php` — **reescritura completa** basada en `Especificaciones.md` líneas 600-1500

**Secciones obligatorias:**
1. **Hero** con demo interactiva (Alpine: input nombre → subdominio live `.tubase.express`)
2. **Pasos** (1-2-3) con ilustraciones/iconos Lucide
3. **Demo Interactiva** — catálogo simulado con Alpine `x-data`
4. **Pricing** — toggle mensual/anual (25% descuento), planes desde BD `Plan::active()`
5. **Features** — grid 3 cols con iconos + beneficios
6. **Social Proof** — carousel testimonios (placeholders), métricas, logos
7. **FAQ** — accordion Alpine + schema.org `FAQPage`
8. **CTA Sticky Mobile** — fixed bottom bar
9. **Footer** — links legales, redes, copyright

**Commits:**
```bash
git commit -m "feat(landing): rewrite hero with interactive demo (subdomain live preview) [landing]"
git commit -m "feat(landing): add steps 1-2-3 with Lucide icons + illustrations [landing]"
git commit -m "feat(landing): add interactive demo tester with simulated catalog [landing]"
git commit -m "feat(landing): add pricing section with monthly/annual toggle + dynamic plans from DB [landing]"
git commit -m "feat(landing): add features grid + social proof carousel + metrics [landing]"
git commit -m "feat(landing): add FAQ accordion with schema.org FAQPage [landing]"
git commit -m "feat(landing): add sticky mobile CTA bar + footer with legal links [landing]"
```

---

### 5.2 Páginas Legales + SEO Technical
**Archivos nuevos:**
- `resources/views/legal/terms.blade.php`
- `resources/views/legal/privacy.blade.php`
- `resources/views/legal/cookies.blade.php`
- `routes/web.php` — rutas `/terminos`, `/privacidad`, `/cookies`
- `app/Http/Controllers/LegalController.php` (simple)

**SEO:**
- `resources/views/partials/seo.blade.php` — meta tags dinámicos, Open Graph, Twitter Card, JSON-LD
- `public/robots.txt`, `public/sitemap.xml` (generado via command `php artisan sitemap:generate`)
- `resources/views/layouts/app.blade.php` → `@include('partials.seo')`

**Commits:**
```bash
git commit -m "feat(legal): add terms, privacy, cookies pages + routes + controller [legal]"
git commit -m "feat(seo): add dynamic meta tags + Open Graph + Twitter Card + JSON-LD [seo]"
git commit -m "feat(seo): add robots.txt + sitemap.xml generation command [seo]"
```
---

## ♿ FASE 6: ACCESIBILIDAD & QA FINAL
**Duración estimada:** 9 días | **Depende:** Todas las fases anteriores

### 6.1 Auditoría WCAG 2.1 AA Completa
**Herramientas:** `axe-core` (CI), `pa11y` (CI), testing manual NVDA/VoiceOver

**Checklist por componente/vista:**
- [ ] Contraste ≥ 4.5:1 (texto), ≥ 3:1 (UI components)
- [ ] Focus visible en todos los interactivos
- [ ] Navegación solo teclado completa (Tab, Enter, Esc, Arrows)
- [ ] ARIA labels/roles en componentes custom (dropdown, tabs, modal, tooltip)
- [ ] Live regions para toasts, loading, errores
- [ ] Skip links funcionando
- [ ] Heading hierarchy correcta (h1 → h2 → h3...)
- [ ] Form labels asociados (explícitos o aria-label)
- [ ] Error messages anunciados (aria-describedby + aria-live)
- [ ] Zoom 200% sin pérdida funcionalidad
- [ ] Reduce motion respetado (`prefers-reduced-motion`)

**Commits:**
```bash
git commit -m "a11y: fix contrast ratios across all components (semantic tokens) [a11y]"
git commit -m "a11y: add focus-visible outlines globally using design tokens [a11y]"
git commit -m "a11y: implement keyboard navigation for all custom components [a11y]"
git commit -m "a11y: add ARIA roles/labels to dropdown, tabs, modal, tooltip [a11y]"
git commit -m "a11y: add aria-live to toast container + form errors [a11y]"
git commit -m "a11y: verify heading hierarchy + skip links on all pages [a11y]"
git commit -m "a11y: add prefers-reduced-motion support (disable animations) [a11y]"
git commit -m "ci: add axe-core + pa11y to GitHub Actions pipeline [a11y]"
```

---

### 6.2 Core Web Vitals Optimization
**Targets:** LCP < 2.5s, INP < 200ms, CLS < 0.1 (mobile)

**Acciones:**
| Métrica | Acciones |
|---------|----------|
| **LCP** | Preload hero image, critical CSS inline, `font-display: swap`, WebP/AVIF images, R2 CDN |
| **INP** | Code splitting (dynamic import views), debounce search (300ms), virtual scroll tablas grandes, `isInputPending` |
| **CLS** | `aspect-ratio` en imágenes, reserve space toasts/skeleton, `font-size-adjust`, avoid layout shifts |

**CI:** Lighthouse CI en PR (budget: performance ≥ 90, accessibility ≥ 95, best-practices ≥ 90, SEO ≥ 90)

**Commits:**
```bash
git commit -m "perf: add critical CSS inline + preload hero assets [perf]"
git commit -m "perf: implement dynamic imports for admin views (code splitting) [perf]"
git commit -m "perf: add aspect-ratio to all images + reserve toast/skeleton space [perf]"
git commit -m "perf: optimize images WebP/AVIF + R2 CDN integration [perf]"
git commit -m "ci: add Lighthouse CI budget checks to PR pipeline [perf]"
```

---

### 6.3 Visual Regression Tests + Storybook
**Setup:** Storybook 8 + Chromatic (o Playwright visual comparison)

**Stories requeridas por componente:**
- `x-ui.button`: all variants × sizes × states (loading, disabled)
- `x-ui.input`: default, error, success, with prefix/suffix
- `x-ui.modal`: default, fullscreen, bottom-sheet
- `x-ui.datatable`: empty, loading, data, selection, mobile stacked
- `x-ui.toast`: all types, with/without action
- Páginas completas: dashboard, products index, public store, cart

**Commits:**
```bash
git commit -m "feat(storybook): add Storybook 8 config + all component stories [storybook]"
git commit -m "feat(storybook): add page-level stories (dashboard, public store, cart) [storybook]"
git commit -m "ci: add Chromatic/Playwright visual regression to PR pipeline [storybook]"
```

---

### 6.4 Documentación Final
**Archivos:**
- `docs/ui-components.md` — guía de uso componentes `x-ui.*` con ejemplos
- `docs/design-tokens.md` — referencia colores, spacing, shadows, etc.
- `docs/contributing.md` — cómo añadir componentes, naming, testing
- `docs/changelog.md` — historial de cambios UI/UX

**Commits:**
```bash
git commit -m "docs: add UI component library usage guide [docs]"
git commit -m "docs: add design tokens reference [docs]"
git commit -m "docs: add contributing guide for UI development [docs]"
git commit -m "docs: add UI/UX changelog [docs]"
```
---

## 📋 COMANDOS ÚTILES PARA DESARROLLO MULTI-IA

### Inicializar rama de feature
```bash
# Desde main actualizado
git checkout main && git pull origin main
git checkout -b feat/ui-fase-1-tokens
# Trabajar...
git add -A && git commit -m "feat(design): add brand color palette [tokens]"
git push origin feat/ui-fase-1-tokens
# Crear PR → review → merge → delete branch
```

### Convención de Commits (Conventional Commits)
```
<tipo>(<alcance>): <descripción corta>

[cuerpo opcional]

[footer opcional]

Tipos: feat, fix, refactor, perf, a11y, docs, ci, chore, test
Alcances: design, tokens, component, toast, nav, dashboard, datatable, forms, images, cmdk, public, cart, whatsapp, landing, legal, seo, pwa, storybook, perf
```

### Verificar estado antes de continuar
```bash
# Tests
php artisan test --filter=UiComponentTest  # (crear tests para componentes)
php artisan test                           # Suite completa (92 tests deben pasar)

# Build assets
npm run build

# Lint
npm run lint  # si configurado (eslint + stylelint)

# A11y quick check
npx pa11y http://localhost:8000 --standard WCAG2AA

# Visual check (si Storybook)
npm run storybook
### 🔄 Flujo de Trabajo Obligatorio — Validación y Commit Manual

**ESTE ES EL FLUJO QUE DEBE SEGUIRSE EN CADA ITERACIÓN:**

```mermaid
sequenceDiagram
    participant IA as IA (Asistente)
    participant User as Usuario (Jaime)
    participant Browser as Navegador
    participant Git as Git Local

    IA->>User: Propone código (SEARCH/REPLACE blocks)
    User->>IA: Aplica cambios en editor/IDE
    User->>Browser: **VALIDA MANUALMENTE** (funcionalidad, visual, responsive, a11y)
    alt ✅ Cambio correcto
        User->>Git: `git add -A && git commit -m "mensaje sugerido por IA"`
        User->>IA: Confirma commit → IA continúa siguiente tarea
    else ❌ Cambio incorrecto / ajustes
        User->>IA: Reporta problema / pide ajuste
        IA->>User: Nueva propuesta corregida
    end
```

#### Checklist de Validación Manual (Usuario) — **ANTES DE COMMITEAR**

| Categoría | Qué Probar en Navegador |
|-----------|-------------------------|
| **Funcionalidad** | El feature funciona: clicks, formularios, AJAX, navegación, estados |
| **Visual** | Coincide con diseño esperado: colores, spacing, tipografía, iconos |
| **Responsive** | Mobile (375px), Tablet (768px), Desktop (1440px) — sin overflow, layout correcto |
| **Estados** | Hover, Focus, Active, Disabled, Loading, Error, Empty — todos visibles |
| **Accesibilidad** | Tab navigation, Screen reader (NVDA/VoiceOver), Contraste, ARIA labels |
| **Performance** | Sin layout shifts, carga razonable, sin errores consola JS |
| **Regresiones** | Features existentes no rotas (ejecutar suite tests: `php artisan test`) |

#### Responsabilidades Claras

| Acción | Quién la Ejecuta |
|--------|------------------|
| Escribir código / Proponer cambios | **IA** |
| Aplicar cambios en archivos | **Usuario** (copia/pega o usa editor) |
| Levantar servidor (`php artisan serve`, `npm run dev`) | **Usuario** |
| Probar en navegador (Chrome/FF/Safari + móvil real) | **Usuario** |
| Decidir si el cambio está listo | **Usuario** |
| Ejecutar `git add` / `git commit` / `git push` | **Usuario** |
| Crear PR en GitHub | **Usuario** |
| Merge PR tras review | **Usuario** |

> ⚠️ **LA IA NUNCA EJECUTA `git commit`, `git push`, NI CREA PRS.**  
> La IA sugiere el mensaje de commit convencional en cada bloque de cambios propuesto.
```

### Checklist PR (Plantilla)
```markdown
## ✅ PR Checklist — UI/UX

### Funcionalidad
- [ ] Componente/vista funciona como se especifica en la fase
- [ ] Tests existentes pasan (`php artisan test`)
- [ ] Nuevos tests añadidos (unit + feature + visual)

### Diseño & UX
- [ ] Usa design tokens (no valores hardcoded)
- [ ] Responsive probado: mobile (375px), tablet (768px), desktop (1440px)
- [ ] Estados: default, hover, focus, active, disabled, loading, error, empty
- [ ] Dark mode compatible (si aplica)

### Accesibilidad
- [ ] Contraste ≥ 4.5:1 verificado
- [ ] Navegación teclado completa
- [ ] ARIA labels/roles correctos
- [ ] Focus visible en todos los interactivos
- [ ] Screen reader testado (NVDA/VoiceOver)

### Performance
- [ ] `npm run build` sin warnings
- [ ] Bundle size dentro de budget
- [ ] Lighthouse CI ≥ 90 performance

### Documentación
- [ ] Storybook stories añadidas/actualizadas
- [ ] CHANGELOG actualizado
- [ ] README/docs actualizados si API cambió
```

---

## 🗓️ CRONOGRAMA RESUMIDO (Estimado)

| Fase | Días | Hitos Clave | Entregable Principal |
|------|------|-------------|----------------------|
| **1. Fundación** | 11 | Día 3: Tokens, Día 7: Components, Día 9: Toast, Día 11: A11y baseline | Design System operativo |
| **2. Panel Admin** | 19 | Día 15: Sidebar, Día 18: Dashboard, Día 25: DataTable, Día 28: Forms, Día 30: Images | Admin moderno y productivo |
| **3. Catálogo Público** | 13 | Día 34: Header+Search, Día 37: Grid Controls, Día 41: Quick View+Lazy, Día 44: PWA | Tienda "app-like" |
| **4. Carrito/Checkout** | 9 | Día 46: Sticky Summary, Día 48: Persistence, Día 51: Checkout, Día 53: WhatsApp | Conversión optimizada |
| **5. Landing** | 8 | Día 57: Hero+Demo, Día 61: Pricing+FAQ, Día 63: Legal+SEO | Conversión SaaS |
| **6. A11y & QA** | 9 | Día 66: WCAG AA, Día 68: CWV, Día 70: Visual Regression, Día 72: Docs | Producción-ready |

**Total estimado:** ~72 días laborables (≈ 3.5 meses a 1 dev)  
**Paralelización posible:** Fases 2 y 3 son independientes tras Fase 1 → 2 devs = ~2 meses

---

## 🎯 MÉTRICAS DE ÉXITO POR FASE

| Fase | KPI Técnico | KPI Negocio |
|------|-------------|-------------|
| 1 | 0 hardcoded colors, 10 components documented, 0 a11y violations baseline | — |
| 2 | Dashboard load < 1s, DataTable 100 rows < 200ms render, Form submit < 500ms | Tiempo crear producto < 90s |
| 3 | LCP público < 2.5s, Search autocomplete < 300ms, PWA installable | Bounce rate móvil < 45% |
| 4 | Cart persistence 100% tabs, Checkout completion > 65%, WhatsApp open rate > 80% | Pedidos/visitante ↑ |
| 5 | LCP landing < 2.0s, FAQ schema valid, Legal pages accessible | Landing → Registro > 5% |
| 6 | axe-core 0 violations, Lighthouse ≥ 90/95/90/90, Visual regression 0 diffs | WCAG AA certificado |

---

## 📝 NOTAS PARA PRÓXIMAS IAs

1. **Leer en orden:** `Especificaciones.md` → `Plan_de_trabajo.md` → Este documento
2. **Tokens first:** Nunca añadas `bg-indigo-600` — usa `bg-brand-600`. Si falta token, añádelo a `tailwind.config.js` PRIMERO
3. **Componentes:** Todo patrón UI repetido → `x-ui.*`. Ver `resources/views/components/ui/`
4. **Commits atómicos:** Un commit = una cosa. Mensajes convencionales obligatorios
5. **Tests:** Cada componente nuevo = test visual (Storybook) + test unitario (Pest) + test feature (si aplica)
6. **Accesibilidad:** No es opcional. Cada PR debe pasar `axe-core` en CI
7. **Performance:** Budget estricto. `npm run build` y revisa `dist/` sizes
8. **Documentación:** Actualiza `docs/` en el mismo PR que el código

---

## 🔗 ENLACES DE REFERENCIA INTERNA

- **Especificaciones visuales:** `Especificaciones.md` (líneas 600-1500: landing prototype; 1100+: admin mobile prototype)
- **Plan trabajo actual:** `Plan_de_trabajo.md` (sección 13.10.3 mejoras UI/UX completadas)
- **MVP Laravel:** `mvp/` — código base actual
- **Componentes existentes:** `mvp/resources/views/components/` — migrar a `x-ui.*`
- **Vistas admin:** `mvp/resources/views/{dashboard,products,categories,orders,store,profile}/`
- **Vistas públicas:** `mvp/resources/views/{stores/public,cart}/`

---

*Documento generado para desarrollo colaborativo multi-IA — TuBase Express — Nov 2026*
- [ ] No hay valores hardcoded de color en nuevos componentes