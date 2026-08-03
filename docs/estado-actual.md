# Estado Actual del Proyecto

> Este documento se actualiza en cada sesión de trabajo relevante. No es un historial completo — para eso está el historial de git y el `## Historial` de cada documento formal. Aquí solo vive **el presente**: en qué fase estamos y qué sigue.

**Última actualización:** 2026-08-03

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
  - [x] **Investigación de Angie sobre chokchok.co y rosavainilla.co** (competencia real en Colombia) implementada, salvo lo que quedó explícitamente para después:
    - **Galería opcional de fotos por producto** (además de la foto de portada): tabla `producto_imagenes`, subir varias a la vez, borrar una, se limpia el archivo físico también al reemplazar/borrar el producto completo. Panel: sección debajo del formulario, sube al elegir el archivo (no hace falta "Guardar"). Tienda: galería con miniaturas clicables en el detalle del producto.
    - **Producto destacado** (`destacado` boolean) + sección "Más vendidos" en el home de la tienda (se oculta cuando hay un filtro de necesidad activo).
    - **Necesidades de piel** (Acné, Cuidado antiedad, Manchas, Poros, Puntos negros, Rojez, Textura, Luminosidad) como tags de muchos-a-muchos sobre `Producto` — filtro por chips en el home (`?necesidad=slug`), tags visibles en el detalle del producto, selector de checkboxes en el formulario del panel.
    - **Barra de envío gratis** (solo texto, sin lógica real de envío todavía — eso es Fase 3, comparador de transportadoras), enlaces en el header y el footer de la tienda.
    - **Contenido institucional editable desde el panel**: se agregaron `quienes_somos`, `contacto_whatsapp`, `contacto_email`, `contacto_horario` al `Tenant` (4 campos, no se justificaba una tabla de contenido genérica). Página **Configuración** en el panel (`/negocio`) para editarlos; las páginas **Quiénes somos** y **Contáctanos** de la tienda ya no son texto estático, leen esto de la API. El usuario pidió inventar el contenido de partida en vez de bloquear el prototipo — el texto y los datos de contacto son inventados a propósito (número/correo con formato claramente de ejemplo) y quedan listos para reemplazarse desde el panel cuando Angie defina el contenido real.
    - **Rutinas sugeridas** (3/5/10 pasos): antes quedaron fuera porque "necesitan que Angie decida qué va en cada una" — con la misma instrucción de inventar y ajustar después, se creó el modelo (`Rutina`, muchos-a-muchos ordenado con `Producto` vía `rutina_producto.orden`) y se sembraron 3 rutinas de ejemplo usando el catálogo ya existente. Página `/rutinas` en la tienda (pasos numerados, enlazan al producto, precio total de la rutina); CRUD completo en el panel con un selector de productos que preserva el orden de selección y permite reordenar con flechas ↑/↓.
    - **Sigue fuera a propósito** (esto sí es una limitación de arquitectura, no de datos faltantes): club de fidelización, quiz de tipo de piel + blog, comparador antes/después — las tres requieren cuentas de cliente con login en la tienda pública, que hoy no existen (el checkout es de invitado). Construirlas de verdad implica agregar autenticación de clientes primero, no es un ajuste de una sesión — queda para después del piloto (Fase 4/5).
    - Confirma que el modelo de negocio real es reventa curada de K-beauty en Colombia — coincide con el catálogo de ejemplo ya cargado la sesión anterior.
- [x] **El prototipo ya está desplegado de verdad, no solo local.** Droplet de pruebas del usuario (`InviteArt`, ya tenía otros proyectos corriendo — Predictor, emcosalud, trendhub, etc.), con OpenLiteSpeed + MariaDB + Redis + PM2 ya instalados de antes. URLs reales, con HTTPS:
  - Tienda: **https://skincare.alegrarte.store**
  - Panel: **https://skincare-admin.alegrarte.store** (mismo login: `admin@skincarepiloto.test` / `password`)
  - API: **https://skincare-api.alegrarte.store**
  Ver "Cómo desplegar cambios nuevos" y "Notas técnicas del despliegue" más abajo para el detalle de cómo quedó armado y los tropiezos reales que hubo (varios, todos resueltos).
- [x] **Suite de pruebas automatizadas real** (antes todo el aislamiento multi-tenant y los flujos críticos se verificaban a mano con `curl`/tinker, sin nada que impidiera una regresión futura). `phpunit.xml` reconfigurado para correr contra MySQL real (`proyecto_alfa_test`, no sqlite — ni está instalada la extensión ni tendría sentido validar contra un motor distinto al real, ver ADR-002) y para escanear tests colocados junto a cada módulo (`app/Modules/*/Tests`, `app/Shared/Tests`), siguiendo la convención ya escrita en `docs/development/testing.md`. Trait compartido `tests/Concerns/CreaNegocios.php` con helpers para crear tenants/usuarios/productos/pedidos de prueba. **14 tests, 53 aserciones, todos verdes**, cubriendo los 3 flujos críticos del documento: login y aislamiento multi-tenant (`AutenticacionYAislamientoTenantTest`), checkout con validación de stock (`CheckoutStockTest`), y confirmar/cancelar pedido con sincronización de inventario (`ConfirmarCancelarPedidoTest`) — este último incluye la aserción negativa de que un tenant no puede confirmar, cancelar ni ver pedidos de otro tenant, y que confirmar el pedido de un tenant nunca toca el stock de un producto homónimo de otro tenant.
- [x] **Script de respaldo automático** (`scripts/respaldo.sh`) para el droplet de pruebas: hace `mysqldump` comprimido de la base de datos y `tar.gz` de las fotos de producto subidas, guardándolos fuera del repo (`/var/backups/skincare`, así un `git pull` nunca los toca) y con rotación de 7 días (el disco del droplet ya está al ~86%, dejar crecer los respaldos sin límite lo llenaría). Las credenciales se leen del `.env` real de la app, nunca quedan hardcodeadas en el script. **Falta instalar el cron en el droplet y probarlo de verdad** (paso siguiente, requiere SSH del usuario — ver "Próximo paso concreto").

## Próximo paso concreto

1. **Instalar el cron del respaldo en el droplet y probarlo de verdad** (lo único técnico que falta de esta sesión — el script `scripts/respaldo.sh` ya está escrito, probado el diseño, pero no ejecutado en el servidor real). Por SSH:
   ```bash
   ssh root@104.248.51.210
   cd /var/www/skincare && git pull
   mkdir -p /var/backups/skincare/db /var/backups/skincare/fotos
   bash scripts/respaldo.sh              # correrlo una vez a mano primero
   cat /var/backups/skincare/respaldo.log   # confirmar que dice "terminado" sin ERROR
   ls -lh /var/backups/skincare/db /var/backups/skincare/fotos   # confirmar que el .sql.gz existe y no está vacío
   crontab -e
   # agregar esta línea (respaldo diario a las 3am hora del servidor):
   0 3 * * * /bin/bash /var/www/skincare/scripts/respaldo.sh
   ```
   Después de instalar el cron, esperar al día siguiente (o forzar la hora del sistema/ajustar temporalmente la línea de cron a "dentro de 2 minutos" para probarlo sin esperar) y confirmar en `respaldo.log` que corrió solo.
2. **Catálogo real de la tienda de skincare** (nombres, precios, fotos) para reemplazar el catálogo de ejemplo — solo el usuario/Angie lo tienen. Las fotos se suben desde el panel (`/productos/{id}/editar`, campo de archivo + galería opcional).
3. **Probar el flujo completo en el navegador real**, ya sobre la URL pública — agregar al carrito y pagar en la tienda, y en el panel confirmar ese pedido y ver que el stock baje.

Con eso, la Semana 4 (y el sprint de 30 días) queda cerrada.

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
- **Eloquent pluraliza en inglés, no en español** — con nombres de modelo irregulares en español se equivoca y hay que fijar `protected $table` a mano. Ya pasó dos veces: `Necesidad` → Eloquent buscaba `necesidads` (tabla real: `necesidades`), `ImagenProducto` → Eloquent buscaba `imagen_productos` (tabla real: `producto_imagenes`). Mismo problema aplica a `constrained()` en migraciones cuando el nombre de la FK no coincide con el plural que Eloquent adivina — hay que pasarle el nombre de tabla explícito: `constrained('necesidades')`. Antes de crear un modelo nuevo con nombre en español que no sea un plural regular (`-s` simple), revisar si hace falta fijar `$table` a mano.
- **El `curl` de Windows (el que trae Git Bash) no puede subir arrays de archivos** (`-F "campo[]=@archivo"`) — falla con `curl: (26) Failed to open/read local data from file/application` por cómo maneja los corchetes, con o sin `-g`/`--globoff`. Para probar endpoints que reciben `imagenes[]` (o cualquier campo tipo array con archivos), usar Python (`requests`, ya disponible) en vez de `curl -F`. Ojo también: en este entorno, el `/tmp` de Git Bash y el `python3` del sistema NO apuntan a la misma carpeta (`/tmp` de bash = `C:\Users\<usuario>\AppData\Local\Temp`; hay que usar la ruta Windows completa al pasarle un archivo a Python).

### Cómo desplegar cambios nuevos al droplet

Manual a propósito, no hay webhook todavía:

```bash
ssh root@104.248.51.210
cd /var/www/skincare
bash scripts/deploy.sh
```

El script hace `git pull`, reinstala dependencias, migra, reconstruye `apps/web` y `apps/admin`, y reinicia los procesos de PM2. **No corre el seeder** (borraría/duplicaría datos reales cargados desde el panel). Ver `scripts/deploy.sh` para el detalle exacto.

### Respaldos automáticos (droplet de pruebas)

`scripts/respaldo.sh` — corre por cron una vez al día (instalación manual, ver "Próximo paso concreto"), hace `mysqldump` comprimido de `proyecto_alfa` y `tar.gz` de `storage/app/public/productos`, los guarda en `/var/backups/skincare` (fuera del repo, así `git pull` nunca los borra) con rotación de 7 días. Las credenciales de la BD se leen del `.env` real de la app en cada corrida, no están escritas en el script. Para restaurar un respaldo:

```bash
gunzip -c /var/backups/skincare/db/proyecto_alfa_AAAAMMDD_HHMMSS.sql.gz | mysql -u root -p proyecto_alfa
tar -xzf /var/backups/skincare/fotos/productos_AAAAMMDD_HHMMSS.tar.gz -C /var/www/skincare/apps/api/storage/app/public/productos
```

Pendiente a futuro (fuera de alcance del prototipo de 30 días, anotado para no perderlo): copiar los respaldos fuera del droplet (a un bucket o al menos al equipo local) — hoy si el droplet completo se pierde, los respaldos se pierden con él.

### Notas técnicas del despliegue (droplet de pruebas)

Server compartido con otros proyectos del usuario — RAM muy ajustada (~1GB total) y disco al 86%. Todo lo de abajo salió de tropiezos reales durante el primer despliegue, no de teoría:

- **El puerto "obvio" casi nunca está libre en un server compartido.** `emcosalud` (otro proyecto del usuario) ya usaba el puerto 3000. Antes de asumir un puerto libre, revisar con `ss -ltnp | grep LISTEN`. Terminamos en 3001 (`skincare-web`) y 3002 (`skincare-admin`).
- **`pm2 start npm -- start -- --port 3000` es frágil** (dos capas de paso de argumentos, npm → next). Mejor invocar el binario de Next directo: `pm2 start node_modules/.bin/next --name X -- start -p PUERTO`.
- **LiteSpeed corre PHP como `www-data` (UID 33), no como `root`**, aunque el vhost tenga "External App Set UID Mode: DocRoot UID" — en la práctica el `lsphp` compartido corre con un usuario fijo. `storage/` y `bootstrap/cache/` deben ser escribibles por ese usuario o Laravel truena con 500 sin poder ni loguear el error (el log falla al intentar escribir). Arreglo: `chown -R root:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache`. El script de despliegue ya lo hace en cada corrida.
- **Diagnóstico de un 500 en producción (`APP_DEBUG=false`) sin ver nada en el navegador:** vaciar `storage/logs/laravel.log`, hacer una sola petición, y leer el archivo limpio (`> storage/logs/laravel.log && curl ... && cat storage/logs/laravel.log`) — así no te confunden líneas viejas de una petición anterior. Si el log queda **vacío** después de un 500, el error nunca llegó a Laravel (falla antes, a nivel de PHP/servidor) — probar con un `<?php phpinfo(); ?>` suelto para aislar si el problema es de PHP o específico de Laravel.
- **En un Virtual Host de LiteSpeed, el campo "Document Root" (pestaña General) no puede quedar en "Not Set", ni siquiera en un vhost que es 100% proxy reverso** (no sirve ningún archivo estático) — dejarlo vacío hace que el vhost nunca termine de activarse: LiteSpeed responde 404 sin loguear ningún error, como si el dominio no existiera. Este fue el bloqueo más largo de depurar porque el `Context` de tipo Proxy y el mapeo de dominio estaban perfectos — el síntoma no apuntaba para nada a "Document Root".
- **El mapeo dominio → vhost vive en el Listener** (`Virtual Host Mappings`), compartido por todos los vhosts que usan ese puerto — no es una propiedad del vhost en sí. Un vhost nuevo necesita que ALGUIEN agregue su dominio ahí explícitamente (tanto en el listener HTTP como en el HTTPS).
- **El reinicio "suave" de LiteSpeed (`lswsctrl restart`, manda `SIGUSR1`) a veces no recarga cambios de vhosts/listeners nuevos.** Si algo no aparece después de eso, probar con un ciclo completo: `lswsctrl stop && lswsctrl start` (corta un par de segundos TODOS los sitios del servidor, no solo el nuestro — aceptable en un servidor de pruebas, avisar si alguna vez se hace en uno real).
- **SSL: certbot en modo `standalone`** (el método que el usuario ya usaba en este servidor para sus otros proyectos) — necesita el puerto 80 libre, así que hay que parar LiteSpeed mientras corre. Un solo certificado cubrió los 3 subdominios a la vez (`certbot certonly --standalone -d dominio1 -d dominio2 -d dominio3`). Certbot configuró renovación automática solo, pero **el renovado no reinicia LiteSpeed por sí solo** — como decisión futura, agregar un deploy-hook de certbot que corra `lswsctrl restart` para que el certificado renovado se recargue solo cada ~90 días.
- **`apps/web` (Next.js) intenta pre-renderizar en build time por defecto.** Las páginas que hacen `await apiFetch(...)` a nivel de Server Component (home, detalle de producto, rutinas, quiénes somos, contáctanos) necesitan `export const dynamic = "force-dynamic"` — si no, Next.js intenta generarlas como HTML estático durante `npm run build`, lo que exige que la API esté alcanzable **en ese momento** (antes de que exista DNS/LiteSpeed para ella) y además serviría datos de catálogo congelados en vez de stock/precios reales. Ya aplicado en todas las páginas de datos.
- **`npm ci` falló en el droplet** ("lock file's picomatch@2.3.2 does not satisfy picomatch@4.0.5") por diferencias de resolución de dependencias entre Windows (donde se generó el lockfile) y Linux — se usó `npm install` en su lugar, más tolerante.
- **RAM ajustada:** builds de Next.js con `NODE_OPTIONS="--max-old-space-size=512"` para no acaparar memoria; hay 2GB de swap ya configurado en el servidor que absorbe los picos.
- **Redimensionar el droplet (más RAM/disco) desde el panel de DigitalOcean exige un reinicio** — la sesión SSH se corta sola, es normal. `pm2 startup` ya estaba configurado de antes en este servidor, así que los procesos de PM2 (incluidos los nuevos, después de un `pm2 save`) vuelven solos tras el reinicio; si un servidor nuevo NO tiene `pm2 startup` configurado, hay que correrlo una vez (genera un servicio systemd) además de `pm2 save`.

## Decisiones pendientes que no son técnicas

- Nombre de la tienda de skincare.
- Catálogo real de productos a cargar en la Semana 4.
