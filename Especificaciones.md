
# 🚀 SYSTEM_PROMPT_DEV: Especificaciones Técnicas y Directivas de Desarrollo
**Proyecto:** SaaS Multi-tenant de Comercio por WhatsApp (WhatsApp Commerce)  
**Objetivo:** Desarrollar una plataforma SaaS ligera, ultra-rápida y de bajo costo que permita a pequeños comerciantes gestionar su catálogo de productos y recibir pedidos formateados directamente en su chat de WhatsApp.

---

## 1. Stack Tecnológico Estándar

El desarrollo debe ceñirse estrictamente a la siguiente pila tecnológica para asegurar máximo rendimiento, bajo consumo de recursos de servidor y despliegue económico:

| Capa | Tecnología Seleccionada | Detalle / Configuración |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 11.x (PHP 8.3+) | Routing por subdominio, Scopes globales de Tenancy, Queues. |
| **Frontend Comerciante** | Laravel Livewire 3 + Alpine.js | Reactividad sin API REST dedicada, ultra-ligero para móvil. |
| **Frontend Comprador** | Blade Templates + Tailwind CSS + Alpine.js | Renderizado SSR ultra-rápido con almacenamiento del carrito en LocalStorage. |
| **Base de Datos** | PostgreSQL 16 | Estrategia Single-Database con discriminador `tenant_id`. |
| **Almacenamiento Medios** | S3 API Compatible (Cloudflare R2) | Optimización e imágenes en formato `.webp`. |
| **Estilos / UI** | Tailwind CSS v3.x | Diseño *Mobile-First* adaptado a pantallas táctiles. |

---

## 2. Arquitectura de Base de Datos y Tenancy Strategy

### 2.1 Estrategia de Inquilinos (Tenancy)
* **Modelo:** Single Database Multi-Tenancy mediante columna de aislamiento `tenant_id` (UUID).
* **Aislamiento de Datos:** Utilizar un **Global Scope** en Eloquent (`TenantScope`) para inyectar automáticamente `WHERE tenant_id = ?` en todas las consultas de los modelos dependientes.

### 2.2 Esquema Relacional de Base de Datos

```sql
-- Tablas Principales de la Aplicación

CREATE TABLE tenants (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    domain_slug VARCHAR(63) UNIQUE NOT NULL, -- subdominio ej: "tiendita"
    business_name VARCHAR(100) NOT NULL,
    whatsapp_number VARCHAR(20) NOT NULL,    -- formato internacional sin "+" (ej: 584120000000)
    currency_symbol VARCHAR(5) DEFAULT '$',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id UUID REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id UUID NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id UUID NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    category_id UUID REFERENCES categories(id) ON DELETE SET NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255),
    is_available BOOLEAN DEFAULT true,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id UUID NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    customer_name VARCHAR(100),
    customer_phone VARCHAR(20),
    total_amount DECIMAL(10, 2) NOT NULL,
    payload JSONB NOT NULL, -- Detalle de items pedidos para analíticas
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

```

---

## 3. Lógica de Enrutamiento y Middleware por Subdominio

1. **Detección de Subdominio (`TenantMiddleware`):**
* Extraer el subdominio de la petición HTTP (`{subdomain}.tubase.com`).
* Si el subdominio es `www` o no existe subdominio: servir la **Landing Page del SaaS**.
* Si existe subdominio: buscar el registro activo en la tabla `tenants` por `domain_slug`.
* Si no existe o está inactivo: retornar error HTTP 404.
* Si existe: registrar el `tenant` en el contenedor de servicios de Laravel (`app()->instance('currentTenant', $tenant)`).



---

## 4. Construcción del Carrito y Disparo a WhatsApp

### 4.1 Carrito en Cliente (Frontend)

* El catálogo no debe saturar la sesión PHP ni la base de datos con carritos temporales.
* Todo el estado del carrito (`items`, `cantidades`, `totales`) se gestiona en la vista cliente con **Alpine.js** y se persiste en **`localStorage`**.

### 4.2 Lógica de Enlace WhatsApp (JavaScript Script)

Al procesar la compra, la IA debe implementar la construcción del mensaje y redirección:

```javascript
function checkoutWhatsApp(tenantPhone, businessName, cartItems, total, customerData) {
    let message = `*¡Hola! Quisiera realizar el siguiente pedido en ${businessName}:*\n\n`;
    
    cartItems.forEach(item => {
        message += `• ${item.quantity}x ${item.name} - $${(item.price * item.quantity).toFixed(2)}\n`;
    });
    
    message += `\n*Total a pagar:* $${total.toFixed(2)}`;
    
    if (customerData.deliveryType) {
        message += `\n*Método:* ${customerData.deliveryType}`;
    }
    if (customerData.name) {
        message += `\n*Cliente:* ${customerData.name}`;
    }
    if (customerData.notes) {
        message += `\n*Notas:* ${customerData.notes}`;
    }

    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = `[https://api.whatsapp.com/send?phone=$](https://api.whatsapp.com/send?phone=$){tenantPhone}&text=${encodedMessage}`;
    
    // Opcional: Enviar copia asíncrona vía fetch a /api/orders para estadísticas del comerciante
    
    window.open(whatsappUrl, '_blank');
}

```

---

## 5. Directivas de Desarrollo para la IA

Cuando programes componentes o módulos para este proyecto, sigue estas reglas estrictas:

1. **Mobile-First por Defecto:**
* El 95% de los compradores accederán desde teléfonos móviles.
* Las imágenes de productos deben cargarse de forma perezosa (`loading="lazy"`).
* La interfaz de administración del comerciante debe estar completamente optimizada para ser usada desde la pantalla de un smartphone.


2. **Procesamiento de Imágenes Obligatorio:**
* Al subir imágenes de productos en el panel, convertirlas automáticamente a formato `.webp` y reescalarlas a una resolución máxima de 800x800 px (calidad 80%) utilizando `spatie/laravel-image-optimizer` o `Intervention Image`.


3. **Seguridad y Tenancy Leakage:**
* **NUNCA** ejecutar consultas SQL puras o Eloquent sin el scope de `tenant_id`.
* Verificar en los Form Requests que el `category_id` enviado al crear/editar un producto pertenezca efectivamente al mismo `tenant_id` autenticado.


4. **Diseño Visual:**
* Utilizar componentes limpios de Tailwind CSS.
* La tienda del cliente final debe ser ultra-intuitiva: botón flotante del carrito, categorías fijas o deslizables horizontalmente y botones de "Agregar" de fácil acceso táctil (+ / -).



---

## 6. Siguientes Fases de Construcción

* [ ] **Fase 1:** Configuración de Laravel 11, Migraciones SQL y Middleware de Tenancy.
* [ ] **Fase 2:** Panel del Comerciante (Livewire): CRUD de Categorías, CRUD de Productos, Configuración de Perfil (Nombre, WhatsApp, Moneda).
* [ ] **Fase 3:** Catálogo Público por Subdominio (Blade + Alpine.js): Vista de Productos, Modal de Detalle, Carrito en LocalStorage y Botón WhatsApp.
* [ ] **Fase 4:** Landing Page SaaS y Registro Autogestionado de Vendedores.


# 📱 Especificaciones de Interfaz y UX: Panel de Administración del Comerciante

**Enfoque Principal:** *Mobile-First*. El 90% de los comerciantes administrarán su tienda desde un teléfono inteligente mientras realizan sus labores diarias. Toda la interfaz debe ser navegable con una sola mano y requerir la menor cantidad de toques (*taps*) posible.

---

## 1. Principios de Diseño UX

1. **Velocidad de carga inmediata:** Componentes reactivos sin recargas de página completas (vía Livewire 3 / Alpine.js).
2. **Sin menús complejos:** Navegación inferior fija (*Bottom Navigation Bar*) estilo app nativa.
3. **Flujo "Cero Fricción" para productos:** Proceso de creación de producto optimizado a un máximo de **2 toques principales**.
4. **Acciones táctiles grandes:** Botones y switchers con un área táctil mínima de `48x48px` para evitar clics erróneos en pantallas táctiles.

---

## 2. Estructura de Navegación Móvil (Bottom Nav Bar)

La pantalla principal del comerciante contará con una barra inferior fija con 4 secciones clave:

```
┌─────────────────────────────────────────────────────────┐
│  [📦 Productos]   [🏷️ Categorías]  [📊 Pedidos]  [⚙️ Ajustes] │
└─────────────────────────────────────────────────────────┘
```

---

## 3. Flujo de Trabajo Extremo: "Subir Producto en 2 Clics"

### El Problema
Los comerciantes informales no tienen tiempo para llenar formularios complejos (SKU, peso, dimensiones, impuestos, etc.). Necesitan publicar productos tan rápido como suben un estado a WhatsApp.

### La Solución (Drawer Inferior / Bottom Sheet Modal)

```
┌─────────────────────────────────────────────────────────┐
│ 🗂️ NUEVO PRODUCTO                          [ Guardar ] │
├─────────────────────────────────────────────────────────┤
│ 📸 [ Tap para Tomar Foto / Elegir de Galería ]          │
│                                                         │
│ 📝 Nombre del Producto: [Ej. Harina PAN 1kg           ] │
│ 💵 Precio ($):          [Ej. 1.20                     ] │
│ 🏷️ Categoría:         [ Alimentos               ▼ ]     │
│                                                         │
│ 🔘 [✓] Disponible inmediatamente                        │
└─────────────────────────────────────────────────────────┘
```

### Paso a Paso Simplificado:
1. **Toque 1:** El comerciante presiona el botón flotante principal **`[ + Nuevo ]`** (fijo en la esquina inferior derecha).
2. **Toque 2:** Presiona la zona de imagen para abrir la cámara o galería y seleccionar la foto.
3. **Completar datos rápidos:** 
   * **Nombre:** Campo auto-enfocado al abrir.
   * **Precio:** Despliega teclado numérico por defecto (`input type="decimal"`).
   * **Categoría:** Desplegable rápido (selecciona por defecto la última categoría utilizada).
4. **Guardar:** Botón **`[ Publicar ]`** que comprime la imagen automáticamente a WebP en segundo plano y actualiza la lista al instante.

---

## 4. Pantallas Principales del Panel

### 4.1. Dashboard de Productos (`/admin/products`)
* **Buscador en tiempo real:** Filtrado por palabra clave sin recargar.
* **Filtro de Categorías:** Chips horizontales deslizables (`Todos`, `Alimentos`, `Bebidas`, `Ofertas`).
* **Tarjetas de Producto Compactas:**
  * Miniatura de la imagen (`64x64px`).
  * Nombre del producto y precio destacado.
  * **Switch "Disponible / Agotado" (Quick Toggle):** Permite ocultar o marcar como agotado un producto con un solo toque cuando se acaba el stock.
  * Botones de acción rápida: *Editar* | *Eliminar*.

### 4.2. Configuración del Negocio (`/admin/settings`)
Formulario simple en una sola vista:
* **Nombre de la Tienda:** Nombre público.
* **Subdominio:** `mitienda.tubase.com` (con indicador visual de disponibilidad en tiempo real).
* **Número de WhatsApp:** Formato internacional validado para asegurar la recepción de pedidos.
* **Símbolo de Moneda:** Selección rápida ($, Bs, €, S/., etc.).
* **Indicaciones de Entrega:** Texto corto para el cliente (Ej: *"Entregas en la garita del condominio de 2pm a 6pm"*).

---

## 5. Directivas de UI y Estilos Tailwind CSS

La IA debe usar las siguientes clases de Tailwind CSS para garantizar la usabilidad móvil:

* **Contenedor Principal:** `max-w-md mx-auto min-h-screen bg-slate-50 pb-20` (Optimizado para pantalla móvil).
* **Botón Flotante de Acción (FAB):** `fixed bottom-20 right-4 bg-emerald-600 text-white p-4 rounded-full shadow-lg hover:bg-emerald-700 z-40`.
* **Tarjetas de Producto:** `bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between gap-3`.
* **Inputs Adaptados a Móvil:** `w-full px-4 py-3 rounded-lg border-slate-200 text-base focus:ring-emerald-500 focus:border-emerald-500` (Usar `text-base` previene el zoom automático molesto en iOS).


# 🌐 Especificaciones de Landing Page de Venta (SaaS Core)

**Objetivo:** Convertir visitantes (vendedores por WhatsApp, comerciantes de condominio, emprendedores locales) en usuarios registrados en menos de 2 minutos.

**Dominio Principal:** `tubase.com` / `www.tubase.com`

---

## 1. Estructura del Funnel de Conversión

```
┌───────────────────────────────────────────────────────────┐
│ 1. HERO SECTION: Propuesta de valor + CTA Principal      │
├───────────────────────────────────────────────────────────┤
│ 2. DEMO INTERACTIVA: Vista previa de catálogo y mensaje   │
├───────────────────────────────────────────────────────────┤
│ 3. PROBLEMA VS SOLUCIÓN: Comparativa visual               │
├───────────────────────────────────────────────────────────┤
│ 4. CÓMO FUNCIONA: 3 Pasos sencillos                       │
├───────────────────────────────────────────────────────────┤
│ 5. PLANES Y PRECIOS: Transparente y de bajo costo          │
├───────────────────────────────────────────────────────────┤
│ 6. PREGUNTAS FRECUENTES (FAQ)                             │
├───────────────────────────────────────────────────────────┤
│ 7. FOOTER + CTA FINAL DE REGISTRO                         │
└───────────────────────────────────────────────────────────┘
```

---

## 2. Contenido y Copywriting por Sección

### 2.1. Hero Section
* **Titular Principal (H1):**
  > *"Convierte tus chats de WhatsApp en ventas organizadas con tu propia tienda web express."*

* **Subtítulo:**
  > Olvídate de enviar fotos sueltas o PDFs pesados. Permite que tus clientes armen su carrito en segundos y recibas sus pedidos estructurados directamente en tu WhatsApp.

* **Call to Action (CTA) Principal:**
  * Botón destacado: `[ 🚀 Crear mi Tienda Gratis ]`
  * Micro-copy de confianza: *"Sin tarjeta de crédito • Configuración en 3 minutos"*.

* **Elemento Visual:** Mockup o demo interactivo que muestre un smartphone navegando por la web, agregando productos al carrito y abriendo WhatsApp con la orden formateada.

### 2.2. Cuadro Comparativo: "La Forma Antigua vs. Tu Tienda Express"

| La Forma Antigua ❌ | Con Tu Tienda Express ⚡ |
| :--- | :--- |
| Enviar 20 fotos sueltas que llenan la memoria del cliente | Un solo enlace profesional (`mitienda.tubase.com`) |
| Responder *"¿Precio e información?"* 50 veces al día | Catálogo visible y siempre actualizado con precios transparentes |
| Pedidos desordenados o mal anotados | Mensaje claro en WhatsApp con la lista exacta de productos y el total calculado |
| Pérdida de tiempo y clientes confundidos | Experiencia de carrito de compras rápida e intuitiva |

### 2.3. ¿Cómo Funciona? (En 3 Pasos)

1. **Crea tu cuenta:** Elige el nombre de tu tienda y tu subdominio en 1 minuto.
2. **Sube tus productos:** Sube fotos y precios directamente desde tu teléfono.
3. **Comparte tu enlace:** Agrégalo a tu perfil de Instagram, estados de WhatsApp o envíalo a los grupos de tu condominio.

### 2.4. Estrategia de Precios (Pricing Table)

#### 🟢 Plan Prueba / Gratuito
* Hasta 10 productos.
* Pedidos ilimitados por WhatsApp.
* Subdominio `tu-nombre.tubase.com`.
* **Precio:** $0 / mes.

#### 🔵 Plan Emprendedor Express
* Productos e imágenes ilimitados.
* Categorías ilimitadas.
* **0% comisiones por venta** (el 100% de tu dinero es tuyo).
* Panel de administración móvil ultrarrápido.
* Soporte directo por WhatsApp.
* **Precio:** $3 - $5 USD / mes (o descuento por pago anual).

---

## 3. Flujo de Registro / Onboarding (`/register`)

El formulario de registro para nuevos vendedores debe requerir únicamente:

1. **Nombre de tu Negocio:** (Ej. *Postres Doña María*).
2. **Subdominio Deseado:** `postresmari.tubase.com` (Validación de disponibilidad en tiempo real).
3. **Tu WhatsApp de Ventas:** (Donde llegarán los pedidos formateados).
4. **Correo electrónico y Contraseña**.

Al presionar **"Crear mi Tienda"**, el sistema debe:
* Crear automáticamente el registro en la tabla `tenants`.
* Iniciar sesión al usuario.
* Redirigir directamente al panel móvil para que suba su primer producto.


# *landing_page_saas_tubase_express.html**

Ejemplo o modelo para ajustar lo necesario

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuBase Express - Tu Tienda Web en 3 Minutos para Ventas por WhatsApp</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(15, 23, 42, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .bg-grid-pattern-dark {
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased selection:bg-brand-500 selection:text-white" x-data="saasLanding()">

    <!-- Sticky Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="#" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center text-white font-black text-xl shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                    ⚡
                </div>
                <div>
                    <span class="font-extrabold text-xl tracking-tight text-slate-900">TuBase<span class="text-brand-600">Express</span></span>
                    <span class="block text-[10px] font-semibold tracking-wider text-slate-400 uppercase -mt-1">WhatsApp Commerce</span>
                </div>
            </a>

            <!-- Desktop Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                <a href="#como-funciona" class="hover:text-brand-600 transition">¿Cómo funciona?</a>
                <a href="#comparativa" class="hover:text-brand-600 transition">Ventajas</a>
                <a href="#demo" class="hover:text-brand-600 transition">Demo en Vivo</a>
                <a href="#precios" class="hover:text-brand-600 transition">Precios</a>
                <a href="#faq" class="hover:text-brand-600 transition">FAQ</a>
            </nav>

            <!-- CTA Actions -->
            <div class="flex items-center gap-3">
                <button @click="openRegisterModal()" class="hidden sm:inline-flex text-xs sm:text-sm font-semibold text-slate-700 hover:text-slate-900 px-3 py-2 transition">
                    Iniciar Sesión
                </button>
                <button @click="openRegisterModal()" class="bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 transition active:scale-95 flex items-center gap-1.5">
                    <span>Crear Tienda Gratis</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="relative pt-12 pb-20 md:pt-20 md:pb-32 overflow-hidden bg-grid-pattern">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                
                <div class="grid lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    
                    <!-- Left Column: Copywriting -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        
                        <!-- Top Badge -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-100/80 border border-emerald-200 text-brand-700 text-xs font-semibold shadow-xs">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>⚡ Diseñado para vendedores por WhatsApp y condominios</span>
                        </div>

                        <!-- H1 Title -->
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.15]">
                            Convierte tus chats de WhatsApp en <span class="bg-clip-text text-transparent bg-gradient-to-r from-brand-600 via-emerald-500 to-teal-600">ventas organizadas</span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-normal">
                            Olvídate de enviar 20 fotos sueltas o PDFs pesados. Permite que tus clientes armen su carrito en segundos y recibas sus pedidos estructurados directamente en tu WhatsApp.
                        </p>

                        <!-- CTA & Trust Badges -->
                        <div class="pt-2 space-y-4">
                            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
                                <button @click="openRegisterModal()" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white font-bold text-base px-8 py-4 rounded-2xl shadow-xl shadow-slate-900/20 hover:shadow-2xl transition active:scale-95 flex items-center justify-center gap-2">
                                    <i data-lucide="rocket" class="w-5 h-5 text-emerald-400"></i>
                                    <span>Crear Mi Tienda Gratis</span>
                                </button>
                                
                                <a href="#demo" class="w-full sm:w-auto bg-white hover:bg-slate-100 text-slate-700 font-semibold text-base px-6 py-4 rounded-2xl border border-slate-200 shadow-sm transition flex items-center justify-center gap-2">
                                    <i data-lucide="play-circle" class="w-5 h-5 text-slate-400"></i>
                                    <span>Ver Demo interactivo</span>
                                </a>
                            </div>

                            <!-- Micro Copy -->
                            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-y-2 gap-x-6 text-xs text-slate-500 font-medium">
                                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Sin tarjeta de crédito</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Listo en 3 minutos</span>
                                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> 0% comisiones por venta</span>
                            </div>
                        </div>

                        <!-- Stats Banner -->
                        <div class="pt-6 border-t border-slate-200/80 grid grid-cols-3 gap-4 max-w-lg mx-auto lg:mx-0 text-center lg:text-left">
                            <div>
                                <p class="text-2xl font-black text-slate-900">3 Min</p>
                                <p class="text-xs text-slate-500 font-medium">Configuración inicial</p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-slate-900">100%</p>
                                <p class="text-xs text-slate-500 font-medium">Tu dinero directo a ti</p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-slate-900">0$</p>
                                <p class="text-xs text-slate-500 font-medium">Para comenzar hoy</p>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Interactive Mockup Showcase -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative w-full max-w-[340px]">
                            <!-- Glow Background Effect -->
                            <div class="absolute -inset-4 bg-gradient-to-tr from-brand-500 to-teal-400 rounded-[40px] blur-2xl opacity-30 animate-pulse"></div>

                            <!-- Smartphone Device Frame -->
                            <div class="relative bg-slate-900 p-3 rounded-[40px] shadow-2xl ring-1 ring-slate-900/10">
                                <!-- Top Notch / Camera -->
                                <div class="w-28 h-4 bg-slate-800 rounded-full mx-auto mb-2 flex items-center justify-center">
                                    <div class="w-3 h-3 rounded-full bg-slate-900"></div>
                                </div>

                                <!-- Screen Content -->
                                <div class="bg-slate-100 rounded-[30px] overflow-hidden text-slate-800 text-left text-xs font-sans shadow-inner">
                                    <!-- Store Header -->
                                    <div class="bg-slate-900 text-white p-3.5 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-500 flex items-center justify-center font-bold text-white text-xs">
                                                MA
                                            </div>
                                            <div>
                                                <p class="font-bold text-xs leading-none">Postres Doña María</p>
                                                <p class="text-[9px] text-emerald-400 font-mono mt-0.5">postresmari.tubase.com</p>
                                            </div>
                                        </div>
                                        <span class="bg-emerald-500/20 text-emerald-300 text-[9px] font-bold px-2 py-0.5 rounded-full border border-emerald-500/30">Abierto</span>
                                    </div>

                                    <!-- Product List in Mockup -->
                                    <div class="p-3 space-y-2 max-h-[280px] overflow-hidden bg-slate-50">
                                        
                                        <!-- Item 1 -->
                                        <div class="bg-white p-2 rounded-xl border border-slate-200 flex items-center justify-between gap-2 shadow-2xs">
                                            <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=100&auto=format&fit=crop&q=80" class="w-10 h-10 rounded-lg object-cover">
                                            <div class="flex-1 min-w-0">
                                                <p class="font-bold text-slate-800 truncate">Torta Marquesa 1kg</p>
                                                <p class="text-brand-600 font-black">$12.50</p>
                                            </div>
                                            <button class="bg-slate-900 text-white px-2 py-1 rounded-lg text-[10px] font-bold flex items-center gap-0.5">
                                                <span>+</span> Add
                                            </button>
                                        </div>

                                        <!-- Item 2 -->
                                        <div class="bg-white p-2 rounded-xl border border-slate-200 flex items-center justify-between gap-2 shadow-2xs">
                                            <img src="https://images.unsplash.com/photo-1528975604071-b4dc52a2d18c?w=100&auto=format&fit=crop&q=80" class="w-10 h-10 rounded-lg object-cover">
                                            <div class="flex-1 min-w-0">
                                                <p class="font-bold text-slate-800 truncate">Quesillo Tradicional</p>
                                                <p class="text-brand-600 font-black">$8.00</p>
                                            </div>
                                            <button class="bg-emerald-600 text-white px-2 py-1 rounded-lg text-[10px] font-bold">
                                                ✓ 1
                                            </button>
                                        </div>

                                        <!-- Simulated WhatsApp Message Overlay -->
                                        <div class="mt-2 bg-emerald-50 border border-emerald-200 p-2.5 rounded-xl shadow-md space-y-1 transform translate-y-1">
                                            <div class="flex items-center gap-1 text-emerald-800 font-bold text-[10px]">
                                                <i data-lucide="message-circle" class="w-3 h-3 text-emerald-600"></i>
                                                <span>Mensaje recibido en WhatsApp:</span>
                                            </div>
                                            <p class="text-[9.5px] font-mono text-slate-700 leading-tight bg-white p-1.5 rounded border border-emerald-100">
                                                *Pedido #1001 en Postres Doña María:*<br>
                                                • 1x Marquesa Chocolate ($12.50)<br>
                                                *Total:* $12.50
                                            </p>
                                        </div>

                                    </div>

                                    <!-- Mockup Floating Button -->
                                    <div class="p-2.5 bg-white border-t border-slate-200">
                                        <div class="bg-emerald-600 text-white py-2 px-3 rounded-xl flex items-center justify-between font-bold text-xs shadow-sm">
                                            <span>Ver Pedido (1 item)</span>
                                            <span>$12.50 →</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Section: Problem vs. Solution -->
        <section id="comparativa" class="py-16 md:py-24 bg-white border-y border-slate-200/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-brand-50 inline-block px-3 py-1 rounded-full border border-brand-100">¿Por qué cambiar?</h2>
                    <p class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">La Forma Antigua vs. Tu Tienda Express</p>
                    <p class="text-slate-600 text-sm sm:text-base">Mira la diferencia abismal en la experiencia de compra de tus clientes.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-8 items-stretch">
                    
                    <!-- The Old Way (Red / Negative) -->
                    <div class="bg-rose-50/50 border border-rose-200/80 rounded-3xl p-6 sm:p-8 space-y-6 relative overflow-hidden">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-bold">
                            ❌ La Forma Antigua (Manual)
                        </div>

                        <ul class="space-y-4 text-sm text-slate-700">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-rose-200 text-rose-700 flex items-center justify-center flex-shrink-0 font-bold text-xs">✕</div>
                                <span>Enviar 20 fotos sueltas que saturan la memoria del teléfono de tu cliente.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-rose-200 text-rose-700 flex items-center justify-center flex-shrink-0 font-bold text-xs">✕</div>
                                <span>Responder 50 veces al día *"¿Cuál es el precio y la información?"*.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-rose-200 text-rose-700 flex items-center justify-center flex-shrink-0 font-bold text-xs">✕</div>
                                <span>Pedidos desordenados en texto plano donde faltan datos o cantidades.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-rose-200 text-rose-700 flex items-center justify-center flex-shrink-0 font-bold text-xs">✕</div>
                                <span>Clientes que se van sin comprar por confusión o respuesta tardía.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Tu Tienda Express (Green / Positive) -->
                    <div class="bg-emerald-50/60 border-2 border-emerald-400/80 rounded-3xl p-6 sm:p-8 space-y-6 relative shadow-lg shadow-emerald-500/5">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500 text-white text-xs font-bold shadow-xs">
                            ⚡ Con Tu Tienda Express
                        </div>

                        <ul class="space-y-4 text-sm text-slate-800 font-medium">
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 font-bold text-xs">✓</div>
                                <span>Un único enlace elegante y profesional: <code class="bg-emerald-100 text-emerald-900 px-1.5 py-0.5 rounded font-mono text-xs">mitienda.tubase.com</code>.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 font-bold text-xs">✓</div>
                                <span>Catálogo siempre actualizado con precios e imágenes claras.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 font-bold text-xs">✓</div>
                                <span>Llega a tu WhatsApp la lista exacta con el cálculo total automático.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 font-bold text-xs">✓</div>
                                <span>Cierre de ventas instantáneo y experiencia fluida para tu comprador.</span>
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </section>

        <!-- Section: How It Works -->
        <section id="como-funciona" class="py-16 md:py-24 bg-slate-900 text-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400 bg-emerald-950/80 inline-block px-3 py-1 rounded-full border border-emerald-800">Cero Complicaciones</h2>
                    <p class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">Publica tu tienda en 3 simples pasos</p>
                    <p class="text-slate-400 text-sm sm:text-base">Sin necesidad de conocimientos de programación ni diseño web.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    
                    <!-- Step 1 -->
                    <div class="bg-slate-800/80 border border-slate-700/80 p-6 sm:p-8 rounded-3xl relative space-y-4 hover:border-emerald-500/50 transition">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black text-xl border border-emerald-500/30">
                            1
                        </div>
                        <h3 class="text-lg font-bold text-white">Crea tu cuenta</h3>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Elige el nombre de tu negocio, tu subdominio personalizado y tu número de WhatsApp de recepción.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-slate-800/80 border border-slate-700/80 p-6 sm:p-8 rounded-3xl relative space-y-4 hover:border-emerald-500/50 transition">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black text-xl border border-emerald-500/30">
                            2
                        </div>
                        <h3 class="text-lg font-bold text-white">Sube tus productos</h3>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Toma fotos desde tu celular, asigna precio y marca la disponibilidad con un solo toque.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-slate-800/80 border border-slate-700/80 p-6 sm:p-8 rounded-3xl relative space-y-4 hover:border-emerald-500/50 transition">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-black text-xl border border-emerald-500/30">
                            3
                        </div>
                        <h3 class="text-lg font-bold text-white">Comparte tu enlace</h3>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Copia tu link y colócalo en tu Instagram, perfil de WhatsApp o compártelo en tus grupos de condominio.
                        </p>
                    </div>

                </div>

                <div class="mt-12 text-center">
                    <button @click="openRegisterModal()" class="bg-brand-500 hover:bg-brand-600 text-slate-900 font-extrabold text-base px-8 py-4 rounded-2xl shadow-xl shadow-brand-500/20 transition active:scale-95">
                        ¡Comenzar Ahora Mismo! →
                    </button>
                </div>

            </div>
        </section>

        <!-- Section: Interactive Demo Tester -->
        <section id="demo" class="py-16 md:py-24 bg-slate-100 border-b border-slate-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                
                <div class="text-center space-y-3 mb-10">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-white inline-block px-3 py-1 rounded-full border border-slate-200">Prueba Interactiva</h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900">Simula el enlace de tu futuro negocio</p>
                    <p class="text-xs sm:text-sm text-slate-600">Escribe el nombre de tu emprendimiento para ver cómo lucirá tu tienda web:</p>
                </div>

                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xl space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">Nombre de tu Negocio:</label>
                        <div class="flex items-center bg-slate-50 border border-slate-300 rounded-2xl overflow-hidden focus-within:ring-2 focus-within:ring-brand-500/30">
                            <input type="text" x-model="demoStoreName" @input="updateDemoSubdomain()" placeholder="Ej. Repostería Sofía" class="w-full bg-transparent px-4 py-3 text-sm outline-none font-medium">
                            <span class="bg-slate-200 text-slate-700 px-4 py-3 text-xs font-mono font-bold border-l border-slate-300 hidden sm:inline">.tubase.com</span>
                        </div>
                    </div>

                    <!-- Live URL Card -->
                    <div class="bg-slate-900 text-white p-4 rounded-2xl flex items-center justify-between gap-2">
                        <div class="overflow-hidden">
                            <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Tu Enlace Web Listo:</p>
                            <p class="text-sm font-mono text-emerald-400 font-bold truncate" x-text="demoSubdomain + '.tubase.com'"></p>
                        </div>
                        <button @click="openRegisterModal()" class="bg-brand-500 hover:bg-brand-400 text-slate-950 text-xs font-extrabold px-4 py-2 rounded-xl whitespace-nowrap transition">
                            Reclamar Link
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- Section: Pricing -->
        <section id="precios" class="py-16 md:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-12 space-y-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-brand-50 inline-block px-3 py-1 rounded-full border border-brand-100">Precios Transparentes</h2>
                    <p class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Planes accesibles para emprendedores</p>
                    <p class="text-slate-600 text-sm sm:text-base">Sin costos ocultos. Sin comisiones por tus ventas. Todo el dinero es tuyo.</p>

                    <!-- Billing Cycle Toggle -->
                    <div class="pt-4 flex items-center justify-center gap-3">
                        <span class="text-xs font-semibold" :class="!annualBilling ? 'text-slate-900' : 'text-slate-400'">Pago Mensual</span>
                        <button @click="annualBilling = !annualBilling" class="relative inline-flex h-6 w-12 items-center rounded-full bg-slate-300 transition">
                            <span :class="annualBilling ? 'translate-x-6 bg-brand-600' : 'translate-x-1 bg-white'" class="inline-block h-4 w-4 transform rounded-full transition"></span>
                        </button>
                        <span class="text-xs font-semibold" :class="annualBilling ? 'text-slate-900' : 'text-slate-400'">
                            Pago Anual <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full">Ahorra 25%</span>
                        </span>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto items-stretch">
                    
                    <!-- Free Plan -->
                    <div class="bg-slate-50 border border-slate-200 rounded-3xl p-8 flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Plan Gratuito</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-slate-900">$0</span>
                                <span class="text-slate-500 text-xs font-medium">/ para siempre</span>
                            </div>
                            <p class="text-xs text-slate-600">Ideal para probar la plataforma o emprendimientos muy pequeños.</p>

                            <ul class="space-y-3 text-xs text-slate-700 pt-4 border-t border-slate-200">
                                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-600"></i> Hasta 10 productos</li>
                                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-600"></i> Pedidos ilimitados por WhatsApp</li>
                                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-600"></i> Subdominio <code class="font-mono text-[10px]">tu-nombre.tubase.com</code></li>
                                <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-600"></i> Panel móvil ultrarrápido</li>
                            </ul>
                        </div>

                        <button @click="openRegisterModal()" class="w-full bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold py-3.5 rounded-xl text-xs transition">
                            Crear Tienda Gratis
                        </button>
                    </div>

                    <!-- Premium Plan -->
                    <div class="bg-slate-900 text-white border-2 border-brand-500 rounded-3xl p-8 flex flex-col justify-between space-y-6 relative shadow-2xl">
                        <div class="absolute -top-3.5 right-6 bg-brand-500 text-slate-950 text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-wider">
                            Recomendado
                        </div>

                        <div class="space-y-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Plan Emprendedor Express</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-white" x-text="annualBilling ? '$3' : '$4.99'">$4.99</span>
                                <span class="text-slate-400 text-xs font-medium">/ mes</span>
                            </div>
                            <p class="text-xs text-slate-400">Para negocios que desean vender sin límites ni restricciones.</p>

                            <ul class="space-y-3 text-xs text-slate-300 pt-4 border-t border-slate-800">
                                <li class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i> <strong>Productos e imágenes ilimitadas</strong></li>
                                <li class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i> Categorías ilimitadas</li>
                                <li class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i> <strong>0% comisiones por venta</strong></li>
                                <li class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i> Soporte prioritario por WhatsApp</li>
                                <li class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i> Estadísticas e historial de pedidos</li>
                            </ul>
                        </div>

                        <button @click="openRegisterModal()" class="w-full bg-brand-500 hover:bg-brand-400 text-slate-950 font-extrabold py-3.5 rounded-xl text-xs transition shadow-lg shadow-brand-500/20">
                            Comenzar Prueba de 14 Días
                        </button>
                    </div>

                </div>

            </div>
        </section>

        <!-- Section: FAQ Accordion -->
        <section id="faq" class="py-16 md:py-24 bg-slate-50 border-t border-slate-200">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">
                
                <div class="text-center space-y-3 mb-12">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-brand-600 bg-white inline-block px-3 py-1 rounded-full border border-slate-200">Dudas Frecuentes</h2>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900">Preguntas Frecuentes</p>
                </div>

                <div class="space-y-3">
                    <template x-for="(faq, index) in faqs" :key="index">
                        <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-2xs">
                            <button @click="openFaq = openFaq === index ? null : index" class="w-full p-4 sm:p-5 text-left font-bold text-slate-800 text-sm flex justify-between items-center gap-4">
                                <span x-text="faq.q"></span>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="openFaq === index ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="openFaq === index" x-collapse class="px-4 pb-5 sm:px-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3" x-text="faq.a">
                            </div>
                        </div>
                    </template>
                </div>

            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-2">
                <span class="text-xl">⚡</span>
                <span class="font-bold text-white text-sm">TuBase Express</span>
                <span>— Catálogos Web para Ventas por WhatsApp</span>
            </div>
            <div class="flex gap-6">
                <a href="#como-funciona" class="hover:text-white transition">Inicio</a>
                <a href="#precios" class="hover:text-white transition">Precios</a>
                <a href="#" @click.prevent="openRegisterModal()" class="hover:text-white transition">Crear Cuenta</a>
            </div>
            <p>© 2026 TuBase. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- REGISTRATION / ONBOARDING MODAL -->
    <div x-show="showRegisterModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs" style="display: none;">
        
        <div @click.away="showRegisterModal = false" class="bg-white w-full max-w-md rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden">
            
            <button @click="showRegisterModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Modal Header -->
            <div class="space-y-1 text-center">
                <div class="w-12 h-12 bg-brand-50 text-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-2 text-xl font-bold">
                    🚀
                </div>
                <h3 class="font-extrabold text-slate-900 text-xl">Crea tu Tienda Web Express</h3>
                <p class="text-xs text-slate-500">Sin tarjeta de crédito. Tu tienda estará lista en 3 minutos.</p>
            </div>

            <!-- Registration Form -->
            <form @submit.prevent="submitRegistration()" class="space-y-4">
                
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Nombre de tu Negocio *</label>
                    <input type="text" x-model="regForm.name" required @input="autoSlug()" placeholder="Ej. Postres Doña María" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Subdominio Deseado *</label>
                    <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-brand-500/20 focus-within:border-brand-500">
                        <input type="text" x-model="regForm.slug" required placeholder="postresmari" class="w-full bg-transparent px-3.5 py-2.5 text-sm outline-none font-mono text-slate-800">
                        <span class="bg-slate-200 text-slate-600 px-3 py-2.5 text-xs font-mono border-l border-slate-200">.tubase.com</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">WhatsApp de Recepción de Pedidos *</label>
                    <input type="text" x-model="regForm.whatsapp" required placeholder="Ej: 584121234567" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-mono outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <p class="text-[10px] text-slate-400">Código de país sin el símbolo +. Ej: 584120000000</p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700">Correo Electrónico *</label>
                    <input type="email" x-model="regForm.email" required placeholder="tu@correo.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-brand-500/20 active:scale-95 transition flex items-center justify-center gap-2 text-sm">
                    <span>Activar Mi Tienda Gratis →</span>
                </button>
            </form>

        </div>
    </div>

    <!-- Alpine.js Application Logic -->
    <script>
        function saasLanding() {
            return {
                annualBilling: false,
                openFaq: 0,
                showRegisterModal: false,
                demoStoreName: 'Postres Doña María',
                demoSubdomain: 'postresmari',

                regForm: {
                    name: '',
                    slug: '',
                    whatsapp: '',
                    email: ''
                },

                faqs: [
                    {
                        q: '¿Cómo cobran mis clientes y dónde recibo el dinero?',
                        a: 'El cliente no paga en la web. La web genera un resumen de compra empaquetado y abre un chat en tu WhatsApp. Tú gestionas el pago directamente con el comprador por Pago Móvil, Zelle, efectivo o transferencia. ¡El 100% del dinero va directo a tu cuenta!'
                    },
                    {
                        q: '¿Necesito conocimientos de desarrollo o diseño web?',
                        a: 'Para nada. El panel de administración fue diseñado para usarse desde tu teléfono móvil. Solo tomas la foto, colocas el nombre y precio, y tu producto queda publicado al instante.'
                    },
                    {
                        q: '¿Hay algún cobro de comisión por venta?',
                        a: 'Cero comisiones. A diferencia de las plataformas tradicionales que te cobran del 3% al 5% por transacción, en TuBase Express no pagas ninguna comisión por las ventas que realices.'
                    },
                    {
                        q: '¿Puedo usar mi subdominio personalizado?',
                        a: '¡Sí! Obtienes de inmediato tu enlace con formato mitienda.tubase.com para colocarlo en tu perfil de Instagram o enviarlo a tus clientes de condominio.'
                    }
                ],

                init() {
                    const renderIcons = () => {
                        if (typeof lucide !== 'undefined' && lucide.createIcons) {
                            lucide.createIcons();
                        }
                    };
                    this.$watch('showRegisterModal', () => this.$nextTick(renderIcons));
                    this.$watch('openFaq', () => this.$nextTick(renderIcons));
                    this.$nextTick(renderIcons);
                },

                updateDemoSubdomain() {
                    this.demoSubdomain = this.demoStoreName
                        .toLowerCase()
                        .trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '')
                        || 'mitienda';
                },

                autoSlug() {
                    this.regForm.slug = this.regForm.name
                        .toLowerCase()
                        .trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '');
                },

                openRegisterModal() {
                    this.showRegisterModal = true;
                },

                submitRegistration() {
                    alert(`¡Felicidades! Se ha creado con éxito la tienda "${this.regForm.name}" en ${this.regForm.slug}.tubase.com. Ahora serás redirigido al panel de administración móvil.`);
                    this.showRegisterModal = false;
                    window.location.href = 'index.html';
                }
            }
        }
    </script>
</body>
</html>


# *panel_comerciante_admin_mobile.html**

Ejemplo o modelo para ajustar lo necesario

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Panel de Administración - TuBase Express</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        /* Hide scrollbars for clean mobile feel */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex justify-center selection:bg-brand-500 selection:text-white" x-data="adminApp()">

    <!-- Main Mobile Container Simulator / Standalone Screen -->
    <div class="w-full max-w-md bg-slate-50 min-h-screen shadow-2xl relative flex flex-col border-x border-slate-200/80 pb-24">

        <!-- Top Header & Store Bar -->
        <header class="bg-slate-900 text-white px-4 pt-4 pb-3 sticky top-0 z-30 shadow-md">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center font-bold text-lg text-white shadow-sm ring-2 ring-brand-400/30">
                        <span x-text="store.name ? store.name.substring(0,2).toUpperCase() : 'MA'">MA</span>
                    </div>
                    <div class="overflow-hidden">
                        <h1 class="font-bold text-base leading-tight truncate" x-text="store.name">Postres Doña María</h1>
                        <a :href="'https://' + store.subdomain + '.tubase.com'" target="_blank" class="text-xs text-emerald-400 hover:text-emerald-300 flex items-center gap-1 font-mono">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span x-text="store.subdomain + '.tubase.com'">postresmari.tubase.com</span>
                            <i data-lucide="external-link" class="w-3 h-3 ml-0.5"></i>
                        </a>
                    </div>
                </div>

                <!-- Action Button: Open Preview -->
                <button @click="openStorePreview = true" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 p-2.5 rounded-xl text-xs font-medium flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="eye" class="w-4 h-4 text-emerald-400"></i>
                    <span class="hidden sm:inline">Ver Tienda</span>
                </button>
            </div>
        </header>

        <!-- Content Body (Tab Dependent) -->
        <main class="flex-1 p-4 space-y-4">

            <!-- TAB 1: PRODUCTOS -->
            <section x-show="activeTab === 'products'" class="space-y-4">
                
                <!-- Search & Filter Bar -->
                <div class="space-y-2">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                        <input type="text" x-model="searchQuery" placeholder="Buscar producto por nombre..." 
                            class="w-full bg-white border border-slate-200 pl-10 pr-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-sm">
                        <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Horizontal Category Filter Chips -->
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                        <button @click="selectedCategory = 'all'" 
                            :class="selectedCategory === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                            class="px-3.5 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition active:scale-95">
                            Todos (<span x-text="products.length">0</span>)
                        </button>
                        <template x-for="cat in categories" :key="cat.id">
                            <button @click="selectedCategory = cat.id" 
                                :class="selectedCategory === cat.id ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                class="px-3.5 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition active:scale-95">
                                <span x-text="cat.name"></span>
                                (<span x-text="products.filter(p => p.categoryId === cat.id).length"></span>)
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Product List Cards -->
                <div class="space-y-2.5">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="bg-white p-3 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-3 hover:border-slate-300 transition">
                            <!-- Image Thumbnail -->
                            <img :src="product.image || 'https://placehold.co/120x120/f1f5f9/94a3b8?text=Foto'" 
                                 :alt="product.name" 
                                 class="w-16 h-16 rounded-xl object-cover border border-slate-100 flex-shrink-0 bg-slate-100">
                            
                            <!-- Product Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded"
                                          x-text="getCategoryName(product.categoryId)">Categoría</span>
                                </div>
                                <h3 class="font-semibold text-slate-800 text-sm truncate leading-snug" x-text="product.name"></h3>
                                <p class="text-brand-600 font-bold text-base" x-text="store.currency + ' ' + parseFloat(product.price).toFixed(2)"></p>
                            </div>

                            <!-- Actions & Availability Switch (Quick Toggle) -->
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <!-- Quick Availability Toggle -->
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" :checked="product.isAvailable" @change="toggleAvailability(product)">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                                <span class="text-[10px] font-medium" :class="product.isAvailable ? 'text-emerald-600' : 'text-slate-400'" x-text="product.isAvailable ? 'Disponible' : 'Agotado'"></span>

                                <!-- Edit & Delete buttons -->
                                <div class="flex items-center gap-1">
                                    <button @click="openEditProduct(product)" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button @click="deleteProduct(product.id)" class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <div x-show="filteredProducts.length === 0" class="bg-white p-8 rounded-2xl border border-dashed border-slate-200 text-center space-y-2">
                        <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                            <i data-lucide="package-x" class="w-6 h-6"></i>
                        </div>
                        <p class="text-slate-600 font-medium text-sm">No se encontraron productos</p>
                        <p class="text-xs text-slate-400">Intenta cambiar el filtro o agrega un nuevo producto con el botón verde.</p>
                    </div>
                </div>
            </section>

            <!-- TAB 2: CATEGORÍAS -->
            <section x-show="activeTab === 'categories'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base">Categorías del Negocio</h2>
                        <p class="text-xs text-slate-500">Organiza tus productos para la vista de tus clientes.</p>
                    </div>
                    <button @click="openCategoryModal()" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-3 py-2 rounded-xl flex items-center gap-1 transition active:scale-95">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Nueva</span>
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(cat, index) in categories" :key="cat.id">
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="cursor-grab text-slate-300 hover:text-slate-500">
                                    <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-slate-800 text-sm" x-text="cat.name"></h4>
                                    <span class="text-xs text-slate-400" x-text="products.filter(p => p.categoryId === cat.id).length + ' productos asociados'"></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <button @click="editCategory(cat)" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <button @click="deleteCategory(cat.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <!-- TAB 3: PEDIDOS -->
            <section x-show="activeTab === 'orders'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800 text-base">Historial de Pedidos</h2>
                        <p class="text-xs text-slate-500">Pedidos armados por clientes que hicieron clic a WhatsApp.</p>
                    </div>
                    <span class="bg-emerald-100 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded-full" x-text="orders.length + ' Total'"></span>
                </div>

                <div class="space-y-3">
                    <template x-for="order in orders" :key="order.id">
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
                            <div class="flex items-start justify-between border-b border-slate-100 pb-2.5">
                                <div>
                                    <span class="text-xs font-mono text-slate-400" x-text="'#' + order.id"></span>
                                    <h4 class="font-bold text-slate-800 text-sm" x-text="order.customerName"></h4>
                                    <p class="text-xs text-slate-500" x-text="order.date"></p>
                                </div>
                                <span :class="{
                                    'bg-amber-50 text-amber-600 border-amber-200': order.status === 'Pendiente',
                                    'bg-emerald-50 text-emerald-600 border-emerald-200': order.status === 'Entregado',
                                    'bg-slate-100 text-slate-600 border-slate-200': order.status === 'Atendido'
                                }" class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full border" x-text="order.status"></span>
                            </div>

                            <!-- Order Items -->
                            <div class="space-y-1 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <template x-for="item in order.items" :key="item.name">
                                    <div class="flex justify-between">
                                        <span x-text="item.qty + 'x ' + item.name"></span>
                                        <span class="font-semibold text-slate-700" x-text="store.currency + ' ' + (item.qty * item.price).toFixed(2)"></span>
                                    </div>
                                </template>
                                <div class="border-t border-slate-200 pt-1.5 mt-1.5 flex justify-between font-bold text-slate-800 text-sm">
                                    <span>Total Pedido:</span>
                                    <span class="text-brand-600" x-text="store.currency + ' ' + parseFloat(order.total).toFixed(2)"></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-1">
                                <a :href="'https://wa.me/' + order.customerPhone + '?text=' + encodeURIComponent('¡Hola ' + order.customerName + '! Te escribo sobre tu pedido #' + order.id)" 
                                   target="_blank" 
                                   class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-3 py-2 rounded-xl flex items-center gap-1.5 transition">
                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                    <span>Chat WhatsApp</span>
                                </a>

                                <select x-model="order.status" class="bg-white border border-slate-200 text-xs font-medium text-slate-700 rounded-xl px-2.5 py-2 focus:outline-none">
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Atendido">Atendido</option>
                                    <option value="Entregado">Entregado</option>
                                </select>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <!-- TAB 4: AJUSTES -->
            <section x-show="activeTab === 'settings'" class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                    <h2 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-2">Configuración de tu Tienda</h2>

                    <!-- Store Name -->
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">Nombre Público del Negocio</label>
                        <input type="text" x-model="store.name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                    </div>

                    <!-- Subdomain (with availability indicator) -->
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">Enlace Web / Subdominio</label>
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-brand-500/20 focus-within:border-brand-500 focus-within:bg-white">
                            <input type="text" x-model="store.subdomain" @input="checkSubdomain()" class="w-full bg-transparent px-3.5 py-2.5 text-sm outline-none font-mono text-slate-800">
                            <span class="bg-slate-200 text-slate-600 px-3 py-2.5 text-xs font-mono border-l border-slate-200">.tubase.com</span>
                        </div>
                        <div class="flex items-center gap-1 mt-1 text-[11px]">
                            <template x-if="subdomainAvailable">
                                <span class="text-emerald-600 font-medium flex items-center gap-1">
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Subdominio disponible
                                </span>
                            </template>
                            <template x-if="!subdomainAvailable">
                                <span class="text-rose-500 font-medium flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Subdominio no disponible
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- WhatsApp Number -->
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">WhatsApp de Recepción de Pedidos</label>
                        <div class="relative">
                            <input type="text" x-model="store.whatsapp" placeholder="Ej: 584121234567" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none font-mono">
                        </div>
                        <p class="text-[11px] text-slate-400">Incluye código de país sin el símbolo +. Ejemplo: 584120000000</p>
                    </div>

                    <!-- Currency Selector -->
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">Moneda de Venta</label>
                        <select x-model="store.currency" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none font-medium text-slate-800">
                            <option value="$">Dólares ($)</option>
                            <option value="Bs">Bolívares (Bs)</option>
                            <option value="€">Euros (€)</option>
                            <option value="S/.">Soles (S/.)</option>
                            <option value="COP">Pesos Colombianos (COP)</option>
                        </select>
                    </div>

                    <!-- Delivery Instructions -->
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">Indicaciones para Entregas / Compras</label>
                        <textarea x-model="store.deliveryNotes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none resize-none"></textarea>
                    </div>

                    <!-- Save Button -->
                    <button @click="saveSettings()" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-brand-500/20 transition active:scale-95 flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </section>

        </main>

        <!-- FLOATING ACTION BUTTON (FAB) FOR NEW PRODUCT -->
        <button x-show="activeTab === 'products'" 
                @click="openNewProductModal()" 
                class="fixed bottom-20 right-4 md:absolute md:bottom-20 bg-emerald-600 hover:bg-emerald-700 text-white p-4 rounded-full shadow-xl shadow-emerald-600/30 transition-all transform active:scale-90 z-40 flex items-center justify-center">
            <i data-lucide="plus" class="w-6 h-6"></i>
        </button>

        <!-- BOTTOM SHEET DRAWER: CREATE / EDIT PRODUCT -->
        <div x-show="showProductModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-full"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-full"
             class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 backdrop-blur-xs"
             style="display: none;">
            
            <div @click.away="showProductModal = false" class="bg-white w-full max-w-md rounded-t-3xl p-5 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
                <!-- Drawer Drag Indicator -->
                <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto"></div>

                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="font-bold text-slate-800 text-base" x-text="editingProduct ? 'Editar Producto' : '🚀 Nuevo Producto'"></h3>
                    <button @click="showProductModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form @submit.prevent="saveProduct()" class="space-y-4">
                    <!-- Photo Picker Simulation -->
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">Foto del Producto</label>
                        <div @click="$refs.photoInput.click()" class="border-2 border-dashed border-slate-200 hover:border-brand-500 rounded-2xl p-4 text-center cursor-pointer bg-slate-50 hover:bg-slate-100/50 transition flex flex-col items-center justify-center gap-2">
                            <template x-if="productForm.image">
                                <img :src="productForm.image" class="w-24 h-24 object-cover rounded-xl border shadow-xs">
                            </template>
                            <template x-if="!productForm.image">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-1">
                                        <i data-lucide="camera" class="w-5 h-5"></i>
                                    </div>
                                    <p class="text-xs font-medium text-slate-700">Tomar Foto o Elegir de Galería</p>
                                    <p class="text-[10px] text-slate-400">Compresión WebP automática</p>
                                </div>
                            </template>
                            <input type="file" x-ref="photoInput" @change="handlePhotoUpload($event)" accept="image/*" class="hidden">
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-700">Nombre del Producto *</label>
                        <input type="text" x-model="productForm.name" required placeholder="Ej. Torta Trazos de Chocolate" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-base focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                    </div>

                    <!-- Price & Category Grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700">Precio (<span x-text="store.currency"></span>) *</label>
                            <input type="number" step="0.01" x-model="productForm.price" required placeholder="0.00" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-base font-bold text-brand-600 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-700">Categoría</label>
                            <select x-model="productForm.categoryId" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Immediate Availability Toggle -->
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <div>
                            <p class="text-xs font-semibold text-slate-800">Disponible Inmediatamente</p>
                            <p class="text-[10px] text-slate-500">Muestra u oculta del carrito de compras</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="productForm.isAvailable" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-600/20 active:scale-95 transition flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-5 h-5"></i>
                        <span x-text="editingProduct ? 'Actualizar Producto' : 'Publicar Producto'"></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- CATEGORY MODAL -->
        <div x-show="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" style="display: none;">
            <div class="bg-white w-full max-w-sm rounded-2xl p-5 space-y-4 shadow-xl">
                <div class="flex justify-between items-center border-b pb-2">
                    <h3 class="font-bold text-slate-800 text-sm" x-text="editingCategory ? 'Editar Categoría' : 'Nueva Categoría'"></h3>
                    <button @click="showCategoryModal = false" class="text-slate-400 p-1"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-700">Nombre de Categoría</label>
                    <input type="text" x-model="categoryForm.name" placeholder="Ej. Postres Fríos" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>
                <button @click="saveCategory()" class="w-full bg-slate-900 text-white font-semibold py-2.5 rounded-xl text-xs active:scale-95 transition">Guardar Categoría</button>
            </div>
        </div>

        <!-- STORE FRONT PREVIEW MODAL (Client View Simulator) -->
        <div x-show="openStorePreview" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-xs flex justify-center p-2 sm:p-4 overflow-y-auto" style="display: none;">
            <div class="bg-slate-50 w-full max-w-md rounded-3xl overflow-hidden flex flex-col shadow-2xl relative my-auto min-h-[85vh] max-h-[90vh]">
                
                <!-- Simulated Buyer Header -->
                <div class="bg-emerald-600 text-white p-4 flex items-center justify-between shadow-md">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-bold text-sm">
                            <span x-text="store.name.substring(0,2).toUpperCase()"></span>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm leading-tight" x-text="store.name"></h4>
                            <span class="text-[10px] text-emerald-100 font-mono">Vista Previa Comprador</span>
                        </div>
                    </div>
                    <button @click="openStorePreview = false" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded-full">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Buyer Storefront Content -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div class="bg-emerald-50 border border-emerald-200 p-3 rounded-xl text-xs text-emerald-800 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                        <span>Así es exactamente como tus clientes verán tu catálogo en su navegador.</span>
                    </div>

                    <!-- Client Product Cards Grid -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <template x-for="p in products.filter(item => item.isAvailable)" :key="'preview-' + p.id">
                            <div class="bg-white border border-slate-200 rounded-xl p-2.5 flex flex-col justify-between space-y-2 shadow-xs">
                                <img :src="p.image || 'https://placehold.co/120x120/f1f5f9/94a3b8?text=Foto'" class="w-full h-24 object-cover rounded-lg">
                                <div>
                                    <h5 class="font-semibold text-slate-800 text-xs truncate" x-text="p.name"></h5>
                                    <p class="text-emerald-600 font-bold text-xs" x-text="store.currency + ' ' + parseFloat(p.price).toFixed(2)"></p>
                                </div>
                                <button @click="addSimulatedCart(p)" class="w-full bg-slate-900 text-white text-[11px] font-medium py-1.5 rounded-lg flex items-center justify-center gap-1 active:scale-95">
                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                    <span>Agregar</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Simulated WhatsApp Cart Footer -->
                <div class="p-3 bg-white border-t border-slate-200 space-y-2">
                    <div class="flex justify-between items-center text-xs font-semibold text-slate-700">
                        <span>Carrito (<span x-text="simulatedCart.length"></span> items)</span>
                        <span class="text-emerald-600 text-sm font-bold" x-text="store.currency + ' ' + simulatedCartTotal.toFixed(2)"></span>
                    </div>

                    <button @click="triggerWhatsAppOrder()" :disabled="simulatedCart.length === 0" class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition active:scale-95 shadow-md">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Pedir por WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast Notification Alert -->
        <div x-show="toast.show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-full shadow-2xl flex items-center gap-2 border border-slate-700">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
            <span x-text="toast.message">Acción completada con éxito</span>
        </div>

        <!-- BOTTOM FIXED NAVIGATION BAR -->
        <nav class="fixed bottom-0 max-w-md w-full bg-white border-t border-slate-200 px-3 py-2 flex items-center justify-around z-30 shadow-lg">
            <button @click="activeTab = 'products'" :class="activeTab === 'products' ? 'text-brand-600 font-bold' : 'text-slate-400 hover:text-slate-600'" class="flex flex-col items-center gap-1 transition">
                <i data-lucide="package" class="w-5 h-5"></i>
                <span class="text-[10px]">Productos</span>
            </button>

            <button @click="activeTab = 'categories'" :class="activeTab === 'categories' ? 'text-brand-600 font-bold' : 'text-slate-400 hover:text-slate-600'" class="flex flex-col items-center gap-1 transition">
                <i data-lucide="tags" class="w-5 h-5"></i>
                <span class="text-[10px]">Categorías</span>
            </button>

            <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'text-brand-600 font-bold' : 'text-slate-400 hover:text-slate-600'" class="flex flex-col items-center gap-1 transition relative">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                <span class="text-[10px]">Pedidos</span>
                <span x-show="orders.filter(o => o.status === 'Pendiente').length > 0" class="absolute -top-1 right-2 w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            </button>

            <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'text-brand-600 font-bold' : 'text-slate-400 hover:text-slate-600'" class="flex flex-col items-center gap-1 transition">
                <i data-lucide="settings" class="w-5 h-5"></i>
                <span class="text-[10px]">Ajustes</span>
            </button>
        </nav>

    </div>

    <!-- Alpine.js Application Logic -->
    <script>
        function adminApp() {
            return {
                activeTab: 'products',
                searchQuery: '',
                selectedCategory: 'all',
                showProductModal: false,
                editingProduct: null,
                showCategoryModal: false,
                editingCategory: null,
                openStorePreview: false,
                subdomainAvailable: true,

                toast: {
                    show: false,
                    message: ''
                },

                // Store Configurations
                store: {
                    name: 'Postres Doña María',
                    subdomain: 'postresmari',
                    whatsapp: '584121234567',
                    currency: '$',
                    deliveryNotes: 'Entregas en la garita principal del condominio de 2pm a 6pm.'
                },

                // Categories Data
                categories: [
                    { id: 'cat-1', name: 'Postres & Tortas' },
                    { id: 'cat-2', name: 'Alimentos & Salados' },
                    { id: 'cat-3', name: 'Bebidas Frías' }
                ],

                // Products Data
                products: [
                    {
                        id: 'prod-1',
                        name: 'Torta Marquesa de Chocolate 1kg',
                        price: 12.50,
                        categoryId: 'cat-1',
                        isAvailable: true,
                        image: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=300&auto=format&fit=crop&q=80'
                    },
                    {
                        id: 'prod-2',
                        name: 'Quesillo Tradicional Casero',
                        price: 8.00,
                        categoryId: 'cat-1',
                        isAvailable: true,
                        image: 'https://images.unsplash.com/photo-1528975604071-b4dc52a2d18c?w=300&auto=format&fit=crop&q=80'
                    },
                    {
                        id: 'prod-3',
                        name: 'Harina PAN Maíz Blanco 1kg',
                        price: 1.20,
                        categoryId: 'cat-2',
                        isAvailable: false,
                        image: 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=300&auto=format&fit=crop&q=80'
                    }
                ],

                // Orders Data
                orders: [
                    {
                        id: '1001',
                        customerName: 'Carlos Mendoza (Apt 4B)',
                        customerPhone: '584149998877',
                        date: 'Hoy, 2:30 PM',
                        status: 'Pendiente',
                        items: [
                            { name: 'Torta Marquesa de Chocolate 1kg', qty: 1, price: 12.50 },
                            { name: 'Quesillo Tradicional Casero', qty: 1, price: 8.00 }
                        ],
                        total: 20.50
                    }
                ],

                // Forms
                productForm: {
                    id: null,
                    name: '',
                    price: '',
                    categoryId: '',
                    isAvailable: true,
                    image: ''
                },

                categoryForm: {
                    id: null,
                    name: ''
                },

                // Simulated Client Cart
                simulatedCart: [],

                init() {
                    const renderIcons = () => {
                        if (typeof lucide !== 'undefined' && lucide.createIcons) {
                            lucide.createIcons();
                        }
                    };
                    this.$watch('activeTab', () => this.$nextTick(renderIcons));
                    this.$watch('showProductModal', () => this.$nextTick(renderIcons));
                    this.$watch('openStorePreview', () => this.$nextTick(renderIcons));
                    this.$nextTick(renderIcons);
                },

                get filteredProducts() {
                    return this.products.filter(p => {
                        const matchesSearch = p.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                        const matchesCat = this.selectedCategory === 'all' || p.categoryId === this.selectedCategory;
                        return matchesSearch && matchesCat;
                    });
                },

                getCategoryName(catId) {
                    const cat = this.categories.find(c => c.id === catId);
                    return cat ? cat.name : 'General';
                },

                toggleAvailability(product) {
                    product.isAvailable = !product.isAvailable;
                    this.showToast(product.isAvailable ? 'Producto activado' : 'Producto marcado como Agotado');
                },

                openNewProductModal() {
                    this.editingProduct = null;
                    this.productForm = {
                        id: 'prod-' + Date.now(),
                        name: '',
                        price: '',
                        categoryId: this.categories[0] ? this.categories[0].id : '',
                        isAvailable: true,
                        image: ''
                    };
                    this.showProductModal = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                openEditProduct(product) {
                    this.editingProduct = product;
                    this.productForm = { ...product };
                    this.showProductModal = true;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                },

                handlePhotoUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.productForm.image = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                },

                saveProduct() {
                    if (this.editingProduct) {
                        const index = this.products.findIndex(p => p.id === this.editingProduct.id);
                        if (index !== -1) {
                            this.products[index] = { ...this.productForm };
                        }
                        this.showToast('Producto actualizado con éxito');
                    } else {
                        this.products.unshift({ ...this.productForm });
                        this.showToast('¡Producto publicado en tu tienda!');
                    }
                    this.showProductModal = false;
                },

                deleteProduct(id) {
                    this.products = this.products.filter(p => p.id !== id);
                    this.showToast('Producto eliminado');
                },

                openCategoryModal() {
                    this.editingCategory = null;
                    this.categoryForm = { id: 'cat-' + Date.now(), name: '' };
                    this.showCategoryModal = true;
                },

                editCategory(cat) {
                    this.editingCategory = cat;
                    this.categoryForm = { ...cat };
                    this.showCategoryModal = true;
                },

                saveCategory() {
                    if (!this.categoryForm.name) return;
                    if (this.editingCategory) {
                        const index = this.categories.findIndex(c => c.id === this.editingCategory.id);
                        if (index !== -1) this.categories[index].name = this.categoryForm.name;
                    } else {
                        this.categories.push({ ...this.categoryForm });
                    }
                    this.showCategoryModal = false;
                    this.showToast('Categoría guardada');
                },

                deleteCategory(id) {
                    this.categories = this.categories.filter(c => c.id !== id);
                    this.showToast('Categoría eliminada');
                },

                checkSubdomain() {
                    const reserved = ['www', 'admin', 'api', 'app'];
                    this.subdomainAvailable = !reserved.includes(this.store.subdomain.toLowerCase().trim());
                },

                saveSettings() {
                    this.showToast('Configuración guardada correctamente');
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 3000);
                },

                // Simulated Client Store Actions
                addSimulatedCart(product) {
                    this.simulatedCart.push(product);
                    this.showToast('Producto agregado al carrito de prueba');
                },

                get simulatedCartTotal() {
                    return this.simulatedCart.reduce((sum, item) => sum + parseFloat(item.price), 0);
                },

                triggerWhatsAppOrder() {
                    let msg = `*¡Hola! Quisiera realizar el siguiente pedido en ${this.store.name}:*\n\n`;
                    this.simulatedCart.forEach(i => {
                        msg += `• 1x ${i.name} - ${this.store.currency}${parseFloat(i.price).toFixed(2)}\n`;
                    });
                    msg += `\n*Total a pagar:* ${this.store.currency}${this.simulatedCartTotal.toFixed(2)}`;
                    msg += `\n*Indicaciones:* ${this.store.deliveryNotes}`;

                    const url = `https://api.whatsapp.com/send?phone=${this.store.whatsapp}&text=${encodeURIComponent(msg)}`;
                    window.open(url, '_blank');
                }
            }
        }
    </script>
</body>
</html>

---

# Especificación funcional consolidada de la SaaS post-MVP

## 1. Objetivo

Evolucionar el MVP validado hacia una plataforma SaaS para que pequeños negocios creen y administren una tienda online, publiquen un catálogo, reciban pedidos y gestionen su afiliación mensual.

La evolución se desarrollará inicialmente en entorno local. La preparación para producción, los cobros reales y la integración con una pasarela de pagos quedan pospuestos hasta autorización explícita.

## 2. Estado actual disponible

La plataforma ya dispone de:

- Registro e inicio de sesión.
- Una tienda inicial por usuario.
- Configuración básica de la tienda.
- Categorías y productos aislados por tienda.
- Imágenes optimizadas y galería pública.
- Catálogo público responsive.
- Carrito mediante sesión.
- Pedidos persistentes.
- Enlaces a WhatsApp.
- Panel administrativo de pedidos.
- Landing inicial conectada a registro e inicio de sesión.
- Suite completa validada: 62 pruebas aprobadas y 184 aserciones.

## 3. Planes comerciales aprobados

### 3.1 Plan Inicial — Gratis

- Precio: `$0`, permanente.
- Una tienda por usuario.
- Hasta 2 productos.
- Hasta 2 categorías.
- Una imagen optimizada por producto.
- Catálogo público, carrito y pedidos por WhatsApp.
- Panel básico de pedidos.
- Subdominio lógico de la plataforma.
- Soporte mediante documentación.
- Publicidad y marca visible de la plataforma.
- Sin estadísticas avanzadas.
- Sin personalización avanzada.
- Sin soporte prioritario.

### 3.2 Plan Emprendedor — Crecimiento

- Precio: `$4.99 USD/mes`.
- Hasta 20 productos.
- Hasta 20 categorías.
- Galería de imágenes por producto.
- Historial completo de pedidos.
- Estadísticas básicas.
- Personalización básica de la tienda.
- Mayor capacidad de almacenamiento.
- Soporte prioritario.
- Sin publicidad de la plataforma.

### 3.3 Plan Profesional — Negocio

- Precio: `$9.99 USD/mes`.
- Hasta 500 productos.
- Categorías ampliadas.
- Mayor capacidad de almacenamiento.
- Estadísticas avanzadas.
- Gestión de clientes.
- Inventario y control de stock.
- Variantes de productos.
- Cupones y promociones.
- Exportación de pedidos.
- Soporte prioritario ampliado.

Las funcionalidades no implementadas deberán aparecer como “Próximamente” y no promocionarse como disponibles.

## 4. Prueba gratuita y ciclo de suscripción

- El Plan Inicial es gratuito permanentemente.
- El Plan Emprendedor tendrá una prueba gratuita de 14 días.
- No se solicitará tarjeta durante la prueba inicial.
- Se enviarán recordatorios 7, 3 y 1 día antes del vencimiento.
- El periodo de gracia será de 7 días.
- Durante la gracia se mostrarán avisos de pago y la política de regularización.
- Tras la gracia, la tienda podrá suspenderse temporalmente.
- La suspensión no eliminará productos, pedidos, imágenes ni configuración.
- La reactivación ocurrirá después de confirmar el pago.
- Los cambios deberán conservar trazabilidad e historial.

## 5. Estados de suscripción

Estados mínimos:

- `trialing`: prueba gratuita activa.
- `active`: suscripción pagada y vigente.
- `past_due`: pago pendiente o fallido.
- `grace_period`: periodo de gracia.
- `suspended`: acceso comercial suspendido.
- `cancelled`: suscripción cancelada.
- `expired`: suscripción finalizada sin renovación.

Cada suscripción deberá conservar:

- Plan.
- Fecha de inicio.
- Fecha de vencimiento.
- Próxima renovación.
- Fecha de inicio y fin de gracia.
- Fecha de cancelación.
- Motivo del cambio de estado.
- Historial de transiciones.

## 6. Pagos y renovaciones

La arquitectura futura deberá incluir:

- Historial de pagos.
- Renovaciones mensuales.
- Reembolsos e incidencias.
- Identificador externo del proveedor.
- Validación de firma de webhooks.
- Idempotencia para evitar duplicados.
- Conciliación entre proveedor y aplicación.
- Registro de eventos de pago.
- No almacenar números de tarjeta ni códigos sensibles.
- Separación entre estado de pago y estado de suscripción.

La integración con una pasarela concreta se decidirá después de validar el mercado objetivo, país, moneda y proveedor disponible.

## 7. Reglas de acceso

- El usuario autenticado solo podrá administrar su tienda.
- El aislamiento por `store_id` se mantendrá en todas las consultas.
- El Plan Inicial tendrá límites verificables en servidor.
- Los límites nunca dependerán únicamente del frontend.
- La suspensión deberá impedir nuevas operaciones comerciales según la política definida.
- Los datos históricos deberán permanecer consultables cuando corresponda.
- Ninguna suspensión eliminará información automáticamente.

## 8. Onboarding posterior al registro

Después del registro, el usuario deberá poder completar:

- Nombre comercial.
- Slug o identificador público.
- Número de WhatsApp.
- Moneda.
- Indicaciones de entrega.
- Imagen o identidad visual futura.
- Publicación de la tienda.

El onboarding deberá validar los datos mínimos antes de permitir publicar el catálogo.

## 9. Landing y conversión

La landing deberá incluir:

- Propuesta de valor clara.
- Beneficios de vender mediante catálogo y WhatsApp.
- Demo visual del catálogo y panel.
- Funcionalidades disponibles actualmente.
- Planes y precios.
- Prueba gratuita y límites.
- Preguntas frecuentes.
- Llamadas a registro e inicio de sesión.
- Separación visible entre funciones actuales y futuras.
- SEO básico, accesibilidad y rendimiento.
- Términos, privacidad y política de cookies.

## 10. Funcionalidades post-MVP futuras

- Inventario y stock.
- Clientes e historial de compras.
- Variantes de productos.
- Cupones y promociones.
- Estadísticas y métricas.
- Notificaciones de pedidos.
- Roles y permisos.
- Auditoría y registro de actividad.
- Exportación de datos.
- Soporte y centro de ayuda.
- Evolución progresiva hacia varias tiendas por usuario.

## 11. Seguridad y buenas prácticas

- No exponer secretos ni valores privados del entorno.
- Mantener autorización y aislamiento por tienda.
- Validar límites en backend.
- Utilizar políticas y middleware para acceso.
- Proteger webhooks y validar firmas.
- Aplicar idempotencia a eventos externos.
- Mantener copias de seguridad antes de producción.
- Registrar errores sin almacenar datos financieros sensibles.
- Mantener pruebas automatizadas para cada regla crítica.

## 12. Criterios generales de aceptación post-MVP

Cada funcionalidad deberá:

- Tener requisitos funcionales claros.
- Definir sus estados y reglas de negocio.
- Mantener compatibilidad con el MVP.
- Incluir pruebas automatizadas.
- Ser validada manualmente cuando afecte a la interfaz.
- Respetar el aislamiento por tienda.
- Documentarse antes o después de su implementación.
- Desarrollarse de forma atómica en entorno local.

