# Contrato de API

## Propósito

Definir el contrato general que toda la API de Proyecto Alfa (Laravel, consumida por `apps/web`, `apps/admin` y, a futuro, clientes de terceros) debe cumplir: cómo se autentica una petición, cómo se identifica el tenant, qué forma tiene una respuesta, cómo se versiona, cómo se limita el abuso y cómo se garantiza que una operación crítica no se ejecute dos veces por error. Este documento fija el **porqué** de cada regla; el detalle prescriptivo (nombres exactos de headers, catálogos de códigos de error, ejemplos exhaustivos endpoint por endpoint) vive en `docs/standards/api.md`.

---

## Objetivo

Que cualquier cliente HTTP — `apps/web`, `apps/admin`, una futura app móvil, o un integrador externo del modelo SaaS — pueda consumir la API sin ambigüedad sobre autenticación, forma de respuesta, errores, paginación o compatibilidad entre versiones, y que el propio equipo de backend tenga un único patrón que aplicar a cada endpoint nuevo en vez de decidirlo caso por caso.

---

## Alcance

Cubre: mecanismo de autenticación y resolución de tenant, formato estándar de respuesta JSON (éxito y error), paginación, versionado de rutas, rate limiting, e idempotencia en operaciones críticas (ej. creación de pedidos, confirmación de pagos).

No cubre: catálogo exhaustivo de endpoints y sus payloads (documentación viva de la API, ej. OpenAPI/Postman), reglas operativas letra por letra de cada header y código de error (`docs/standards/api.md`), ni la estructura interna del backend que produce estas respuestas (`arquitectura-backend.md`).

---

## Problema que resuelve

Una API que crece sin contrato explícito termina con cada endpoint devolviendo errores en una forma distinta, paginación inconsistente entre módulos, y ningún mecanismo para saber si un tenant fue resuelto correctamente. En un sistema multi-tenant esto es peor: un fallo al identificar el tenant de una petición no es un bug cualquiera, es una fuga de datos entre negocios. Y en operaciones de negocio crítico — crear un pedido, confirmar un pago — un reintento de red sin idempotencia puede duplicar una venta o cobrar dos veces. Este documento fija las reglas que evitan esos tres problemas antes de escribir el primer controlador.

---

## Principios

1. **La API es la única puerta.** Ningún dato de negocio se sirve fuera de estos contratos (ver `vision-tecnica.md`, principio API-first).
2. **El tenant se resuelve una sola vez, temprano, y de forma explícita.** Nunca se infiere implícitamente a mitad de un servicio.
3. **La forma de la respuesta es predecible.** Un cliente puede parsear cualquier respuesta de éxito o error sin conocer el endpoint de antemano.
4. **Versionar es más barato que romper.** Un cambio incompatible se lanza como versión nueva, nunca como mutación silenciosa de la actual.
5. **Toda operación que mueve dinero, stock o compromisos con el cliente es idempotente por diseño**, no como parche posterior a un incidente.
6. **El límite de uso protege al sistema, no castiga al cliente legítimo.** El rate limiting existe para contener abuso y picos anómalos, con márgenes generosos para el uso normal.

---

## Reglas

### Autenticación y resolución de tenant

- Toda petición autenticada usa un token portador (Laravel Sanctum) en el header `Authorization: Bearer <token>`. No se aceptan credenciales en query string.
- El tenant de la petición se resuelve a partir del token autenticado (el usuario pertenece a un tenant) o, para endpoints públicos de la tienda (`apps/web`), a partir del dominio/subdominio de la petición (ej. `tienda-piloto.proyectoalfa.com` → tenant "tienda-piloto"). El detalle de resolución por dominio vive como decisión futura (ver más abajo).
- El `tenant_id` resuelto se inyecta como contexto de request antes de llegar a cualquier controlador (vía middleware); ningún controlador ni servicio confía en un `tenant_id` que venga del body o de un parámetro de query.
- Toda query a base de datos generada durante la petición queda automáticamente acotada a ese `tenant_id` (ver `base-de-datos.md`); un endpoint que necesite cruzar tenants (ej. panel interno de operaciones de Proyecto Alfa) se marca y audita explícitamente.
- Reglas exactas de nombres de headers, formato del token y expiración: `docs/standards/api.md`.

### Formato de respuesta

- Toda respuesta exitosa se envuelve en un sobre estándar:
  ```json
  {
    "data": { "id": "ord_123", "estado": "confirmado" },
    "meta": { "version": "v1" }
  }
  ```
- Toda respuesta de error usa la misma forma en todos los módulos:
  ```json
  {
    "error": {
      "codigo": "STOCK_INSUFICIENTE",
      "mensaje": "No hay unidades disponibles del producto solicitado.",
      "detalles": { "producto_id": "prod_789", "disponible": 0 }
    }
  }
  ```
- `codigo` es estable y apto para lógica de cliente (ej. mostrar un mensaje distinto); `mensaje` es para humanos y puede cambiar de redacción sin romper integraciones.
- Catálogo completo de códigos de error por módulo: `docs/standards/api.md`.

### Paginación

- Toda colección se pagina por defecto; no existen endpoints de listado que devuelvan "todo" sin límite.
- Se usa paginación basada en cursor para colecciones que pueden crecer sin cota y se consultan con frecuencia (pedidos, movimientos de inventario), y paginación por página/offset para colecciones acotadas y de bajo volumen (ej. lista de transportadoras configuradas).
- Toda respuesta paginada incluye `meta.pagina_actual` (u orígen de cursor), `meta.total` cuando calcularlo no sea costoso, y un enlace o cursor explícito a la página siguiente.

### Versionado

- Toda ruta vive bajo un prefijo de versión mayor: `/api/v1/...`.
- Un cambio incompatible (quitar un campo, cambiar su tipo, cambiar el significado de un código de estado) exige una nueva versión (`/api/v2/...`); un cambio aditivo (nuevo campo opcional, nuevo endpoint) no la exige.
- Una versión se retira solo después de un período de convivencia anunciado con los consumidores conocidos (hoy: `apps/web` y `apps/admin`; a futuro: integradores del modelo SaaS).

### Rate limiting

- Todo endpoint autenticado tiene un límite por tenant y por token, no solo por IP (una IP puede representar a muchos tenants detrás de NAT o CDN).
- Los endpoints públicos de `apps/web` (catálogo, checkout) tienen un límite más generoso pensado para tráfico real de compradores, pero acotado para contener bots y scraping agresivo.
- Los endpoints que disparan trabajo costoso o externo (ej. recotizar envío con una transportadora, regenerar contenido con IA) tienen un límite más estricto que un endpoint de solo lectura sobre datos ya cacheados.
- Al superar el límite, la API responde `429` con el sobre de error estándar y un código `RATE_LIMIT_EXCEDIDO`, incluyendo cuándo reintentar.

### Idempotencia en operaciones críticas

- Toda operación de escritura que, repetida por error, cause daño de negocio (crear un pedido, confirmar un pago, generar una guía de envío) exige un header `Idempotency-Key` enviado por el cliente.
- El backend guarda la clave de idempotencia junto al resultado de la primera ejecución (con expiración razonable, ej. 24h) y, ante una repetición con la misma clave, devuelve la misma respuesta sin repetir el efecto de negocio.
- Un reintento de red (timeout, caída de conexión) durante el checkout es el caso que esta regla existe para resolver: el cliente reintenta con la misma clave y nunca duplica el pedido.
- El detalle de implementación (dónde se guarda la clave, tiempo de vida, qué endpoints la exigen obligatoriamente) vive en `docs/standards/api.md`.

---

## Ejemplos

- **Crear un pedido:** `POST /api/v1/pedidos` con header `Idempotency-Key: chk_9f3a...` generado por el frontend al iniciar el checkout. Si la respuesta se pierde por un corte de red y `apps/web` reintenta automáticamente, el backend detecta la clave repetida y devuelve el mismo pedido ya creado, sin generar un segundo.
- **Resolución de tenant en tienda pública:** una visita a `mitienda.proyectoalfa.com/productos` resuelve el tenant por dominio antes de tocar el catálogo; una visita a `admin.proyectoalfa.com` resuelve el tenant por el usuario autenticado en el token.
- **Error de negocio típico:** al intentar confirmar un pedido sin stock suficiente, la API responde `422` con `codigo: "STOCK_INSUFICIENTE"`, que `apps/web` usa para mostrar "solo quedan 2 unidades" sin tener que parsear el mensaje en español.
- **Rate limiting diferenciado:** el endpoint de regenerar descripción de producto con IA (`POST /api/v1/catalogo/productos/{id}/descripcion-ia`) tiene un límite de, por ejemplo, 20 solicitudes por hora por tenant; el endpoint de listar productos del mismo tenant permite miles.

---

## Casos límite

- **Token válido pero de un tenant suspendido o dado de baja:** la petición se rechaza con `403` y un código explícito (`TENANT_SUSPENDIDO`), no con un `401` genérico que sugiera credenciales inválidas.
- **Idempotency-Key reutilizada con un payload distinto:** se considera un error del cliente (`409 CONFLICTO_IDEMPOTENCIA`); la clave identifica una operación, no se reinterpreta con datos nuevos.
- **Cliente que no envía `Idempotency-Key` en un endpoint que la exige:** se rechaza con `400` antes de ejecutar cualquier efecto, en vez de ejecutar la operación sin protección.
- **Consumidor externo (futuro integrador SaaS) en una versión de API retirada:** recibe un error explícito de versión no soportada con referencia a la versión vigente, no un `404` silencioso.

---

## Decisiones futuras

- Mecanismo exacto de resolución de tenant por dominio/subdominio para `apps/web` (dominio propio del tenant vs. subdominio de Proyecto Alfa) cuando exista más de un tenant real.
- Si se adopta OpenAPI como fuente formal del contrato (generación de documentación y de clientes tipados para `apps/web`/`apps/admin`) o se mantiene documentación manual en `docs/standards/api.md`.
- Política formal de deprecación y retiro de versiones cuando existan integradores externos del modelo SaaS.
- Límites numéricos exactos de rate limiting por tipo de endpoint (hoy son principios de diseño, no cifras cerradas): se fijarán con datos reales de tráfico del piloto.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — principio API-first y multi-tenant que este contrato aplica.
- `docs/architecture/arquitectura-backend.md` — cómo se implementan estos contratos dentro de cada módulo Laravel.
- `docs/architecture/seguridad.md` — autenticación, autorización y auditoría en detalle.
- `docs/standards/api.md` — reglas operativas detalladas (headers exactos, catálogo de códigos de error, ejemplos por endpoint) — en construcción por otro colaborador.

---

## Historial

- **2026-07-27** — Primera versión.
