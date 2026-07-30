# Estado Actual del Proyecto

> Este documento se actualiza en cada sesión de trabajo relevante. No es un historial completo — para eso está el historial de git y el `## Historial` de cada documento formal. Aquí solo vive **el presente**: en qué fase estamos y qué sigue.

**Última actualización:** 2026-07-27

---

## Fase

- **Fase 1 — Fundación documental:** completada. README, LICENSE, .gitignore y los 47 documentos de `docs/` + `templates/` + ADR-001 están escritos y commiteados (`2d52a3a`).
- **Fase 2 — Prototipo funcional del piloto:** en curso. Objetivo del sprint: un prototipo **confiable y funcional**, no un despliegue en producción. No se integra pasarela de pago real, publicidad ni servicios pagos todavía — eso se evalúa después de validar el prototipo.
- Ventana de tiempo: el usuario tiene 30 días libres a partir de 2026-07-27 dedicados a avanzar esto, con intención de avanzar más rápido de lo mínimo si es posible.

---

## El piloto activo

- **Negocio:** tienda de skincare en línea (nombre del negocio y catálogo real: pendientes de definir).
- **Equipo:** proyecto de dos socios.
  - **Angie** — socia. Se enfoca en marketing y comunicaciones; su participación empieza cuando haya un prototipo que mostrar, no durante la construcción técnica.
  - **El usuario** (dueño de este repositorio) — a cargo de producto y desarrollo, construyendo en solitario con Claude Code como implementador principal.
- Esto es exactamente el "negocio piloto" que describen `docs/business/vision-producto.md` y `docs/business/actores.md` — ahí donde esos documentos dicen "el equipo fundador cumple varios roles a la vez", ese equipo fundador es el usuario (hoy) y Angie (desde que haya algo que comunicar).

## Infraestructura ya disponible

- Un servidor (Droplet de DigitalOcean) reservado para pruebas de este prototipo, que se configura con **OpenLiteSpeed + PHP + MySQL + Redis de forma nativa, sin Docker** — decisión registrada en `docs/adr/ADR-002.md` tras verificar que el entorno local ya usa MySQL (Laragon) y que un desarrollador solo, en 30 días, no gana nada agregando Docker ni PostgreSQL a lo que ya tiene funcionando.
- Un dominio ya comprado, disponible para usarse cuando el prototipo esté listo para mostrarse.
- Ninguno de los dos se usa como entorno de producción real todavía — el droplet cumple hoy, a la vez, el rol de staging y de "producción" del prototipo del sprint de 30 días (ver `docs/development/devops.md`).

---

## Hecho hasta ahora

- [x] Fase 1 completa y commiteada.
- [x] Roadmap ajustado tras investigación real: Mercado Libre se integra antes que TikTok Shop (TikTok Shop no tenía onboarding de vendedor local abierto en Colombia a la fecha de la investigación — re-validar en `docs/research/tiktok-shop.md` antes de construir esa integración).
- [x] Alcance del sprint de 30 días acordado: prototipo funcional, no producción.
- [x] Decisión de infraestructura tomada: MySQL en vez de PostgreSQL, despliegue nativo (sin Docker) en vez de contenedores, para todos los entornos de este prototipo — ver `docs/adr/ADR-002.md`.
- [x] Entorno local listo: PHP subido de 8.1.10 a **8.3.32** (instalado y configurado a mano dentro de Laragon, con las mismas extensiones que ya usaba la 8.1: pdo_mysql, mysqli, gd, mbstring, curl, openssl, zip, bcmath, intl, exif, fileinfo). MySQL 8.0.30 corriendo, Composer y Node confirmados. **Decisión operativa:** no usamos Apache/httpd de Laragon (dio conflicto de DLLs al cambiar de versión de PHP, típico de Apache+PHP en Windows, y de todas formas no lo necesitamos) — Laragon queda solo para MySQL; Laravel corre con su propio servidor (`php artisan serve`) y Next.js con el suyo (`npm run dev`).
- [x] `apps/api` creado (Laravel 13, PHP 8.3.32), conectado a MySQL (`proyecto_alfa`, migrado) y Redis (vía `predis`, porque la extensión nativa de Redis no está disponible en Windows). Corre con `php artisan serve` en `http://127.0.0.1:8000` — sin Apache.
- [x] `apps/web` creado (Next.js 16, TypeScript, Tailwind, App Router). Corre con `npm run dev` en `http://localhost:3000`.
- [x] `backend/` y `frontend/` (legacy, vacíos) eliminados — la migración a `apps/` que estaba pendiente en el README ya se hizo directamente, sin paso intermedio.
- [x] **Semana 1 — Cimientos: completa.** Modelo de datos núcleo con `tenant_id` (`tenants`, `categorias`, `productos`, `clientes`, `pedidos`, `detalle_pedidos`, más `tenant_id` en `users`), estructura modular real (`app/Modules/Catalogo`, `app/Modules/Pedidos`, `app/Shared`) siguiendo `docs/architecture/arquitectura-backend.md`. Scoping automático por tenant (`TenantScope` + trait `BelongsToTenant`) **probado end-to-end**: un segundo tenant de prueba solo veía su propio producto, nunca los del piloto — ver detalle en Referencias. Autenticación con Sanctum (`POST /api/v1/login`, `/me`, `/logout`) funcionando. Sembrado el piloto real: tenant "Skincare Piloto", usuario `admin@skincarepiloto.test` / `password`, 3 categorías y 5 productos de skincare de ejemplo.
- [x] **Semana 2 — Tienda: completa (pendiente de que el usuario la pruebe en el navegador — ver nota abajo).** Rutas públicas sin login (`/api/v1/tienda/productos`, `/tienda/productos/{slug}`, `/tienda/pedidos`) resueltas contra un tenant fijo por configuración (`ResolvePublicTenant`, ver Notas técnicas). `apps/web` ya no muestra la página default de Next.js: inicio con catálogo real, detalle de producto, carrito (estado en `localStorage`, `CarritoContext`), checkout que crea el pedido en `pendiente_pago` sin pasarela de pago real. Validación de stock en el checkout devuelve `STOCK_INSUFICIENTE` con el formato de error estándar del proyecto.
- [x] **Semana 3 — Panel: completa (pendiente de que el usuario la pruebe en el navegador).** `apps/admin` (Next.js, puerto 3001) con login (Sanctum), CRUD de productos (crear/editar/eliminar, con slug único autogenerado y categorías), y gestión de pedidos (listar filtrando por estado, ver detalle, **confirmar** — descuenta stock de verdad, con `lockForUpdate` para que dos confirmaciones simultáneas no descuenten stock que ya no existe — y **cancelar**, que devuelve el stock si el pedido ya estaba confirmado). Probado con `curl` de punta a punta: crear/editar producto, aislamiento entre tenants en los endpoints nuevos, confirmar pedido (stock baja), cancelar pendiente (stock no se toca), cancelar confirmado (stock se devuelve), doble confirmación (rechazada con `409 ESTADO_INVALIDO`).
- [~] **Semana 4 — Confiabilidad: en curso.** Pasada de hardening ya hecha (ver abajo); falta cargar el catálogo real (bloqueado en un dato de negocio, no técnico) y que el usuario pruebe todo en su navegador.
  - [x] Rate limiting activado (no existía pese a estar documentado): 120/min en tienda pública, 10/min específico para crear pedidos (evita spam de pedidos falsos), 120/min por usuario en el panel. Error `429 RATE_LIMIT_EXCEDIDO` con el mismo formato estándar, probado forzando el límite de verdad.
  - [x] Mensajes de validación en español (`laravel-lang/lang`, `lang:add es`) — antes salían en inglés pese a `APP_LOCALE=es`, porque Laravel no trae español integrado.
  - [x] Nombre del tenant de prueba limpiado: era literalmente `"Skincare Piloto (nombre por definir)"` y se mostraba tal cual en el panel y la tienda — ahora `"Skincare Piloto"`, presentable mientras se define el nombre real.
  - [x] `apps/web/.env.example` y `apps/admin/.env.example` agregados (no existían — sin esto, nadie sabría qué variable de entorno hace falta al clonar el repo).
  - [x] **Catálogo de ejemplo ampliado con imagen**: se agregó `imagen_url` al modelo de `Producto` (nullable) y se muestra en la tienda (grilla, detalle) y en el panel (miniatura en la lista, campo con vista previa en el formulario). El catálogo de prueba pasó de 5 a **14 productos en 6 categorías** (limpieza, tónicos y esencias, sueros y tratamientos, hidratación, protección solar, mascarillas y labios), con nombres de productos de skincare coreano realmente virales (investigado por web: [Furylist — 18 Viral Beauty Products of 2026](https://furylist.com/18-viral-beauty-products-of-2026-that-tiktok-cant-stop-talking-about/)) y fotografía de stock libre de derechos (Unsplash) elegida por categoría — a propósito **no** son fotos reales de esas marcas, usar la foto real de un producto ajeno sí sería un problema de derechos de autor aunque sea solo un prototipo local. Incluye un producto con stock 0 a propósito para poder ver el estado "Agotado" en la tienda.
  - [x] **Subida real de fotos** en el panel (antes solo se podía pegar una URL): el formulario de producto ahora tiene un campo de archivo (JPG/WEBP, máx. 4 MB) con vista previa. El backend guarda el archivo en `storage/app/public/productos` (servido vía `php artisan storage:link`, ya corrido), reemplaza y borra la foto anterior si se sube una nueva, y borra el archivo al eliminar el producto (antes se quedaba huérfano en disco — se encontró y se corrigió durante esta misma sesión). Probado de punta a punta con `curl -F`: subir, reemplazar (borra la vieja), editar sin tocar la foto (la conserva), eliminar (borra el archivo). Angie (socia, diseñadora/fotógrafa) va a suministrar las fotos reales de producto — especificaciones que se le pidieron: cuadradas, fondo neutro/blanco, mínimo 1000×1000 px, mismo estilo de iluminación en todas para que la tienda se vea cohesiva.
  - [~] **Investigación de Angie sobre chokchok.co y rosavainilla.co** (competencia real en Colombia) trajo una lista de funcionalidades deseadas — quedó triada, no implementada todavía: fácil/pendiente (barra de envío gratis, Quiénes somos, Contáctanos, sección "más vendidos"), esfuerzo medio (filtro por necesidad de piel, rutinas sugeridas), grande/después del piloto (club de fidelización con puntos, quiz de tipo de piel + blog, comparador antes/después). Confirma que el modelo de negocio real es reventa curada de K-beauty en Colombia — coincide con el catálogo de ejemplo ya cargado.

## Próximo paso concreto

Dos cosas en paralelo, ninguna depende de la otra:
1. **Dato de negocio pendiente (no técnico):** catálogo real de la tienda de skincare (nombres, precios, fotos si hay) para reemplazar los 5 productos de ejemplo — solo el usuario/Angie lo tienen.
2. **El usuario prueba en su navegador real** el flujo interactivo completo (carrito y checkout en `apps/web`; login, productos y pedidos en `apps/admin`) — todo lo que se pudo probar sin navegador ya está verificado con `curl`.

Con el catálogo real cargado y el navegador confirmado, la Semana 4 (y el sprint de 30 días) queda cerrada.

### Cómo levantar el entorno en una sesión nueva

1. Laragon: solo necesitas que **MySQL** esté iniciado (no Apache).
2. Redis: corre como **servicio de Windows** (`RedisProyectoAlfa`) — no depende de que Laragon ni ninguna terminal estén abiertos. Si por algo dejara de estar activo: `Start-Service RedisProyectoAlfa` en PowerShell.
3. API: `cd apps/api && php artisan serve --port=8000`
4. Tienda pública: `cd apps/web && npm run dev -- --port 3000`
5. Panel administrativo: `cd apps/admin && npm run dev -- --port 3001`
6. La versión activa de PHP debe ser la 8.3.32 (`C:\laragon\bin\php\php-8.3.32-Win32-vs16-x64`), no la 8.1.10 vieja.
7. Panel: entrar en `http://localhost:3001` con `admin@skincarepiloto.test` / `password`.
8. Tienda pública: abrir `http://localhost:3000` directamente, sin login.

**Nota sobre procesos en segundo plano:** lanzar `php artisan serve` o `npm run dev` con `nohup ... &` desde bash no sobrevive entre turnos de una sesión de Claude Code — hay que lanzarlos con `Start-Process` de PowerShell (o dejarlos corriendo en una terminal propia del usuario) para que no se caigan solos.

### Notas técnicas para la próxima sesión

- Laravel 13 usa atributos PHP (`#[Fillable([...])]`, `#[Hidden([...])]`) en vez de las propiedades `$fillable`/`$hidden` — revisar `app/Models/User.php` como referencia antes de escribir un modelo nuevo, no asumir la sintaxis clásica.
- `tenant_id` es **intencionalmente no-fillable** en los modelos de negocio (Producto, Categoria, etc.): se asigna solo automáticamente vía `BelongsToTenant` cuando hay un `currentTenantId` resuelto (middleware `resolve-tenant`), nunca desde el cuerpo de una petición. `firstOrCreate`/`updateOrCreate` sí lo aceptan porque usan `forceFill` internamente — es la excepción esperada, no un bug.
- `apps/web` trae su propio `apps/web/CLAUDE.md` (apunta a `apps/web/AGENTS.md`): advierte que Next.js 16 tiene cambios que rompen con versiones anteriores — revisar `node_modules/next/dist/docs/` antes de escribir código de Next.js nuevo, no asumir patrones de versiones anteriores.
- **`ModelNotFoundException` no llega como tal a `bootstrap/app.php`**: Laravel la convierte a `Symfony\Component\HttpKernel\Exception\NotFoundHttpException` antes de pasar por los `render()` personalizados — hay que capturar `NotFoundHttpException`, no `ModelNotFoundException`, para que el 404 salga con el formato de error del proyecto.
- **La tienda pública resuelve el tenant contra un slug fijo** (`config('tenant.slug_publico_por_defecto')`, hoy `skincare-piloto`, ver `App\Shared\Http\Middleware\ResolvePublicTenant`) porque solo existe un tenant. El día que haya más de uno sirviendo tienda pública, esto se reemplaza por resolución real por dominio — es el único punto que hay que tocar.
- **Redis corre como servicio de Windows** (`RedisProyectoAlfa`), no como proceso suelto — se instaló así porque los procesos lanzados con `nohup` (bash) o incluso `Start-Process` sin ser servicio se caían entre turnos de la sesión. `php artisan serve` y `npm run dev` sí se relanzan con `Start-Process` de PowerShell cuando hace falta (más simples, se reinician rápido), no valía la pena convertirlos en servicio.
- **`/login` y `/me` devuelven la misma forma de usuario** (`{id, nombre, email, tenant: {id, nombre}}`) a propósito — al principio no era así (`/login` devolvía `tenant_id` suelto) y rompía cualquier pantalla que confiara en la respuesta del login sin esperar a `/me`. Si se agrega un campo al usuario, agregarlo en el método privado `formatearUsuario()` de `AuthController`, no en cada endpoint por separado.
- **No uses route model binding implícito (`Producto $producto` en la firma del controlador) para recursos con scope de tenant.** Se probó y el orden real de ejecución entre `SubstituteBindings` (que resuelve el binding) y nuestro middleware `resolve-tenant` no está garantizado por Laravel — existe el riesgo real de que el producto se busque antes de que el tenant esté resuelto, lo que dejaría pasar acceso entre tenants. La solución usada en todo el panel: recibir el `id` como `int` en la firma y hacer `Producto::findOrFail($id)` **dentro** del método del controlador, donde ya se garantiza que todo el middleware terminó de ejecutar. Verificado con un tenant de prueba: sin este cuidado, hubiera sido un hueco de seguridad real, no teórico.
- **`apps/admin` es CSR** (Client-Side Rendering): todas las páginas del panel son Client Components (`"use client"`) que piden los datos ellas mismas con `useEffect` + `apiFetch`, porque el token de sesión vive en `localStorage` del navegador y un Server Component de Next.js no tiene acceso a eso. `apps/web` (la tienda) es distinto: ahí sí se usa Server Components para el catálogo, porque no depende de sesión — ver `docs/architecture/arquitectura-frontend.md`.

## Decisiones pendientes que no son técnicas

- Nombre de la tienda de skincare.
- Catálogo real de productos a cargar en la Semana 4.
