<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu tienda online para vender por WhatsApp</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="text-xl font-extrabold text-emerald-600">
                TuBase Express
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}"
                    class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                    Crear mi tienda
                </a>
            </div>
        </nav>
    </header>

    <main>
        <section class="mx-auto grid max-w-7xl gap-12 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
            <div>
                <p class="mb-4 text-sm font-bold uppercase tracking-wide text-emerald-600">
                    Comercio por WhatsApp
                </p>

                <h1 class="text-4xl font-black tracking-tight text-slate-900 sm:text-6xl">
                    Convierte tus conversaciones en ventas organizadas.
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600">
                    Crea tu catálogo online, recibe pedidos estructurados y comparte un enlace profesional con tus
                    clientes.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="rounded-xl bg-emerald-600 px-6 py-3 text-center font-bold text-white hover:bg-emerald-700">
                        Crear mi tienda gratis
                    </a>
                    <a href="#como-funciona"
                        class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-center font-semibold text-slate-700 hover:bg-slate-100">
                        ¿Cómo funciona?
                    </a>
                </div>
            </div>

            <div class="rounded-3xl bg-slate-900 p-6 text-white shadow-2xl">
                <p class="text-sm font-semibold text-emerald-400">Vista previa de tu tienda</p>
                <div class="mt-6 space-y-3">
                    <div class="rounded-xl bg-white/10 p-4">
                        <p class="font-bold">Postres Doña María</p>
                        <p class="mt-1 text-sm text-slate-300">Catálogo disponible para tus clientes</p>
                    </div>
                    <div class="rounded-xl bg-emerald-600 p-4">
                        <p class="font-bold">Pedido organizado</p>
                        <p class="mt-1 text-sm text-emerald-100">Productos, cantidades y total listos para WhatsApp.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="como-funciona" class="border-y border-slate-200 bg-white px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <h2 class="text-center text-3xl font-extrabold">Tu tienda en tres pasos</h2>

                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <p class="text-2xl font-black text-emerald-600">1</p>
                        <h3 class="mt-3 font-bold">Crea tu cuenta</h3>
                        <p class="mt-2 text-sm text-slate-600">Regístrate y configura los datos básicos de tu negocio.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <p class="text-2xl font-black text-emerald-600">2</p>
                        <h3 class="mt-3 font-bold">Publica tus productos</h3>
                        <p class="mt-2 text-sm text-slate-600">Añade categorías, precios, imágenes y disponibilidad.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-6">
                        <p class="text-2xl font-black text-emerald-600">3</p>
                        <h3 class="mt-3 font-bold">Comparte tu enlace</h3>
                        <p class="mt-2 text-sm text-slate-600">Recibe pedidos organizados directamente en WhatsApp.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="funcionalidades" class="bg-slate-50 px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                        Todo lo que necesitas para comenzar
                    </p>
                    <h2 class="mt-2 text-3xl font-extrabold text-slate-900">
                        Una tienda sencilla, clara y lista para vender
                    </h2>
                    <p class="mt-4 text-slate-600">
                        Organiza tu catálogo y recibe pedidos sin complicaciones.
                    </p>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-slate-900">Catálogo público</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Comparte un enlace profesional para que tus clientes consulten tus productos.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-slate-900">Pedidos organizados</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Recibe productos, cantidades y totales preparados para continuar la conversación por
                            WhatsApp.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-slate-900">Gestión sencilla</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Administra categorías, productos, disponibilidad, imágenes y estados de pedidos desde tu
                            panel.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-slate-900 px-4 py-8 text-center text-sm text-slate-400">
        © {{ date('Y') }} TuBase Express. Todos los derechos reservados.
    </footer>
</body>

</html>
