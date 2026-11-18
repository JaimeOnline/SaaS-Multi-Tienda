# Prompt de continuidad — Asistente Senior Laravel SaaS

Actúa como asistente senior de desarrollo para este proyecto Laravel. Responde siempre en español.

## Protocolo obligatorio

- Trabaja de forma atómica: una sola acción por respuesta.
- Si hay varios archivos, modifica solo uno por vez.
- Si hay varios comandos, indica solo uno por vez.
- Después de cada acción, detente y espera `Listo`, `OK` o `Siguiente`.
- Para cambios de código, utiliza bloques SEARCH/REPLACE.
- No generes código completo innecesario.
- Ejecuta la prueba relacionada después de cada cambio importante.
- Actualiza `Plan_de_trabajo.md` cuando se complete una tarea importante.

## Revisión inicial obligatoria

Antes de modificar código:

1. Lee completamente `Plan_de_trabajo.md`.
2. Lee completamente `Especificaciones.md`.
3. Ejecuta `git status` desde la raíz del repositorio.
4. Revisa la estructura y los archivos relacionados con la tarea pendiente.
5. **Inicia el servidor local** (`php artisan serve` en `mvp/`) y verifica acceso manual en **Brave** (`http://localhost:8000`).
6. Confirma la primera acción y detente.

No accedas ni muestres `mvp/.env`. Solo puedes revisar `mvp/.env.example`.

## Ubicación

- Repositorio: `C:\Users\Jaime\Documents\Mis Documentos\Programacion\SaaS Multi Tienda`
- Aplicación Laravel: `C:\Users\Jaime\Documents\Mis Documentos\Programacion\SaaS Multi Tienda\mvp`

Trabaja dentro de `mvp` y no crees otro repositorio Git.

## Estado actual

El MVP y la evolución SaaS local ya implementados incluyen:

- Autenticación y protección administrativa.
- Tienda, categorías y productos aislados por `store_id`.
- Imágenes de productos y galería WebP.
- Catálogo público, carrito y pedidos persistentes.
- Enlaces para WhatsApp.
- Gestión administrativa de pedidos.
- Planes comerciales y suscripción inicial.
- Prueba gratuita de 14 días.
- Límites de productos, categorías e imágenes.
- Ciclo de vida: `trialing → expired → grace_period → suspended`.
- Reactivación de suscripciones suspendidas (servicio + UI completa).
- Control de acceso administrativo selectivo: lista blanca para `/dashboard`, `/store/settings`, `/subscriptions/reactivate` durante `grace_period` y `suspended`.
- Recordatorios persistentes a 7, 3 y 1 día.
- Notificaciones por correo en cola.
- **Dashboard: avisos visibles para `grace_period` (ámbar, dos fechas), `suspended` (rojo, motivo), `expired` (gris, fecha).**
- **Botón "Reactivar suscripción" en dashboard (solo `suspended`).**
- **Historial de transiciones de suscripción: tabla `subscription_histories`, modelo `SubscriptionHistory`, registro automático en cada cambio de estado con `from_status`, `to_status`, `reason`, `origin` (`system`/`user`), `changed_at`.**
- Suite validada: **99 pruebas aprobadas y 298 aserciones**.

No repitas estas funcionalidades salvo que una tarea pendiente requiera una corrección específica.

## Próximo trabajo (Bloque 1: Avisos administrativos en grace_period) — **BLOQUE COMPLETADO ✅**

**Objetivo**: Que el comerciante vea claramente el estado de su suscripción, fechas clave y pueda actuar para regularizar.

### Tareas atómicas en orden:

1. ✅ **COMPLETADO** — **Dashboard: mostrar avisos en `grace_period`, `suspended` y `expired`**
   - Añadido en `dashboard.blade.php` bloques condicionales para:
     - `grace_period`: badge ámbar, texto "Periodo de gracia activo", muestra `expires_at` y `grace_period_ends_at`.
     - `suspended`: badge rojo, texto "Suscripción suspendida", muestra `status_reason`.
     - `expired`: badge gris, texto "Prueba expirada", muestra `expires_at`.
   - Estilos consistentes con bloques existentes (`trialing`, `active`).
   - Validado manualmente en Brave (puerto 8001) y tests pasan (92/282).

2. ✅ **COMPLETADO** — **Middleware: permitir acceso a rutas de regularización durante `grace_period` y `suspended`**
   - Modificado `EnsureActiveSubscription` con lista blanca:
     - Permite acceso si status ∈ [`grace_period`, `suspended`] **solo** a rutas permitidas.
     - Bloquea (403) el resto de rutas administrativas.
   - Rutas permitidas: `dashboard`, `store.settings.edit`, `subscriptions.reactivate`.

3. ✅ **COMPLETADO** — **Rutas accesibles durante suspensión/gracia (lista blanca)**
   - `GET /dashboard` — ver avisos y botón de reactivación.
   - `POST /subscriptions/reactivate` — reactivar suscripción.
   - `GET /store/settings` — actualizar datos de facturación/contacto.

4. ✅ **COMPLETADO** — **Controlador: añadir `reactivateSubscription` en `SubscriptionController`**
   - Valida que la suscripción esté en `suspended`.
   - Invoca `SubscriptionLifecycleService::reactivateSubscription()`.
   - Redirige con mensaje de éxito/error.

5. ✅ **COMPLETADO** — **Dashboard: botón "Reactivar suscripción" visible solo en `suspended`**
   - Formulario POST a `/subscriptions/reactivate` con `@csrf`.

6. ✅ **COMPLETADO** — **Pruebas automatizadas (Feature + Unit)**
   - `GracePeriodDashboardTest`: 3 tests - verifica avisos, fechas y botones en dashboard.
   - `SubscriptionAccessTest` actualizado: 2 tests - verifica 403 en rutas bloqueadas y 200 en permitidas.
   - Suite completa: **92 tests, 282 aserciones**.

7. ✅ **COMPLETADO** — **Validación manual en Brave**
   - Flujo probado: trialing → grace_period → suspended → reactivar.
   - Verificada visibilidad de fechas, badges, botones y mensajes.
   - Confirmado: rutas bloqueadas devuelven 403, permitidas cargan 200.

8. ✅ **COMPLETADO** — **Actualizar documentación**
   - `Plan_de_trabajo.md` y `Promt_para_que_continue_otra_IA.md` actualizados.

### Restricciones importantes

- **No implementes** cobros reales, pasarelas, webhooks de producción, VPS, multi-tienda avanzada ni superadministrador sin autorización explícita.
- Los estados `past_due` y `cancelled` se implementarán **solo** cuando exista flujo real de pagos.

## Reglas de seguridad

- No muestres contraseñas, `APP_KEY` ni contenido de `.env`.
- No ejecutes `git add`, `commit`, `push`, `restore` o eliminación de archivos sin autorización.
- No uses comandos destructivos sin advertencia y confirmación.
- Conserva la autorización y el aislamiento por `store_id`.

## Validación manual obligatoria en Brave

Antes de considerar completa cualquier tarea de UI/flujo:

1. `cd mvp && php artisan serve`
2. Abrir `http://localhost:8000` en **Brave**.
3. Probar el flujo completo correspondiente (login, dashboard, catálogo, carrito, pedido, panel admin).
4. Verificar responsive (móvil/escritorio) y que no hay errores en consola.
5. Confirmar `OK` o `Siguiente` solo tras validación manual exitosa.

## Próximo trabajo (Bloque 2: Historial de transiciones de suscripción) — **BLOQUE COMPLETADO ✅**

**Objeto**: Registrar automáticamente cada cambio de estado de la suscripción para auditoría y trazabilidad.

### Tareas atómicas completadas:

1. ✅ **COMPLETADO** — **Migración `create_subscription_histories_table`**
   - Campos: `subscription_id` (FK), `from_status`, `to_status`, `reason`, `origin`, `changed_at`, índices compuestos.

2. ✅ **COMPLETADO** — **Modelo `SubscriptionHistory`**
   - `$fillable`, casts `datetime` en `changed_at`, relación `belongsTo(Subscription)`.

3. ✅ **COMPLETADO** — **Relación en modelo `Subscription`**
   - `hasMany(SubscriptionHistory)` ordenada por `changed_at`.

4. ✅ **COMPLETADO** — **Registro automático en `SubscriptionLifecycleService`**
   - `expireTrials()`: `trialing → expired` (origen: `system`).
   - `startGracePeriods()`: `expired → grace_period` (origen: `system`).
   - `suspendFinishedGracePeriods()`: `grace_period → suspended` (origen: `system`).
   - `reactivateSubscription()`: `suspended → active` (origen: `user`).

5. ✅ **COMPLETADO** — **Registro en `SubscriptionController::startEntrepreneurTrial()`**
   - Creación inicial: `null → trialing` (origen: `user`).

6. ✅ **COMPLETADO** — **Pruebas automatizadas**
   - `SubscriptionHistoryTest`: 7 tests unitarios, 16 aserciones.
   - Suite completa: **99 tests, 298 aserciones**.

---

## La próxima IA debe comenzar informando:

> He leído el plan de trabajo. El MVP y la evolución SaaS local implementada incluyen autenticación, tiendas, categorías, productos, imágenes, catálogo público, carrito, pedidos persistentes, enlaces `wa.me`, panel administrativo, planes comerciales, prueba gratuita, límites verificables, ciclo de vida de suscripciones, reactivación completa (servicio + UI), control de acceso selectivo con lista blanca (`/dashboard`, `/store/settings`, `/subscriptions/reactivate`), recordatorios, notificaciones por correo, comandos Artisan y scheduler. **Dashboard muestra avisos para `grace_period`, `suspended` y `expired` (badges con fechas/motivos) y botón "Reactivar suscripción" (solo `suspended`).** **Historial de transiciones de suscripción implementado con registro automático de cada cambio de estado (`from_status`, `to_status`, `reason`, `origin`, `changed_at`).** La suite completa registra **99 pruebas aprobadas y 298 aserciones**. **Pagos, renovaciones y cancelaciones reales: PENDIENTES hasta definición del método de pago / pasarela.** Sin modificar código durante la revisión inicial.
