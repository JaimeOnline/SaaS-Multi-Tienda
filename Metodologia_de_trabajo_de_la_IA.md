# Metodología de Trabajo Asistido por IA para Proyectos Laravel

## 1. Objetivo

Este documento describe una metodología práctica para desarrollar proyectos Laravel de forma segura, incremental, verificable y fácil de mantener con el apoyo de una Inteligencia Artificial (IA).

El método busca que cada cambio cumpla con las siguientes condiciones:
* **Propósito claro:** Cada modificación debe tener un objetivo específico.
* **Impacto mínimo:** Modificar la menor cantidad posible de archivos por iteración.
* **Verificable:** Que pueda probarse inmediatamente de forma manual o automatizada.
* **Estabilidad:** Mantener el proyecto siempre funcional entre cambios.
* **Documentado:** Registrar los avances y cambios estructurales.
* **Seguro:** Respetar la privacidad de los datos y la seguridad del repositorio.

> 💡 **Nota clave:** La IA actúa como asistente técnico, pero el desarrollador mantiene el control absoluto sobre las decisiones funcionales, la autorización de cambios, los comandos ejecutados, las operaciones de Git y la información privada del entorno.

---

## 2. Principios Fundamentales

### 2.1 Trabajo Atómico
Cada interacción o respuesta de la IA debe realizar **una sola acción** a la vez.

**Ejemplos de acciones únicas:**
* Revisar un archivo específico.
* Ejecutar un comando en la terminal.
* Modificar un único archivo.
* Ejecutar una prueba unitaria o de integración.
* Actualizar un archivo de documentación.

⚠️ *Después de cada acción, se debe esperar la confirmación del desarrollador antes de continuar.*

**¿Qué evita este enfoque?**
* Cambios masivos difíciles de revisar o revertir.
* Acumulación de errores en cascada.
* Modificaciones accidentales en archivos no relacionados.
* Pérdida del hilo o contexto de la conversación.
* Dificultad para identificar qué cambio exacto causó un fallo.

### 2.2 Revisar Antes de Modificar
Nunca se debe escribir código basándose únicamente en suposiciones o alucinaciones. Antes de modificar cualquier archivo, la IA debe seguir este orden:

1. **Leer** el archivo objetivo completamente.
2. **Entender** su responsabilidad dentro de la arquitectura.
3. **Revisar** los archivos directamente relacionados (modelos, rutas, etc.).
4. **Identificar** el patrón de diseño y estilo utilizado en el proyecto.
5. **Confirmar** el cambio más pequeño y óptimo necesario.

### 2.3 Cambios Pequeños y Verificables
Las funcionalidades grandes se deben desglosar en un paso a paso secuencial. No se recomienda crear o alterar todo el sistema en una sola operación. 

**Flujo incremental recomendado:**
1. Migración de Base de Datos
2. Modelo Eloquent
3. Relaciones y Validaciones
4. Controlador (FormRequest si aplica)
5. Rutas
6. Vistas / Componentes frontend
7. Pruebas Automatizadas (Feature/Unit)
8. Ejecución y validación de pruebas
9. Actualización de Documentación

### 2.4 Código Acompañado de Pruebas
Una funcionalidad **no se considera terminada** solo porque la pantalla funciona manualmente en el navegador. Debe verificarse mediante pruebas automatizadas que cubran:

* 🌐 Respuestas HTTP y códigos de estado correctos.
* 🔐 Autenticación y flujos de sesión.
* 🧪 Validación de formularios y datos de entrada.
* 💾 Persistencia correcta en la base de datos.
* 🛡️ Autorización y políticas de acceso (Policies/Gate).
* 🏢 Aislamiento estricto de datos entre diferentes usuarios o tiendas (Multi-tenant).

---

## 3. Protocolo Inicial de una Nueva Sesión

Antes de sugerir o realizar cualquier cambio de código, la IA debe auditar el estado del proyecto revisando los siguientes archivos si existen:

* 📄 `Plan_de_trabajo.md`
* 📄 `Especificaciones.md`
* 📂 Estructura general del proyecto
* 💻 Salida del comando `git status`
* ⚙️ El archivo `mvp/.env.example`
* 🗄️ Migraciones, modelos y rutas relacionados con la tarea actual

### 🛑 Reglas de Seguridad Estrictas (Filtro de Secretos)
**Nunca** se debe revisar, solicitar ni mostrar en el chat el contenido de:
* ❌ `mvp/.env` (archivo real de entorno)
* ❌ Contraseñas de bases de datos o servicios
* ❌ El valor del `APP_KEY`
* ❌ API Tokens, llaves privadas o credenciales de servicios externos
* ❌ Información privada de entornos de producción

> 🚀 **Salida Esperada:** La primera respuesta de la IA en una nueva sesión debe contener **únicamente**: un resumen corto del estado actual, el próximo objetivo inmediato y una sola acción inicial para comenzar.

---

## 4. Preparación de un Proyecto Laravel

### 4.1 Verificar Herramientas del Entorno
Antes de comenzar el desarrollo, conviene verificar las versiones del entorno ejecutable:

```powershell
php -v
composer --version
node --version
npm --version
git --version
php -m # Confirmar módulos instalados
```

*Para proyectos que utilizan **PostgreSQL**, es obligatorio confirmar que PHP tenga habilitada la extensión:* `pdo_pgsql`.

### 4.2 Estructura del Proyecto y Git
La aplicación Laravel se aloja dentro del directorio `mvp/`, manteniendo el repositorio Git en la raíz del espacio de trabajo.

⚠️ **Regla crítica:** No inicializar ni crear otro repositorio Git dentro de la carpeta `mvp/`.

```text
SaaS Multi Tienda/
├── .git/
├── Plan_de_trabajo.md
├── Especificaciones.md
├── Metodologia_de_trabajo_de_la_IA.md
└── mvp/  <-- Carpeta raíz de Laravel
```

### 4.3 Instalación del Sistema de Autenticación
Para una aplicación Blade tradicional se utiliza **Laravel Breeze**:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
```

Una vez instalado, se debe verificar el correcto funcionamiento de:
* Registro e Inicio de sesión.
* Cierre de sesión (Logout).
* Recuperación de contraseña y verificación de correo electrónico.
* Protección de rutas mediante middleware.

---

## 5. Configuración de PostgreSQL

El proyecto utiliza **PostgreSQL** tanto para el entorno de desarrollo como para el de pruebas.

### Configuración del Entorno (.env)
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=saas_multi_tienda
DB_USERNAME=postgres
```

⚠️ *La contraseña real (`DB_PASSWORD`) debe permanecer únicamente de forma local en el `.env` y jamás registrarse en la documentación, prompts, capturas o control de versiones.*

### Base de Datos de Pruebas
Se utiliza una base de datos independiente para no corromper los datos de desarrollo: `saas_multi_tienda_test`. Esta configuración se gestiona de forma segura a través del archivo `phpunit.xml`.

---

## 6. Migraciones y Diseño de Base de Datos

### 6.1 Orden de Creación Recomendado
Diseñar y ejecutar las tablas respetando rigurosamente sus dependencias jerárquicas:
1. `users` (Usuarios)
2. `stores` (Tiendas)
3. `categories` (Categorías)
4. `products` (Productos)
5. `orders` (Pedidos)
6. `order_items` (Detalles de pedidos)

### 6.2 Relaciones Multi-tienda (Multi-tenancy) desde el Inicio
Incluso si inicialmente se plantea un esquema de una sola tienda por usuario, todas las entidades dependientes deben incluir la llave foránea `store_id`.

```php
$table->foreignId('store_id')
      ->constrained()
      ->cascadeOnDelete();
```

**Beneficios:**
* Filtrado de datos nativo y seguro por tienda.
* Prevención estricta de accesos cruzados de datos entre clientes.
* Escalabilidad inmediata hacia un modelo multi-tienda avanzado.

### 6.3 Restricciones de Unicidad e Integridad
Para mantener la consistencia de datos en el contexto de cada tienda, se aplican llaves únicas compuestas:

```php
// El slug del producto/categoría solo debe ser único dentro de la misma tienda
$table->unique(['store_id', 'slug']);
```

Para productos que pertenecen de forma opcional a una categoría, se utiliza la anulación en cascada para evitar la pérdida de datos del producto:

```php
$table->foreignId('category_id')
      ->nullable()
      ->constrained()
      ->nullOnDelete(); // Si se elimina la categoría, el producto se conserva como "Sin categoría"
```

### 6.4 Ejecución Segura de Migraciones
Comando estándar: `php artisan migrate`.

Antes de ejecutar cualquier migración en el sistema, revisa detalladamente: las claves foráneas, las acciones ante borrados (`cascadeOnDelete` / `nullOnDelete`), los campos requeridos, los índices y los tipos de datos.

⚠️ **Prohibición:** No ejecutar comandos destructivos como `php artisan migrate:fresh` sin advertir previamente los riesgos de pérdida de datos y solicitar una confirmación explícita.

---

## 7. Modelos Eloquent y Relaciones

Cada modelo debe tener declaradas explícitamente sus propiedades de asignación masiva (`$fillable`), sus relaciones y la conversión de tipos nativos (`casts`).

### Ejemplo de Modelo Estructurado (`Product.php`)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'image_path',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
```

### Mapa de Relaciones Esperadas:
* `User` ➡️ *hasOne* ➡️ `Store`
* `Store` ➡️ *belongsTo* ➡️ `User`
* `Store` ➡️ *hasMany* ➡️ `Category`
* `Store` ➡️ *hasMany* ➡️ `Product`
* `Category` ➡️ *belongsTo* ➡️ `Store`
* `Category` ➡️ *hasMany* ➡️ `Product`

También conviene revisar `fillable` y `casts` para evitar:

-   Asignación masiva incorrecta.
-   Precios tratados como texto.
-   Booleanos tratados como cadenas.
-   Campos que no se pueden guardar.

## 8\. Registro automático de la tienda

Cuando el dominio indica que cada usuario debe tener una tienda, la creación automática se implementa junto con el registro.

El flujo debe comprobar:

1.  Se crea el usuario.
2.  Se crea una tienda asociada.
3.  El `user_id` corresponde al usuario registrado.
4.  El slug inicial es válido.
5.  Los datos predeterminados no contienen secretos.

Esto se verifica con una prueba de Feature y una aserción de base de datos.

## 9\. Desarrollo por capas

Para cada funcionalidad se recomienda avanzar en este orden:

### Paso 1: Base de datos

Crear o revisar la migración.

### Paso 2: Modelo

Agregar campos, relaciones y conversiones.

### Paso 3: Validación

Usar un `FormRequest` cuando la funcionalidad tenga varias reglas.

Ejemplo:

```php
public function authorize(): bool  
{  
    return $this->user()?->store !== null;  
}  
```
### Paso 4: Controlador

El controlador debe:

-   Recibir la solicitud validada.
-   Obtener la tienda del usuario autenticado.
-   Consultar datos mediante la relación de la tienda.
-   No confiar en un `store_id` enviado por el formulario.
-   Redirigir después de guardar.

### Paso 5: Rutas

Verificar que estén protegidas con middleware:
```php
Route::middleware('auth')->group(function () {  
    // Rutas administrativas.  
});  
```
### Paso 6: Vista

Crear la vista Blade reutilizando:

-   El layout existente.
-   Los componentes visuales del proyecto.
-   Protección CSRF.
-   Mensajes de validación.
-   Formularios HTML claros.

### Paso 7: Pruebas

Cubrir el comportamiento esperado y los casos de seguridad.

## 10\. Autorización y aislamiento por tienda

La regla más importante del proyecto es:

Un usuario solo puede acceder a los datos de su propia tienda.  

### 10.1 Consultas de listado

Correcto:
```php
$products = $request->user()  
    ->store  
    ->products()  
    ->get();  
```
Incorrecto:
```php
$products = Product::all();  
```

### 10.2 Creación

El `store_id` debe provenir de la tienda autenticada:
```php
$store->products()->create(\[  
    'name' => $validated\['name'\],  
    'slug' => Str::slug($validated\['name'\]),  
\]);  
```

Nunca debe confiarse en un `store_id` enviado desde el navegador.

### 10.3 Actualización y eliminación

Antes de modificar o eliminar:
```php
abort\_unless(  
    $product->store\_id === $request->user()->store->id,  
    404  
);  
```

También debe comprobarse que las categorías relacionadas pertenezcan a la misma tienda.

### 10.4 Pruebas de aislamiento

Cada recurso multi-tienda debe probar que un usuario:

-   No ve registros de otra tienda.
-   No puede editar registros de otra tienda.
-   No puede eliminarlos.
-   No puede asociarlos con categorías ajenas.

## 11\. Verificación de rutas

Después de agregar rutas se puede revisar el registro de Laravel con:

php artisan route:list --path=categories  
php artisan route:list --path=products  

Esto permite comprobar:

-   Método HTTP.
-   URI.
-   Nombre de ruta.
-   Controlador y método.
-   Existencia de rutas duplicadas.
-   Errores de escritura.

Es preferible ejecutar un comando específico antes de revisar toda la tabla de rutas.

## 12\. Pruebas automatizadas con Laravel

### 12.1 Ejecutar toda la suite

php artisan test  

Este comando ejecuta las pruebas PHPUnit mediante la integración de Laravel.

### 12.2 Ejecutar una clase concreta

php artisan test --filter=CategoryTest  
php artisan test --filter=ProductTest  

Esto acelera el ciclo de desarrollo cuando solo se modifica una funcionalidad.

### 12.3 Tipos de pruebas utilizados

#### Prueba de acceso

```php

$response = $this->get(route('products.index'));  
  
$response->assertRedirect('/login');  
```

Verifica que un visitante no acceda a una ruta protegida.

#### Prueba de respuesta correcta

```php
$response->assertOk(); 
```

Comprueba que una página se renderice correctamente.

#### Prueba de redirección

```php
$response  
    ->assertSessionHasNoErrors()  
    ->assertRedirect(route('products.index'));  
```
Comprueba el resultado esperado después de un formulario.

#### Prueba de base de datos
```php
$this->assertDatabaseHas('products', \[  
    'name' => 'Torta de chocolate',  
\]);  
```
Comprueba que un registro fue persistido.

#### Comprobar que un registro no existe
```php
$this->assertDatabaseMissing('products', \[  
    'id' => $product->id,  
\]);  
```
Comprueba eliminaciones o rechazos de creación.

#### Comprobar aislamiento
```php
$response  
    ->assertSee('Producto propio')  
    ->assertDontSee('Producto ajeno');  
```

Comprueba que el listado no exponga datos de otra tienda.

### 12.4 RefreshDatabase

Las pruebas de Feature utilizan:

use RefreshDatabase;  

Esto permite que cada prueba trabaje con una base limpia y evita que los datos de una prueba afecten a otra.

La base de pruebas debe estar separada de la base principal.

### 12.5 Orden de ejecución de pruebas

Después de un cambio pequeño:

1.  Ejecutar la prueba específica.
2.  Revisar el resultado.
3.  Corregir si es necesario.
4.  Ejecutar la suite completa al cerrar la tarea.

Ejemplo:

php artisan test --filter=ProductTest  
php artisan test  

### 12.6 Interpretar el resultado

Un resultado exitoso informa normalmente:

-   Cantidad de pruebas exitosas.
-   Cantidad de aserciones.
-   Duración.

No basta con que compile el código: las aserciones deben verificar el comportamiento real.

En este punto del proyecto se verificó una suite de:

43 pruebas exitosas  
120 aserciones exitosas  

## 13\. Validación de formularios

Las reglas deben reflejar el dominio.

Ejemplo:

'name' => \['required', 'string', 'max:255'\],  
'price' => \['required', 'numeric', 'min:0'\],  
'category\_id' => \['nullable', 'integer', 'exists:categories,id'\],  
'is\_available' => \['sometimes', 'boolean'\],  

La regla `exists` comprueba que exista el identificador, pero no garantiza que pertenezca a la tienda actual. Por eso se necesita una segunda verificación de autorización por `store_id`.

Validar siempre:

-   Campos obligatorios.
-   Longitud.
-   Tipo.
-   Rangos numéricos.
-   Relaciones.
-   Archivos.
-   Permisos.

## 14\. Imágenes y almacenamiento

Cuando se implemente la gestión de imágenes:

1.  Revisar `config/filesystems.php`.
2.  Revisar el disco configurado.
3.  Validar MIME y tamaño.
4.  Comprobar que el producto pertenezca a la tienda.
5.  Guardar la ruta en `image_path`.
6.  Eliminar la imagen anterior al reemplazarla.
7.  Eliminar la imagen al borrar el producto.
8.  Probar almacenamiento y eliminación.
9.  Evitar aceptar rutas arbitrarias enviadas por el usuario.

Durante el MVP se utilizará almacenamiento local de Laravel, dejando la estructura preparada para migrar posteriormente a S3, Cloudinary u otro proveedor.

## 15\. Control de Git

Antes de trabajar:

git status --short  

Este comando permite detectar:

-   Archivos modificados.
-   Archivos eliminados.
-   Archivos sin seguimiento.
-   Cambios previos del usuario.

Reglas:

-   No restaurar archivos sin autorización.
-   No eliminar archivos sin autorización.
-   No ejecutar `git add` sin autorización.
-   No ejecutar `git commit` sin autorización.
-   No ejecutar `git push` sin autorización.
-   No crear otro repositorio dentro de `mvp`.

El estado de Git se revisa, pero la IA no debe asumir que tiene permiso para modificarlo.

## 16\. Documentación continua

Después de cada tarea importante actualizar:

-   `Plan_de_trabajo.md`.
-   Este documento si se incorpora una práctica nueva.
-   El archivo de continuidad para otra IA cuando cambie el próximo objetivo.

La documentación debe registrar:

-   Qué se implementó.
-   Qué archivos se modificaron.
-   Qué pruebas se ejecutaron.
-   Cuántas pruebas pasaron.
-   Qué queda pendiente.
-   Cuál es el siguiente paso.

## 17\. Flujo completo reutilizable

Este es el ciclo recomendado para cualquier nueva funcionalidad:

1\. Leer la documentación.  
2\. Revisar el estado de Git.  
3\. Revisar la estructura.  
4\. Revisar migraciones y modelos.  
5\. Definir un objetivo pequeño.  
6\. Revisar el archivo que se modificará.  
7\. Crear o modificar la migración.  
8\. Crear o modificar el modelo.  
9\. Crear la validación.  
10\. Crear o modificar el controlador.  
11\. Crear las rutas.  
12\. Crear o modificar la vista.  
13\. Crear las pruebas.  
14\. Ejecutar la prueba específica.  
15\. Corregir errores.  
16\. Ejecutar la suite completa.  
17\. Actualizar la documentación.  
18\. Revisar nuevamente el estado final.  

No es obligatorio que todas las tareas tengan exactamente los mismos archivos, pero sí deben respetar el orden lógico y la validación progresiva.

## 18\. Qué hacer cuando una prueba falla

Ante un fallo:

1.  Leer el mensaje completo.
2.  Identificar la prueba concreta.
3.  Determinar si falla la ruta, validación, controlador, vista o base de datos.
4.  Revisar el archivo involucrado.
5.  Aplicar el cambio mínimo.
6.  Ejecutar nuevamente la prueba específica.
7.  Ejecutar la suite completa después de corregirla.
8.  Documentar la solución si aporta una práctica reutilizable.

No ocultar el error ni modificar muchas partes del proyecto simultáneamente.

## 19\. Criterio de finalización de una tarea

Una tarea puede considerarse terminada cuando:

-   La funcionalidad cumple el objetivo.
-   La autorización está comprobada.
-   La validación funciona.
-   Las rutas están registradas.
-   La vista responde correctamente.
-   Las pruebas específicas pasan.
-   La suite completa pasa.
-   La documentación está actualizada.
-   No se expusieron secretos.
-   No quedaron cambios destructivos sin autorización.

## 20\. Aplicación de la metodología en este proyecto

El proceso aplicado hasta ahora fue:

1.  Definir el MVP y sus límites.
2.  Crear el proyecto Laravel.
3.  Configurar Breeze, Blade, Tailwind y Alpine.js.
4.  Configurar PostgreSQL.
5.  Crear y ejecutar migraciones.
6.  Crear modelos y relaciones.
7.  Crear automáticamente una tienda al registrar un usuario.
8.  Proteger el dashboard.
9.  Implementar configuración editable de la tienda.
10.  Implementar el CRUD de categorías.
11.  Probar el aislamiento de categorías por tienda.
12.  Implementar el CRUD básico de productos.
13.  Añadir búsqueda, filtros y disponibilidad.
14.  Probar el aislamiento de productos y categorías.
15.  Ejecutar pruebas específicas después de cada bloque.
16.  Ejecutar la suite completa.
17.  Registrar el estado y el próximo objetivo en la documentación.

Resultado registrado:

43 pruebas exitosas  
120 aserciones exitosas  

El siguiente objetivo técnico pendiente es implementar la carga y eliminación de imágenes de productos, antes de continuar con el catálogo público.

## 21\. Comandos esenciales de referencia

### Laravel

php artisan migrate  
php artisan route:list --path=products  
php artisan test --filter=ProductTest  
php artisan test  
php artisan serve  

### Frontend

npm install  
npm run build  

### Composer

composer install  
composer require laravel/breeze --dev  

### Git

git status --short  
git diff  

Los comandos de Git que modifiquen el repositorio requieren autorización explícita.

## 22\. Resumen práctico

La mejor forma de trabajar con IA en un proyecto Laravel es:

-   Planificar antes de programar.
-   Revisar antes de modificar.
-   Cambiar poco.
-   Probar pronto.
-   Validar en la base de datos.
-   Comprobar autorización.
-   Ejecutar la suite completa.
-   Documentar cada avance.
-   Mantener los secretos fuera de la conversación y del repositorio.
-   Conservar al usuario como responsable de aprobar cada acción.