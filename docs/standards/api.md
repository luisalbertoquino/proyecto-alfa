# Reglas Operativas de la API

## Propósito

Dar el detalle prescriptivo, letra por letra, del contrato que `docs/architecture/apis.md` fija a nivel de principios: nombres exactos de headers, verbos HTTP por tipo de operación, códigos de estado, forma exacta de la respuesta JSON, paginación, cómo se identifica el tenant en cada request, y versionado. Donde `apis.md` explica el porqué, este documento da la regla lista para aplicar sin interpretación.

---

## Objetivo

Que cualquier desarrollador que construya un endpoint nuevo lo haga igual que todos los anteriores, sin tener que mirar cómo lo hizo otro módulo como referencia informal.

---

## Alcance

Cubre: verbos HTTP y su uso, códigos de estado HTTP por situación, forma exacta del sobre de respuesta (éxito y error), catálogo de convenciones de paginación, headers de autenticación y tenant, versionado de rutas, y la mecánica de idempotencia.

No cubre: el porqué de cada decisión (`docs/architecture/apis.md`), ni las convenciones de nombres de rutas y recursos (`docs/standards/naming.md`).

---

## Problema que resuelve

`docs/architecture/apis.md` fija los principios (sobre estándar, versionado, idempotencia) pero deja el detalle exacto para este documento. Sin este nivel de detalle, cada endpoint nuevo reinterpreta el principio a su manera: un módulo usa `200` donde otro usa `201`, uno pagina con `page` y otro con `cursor` sin razón, y el frontend termina con código especial por módulo para consumir la misma API.

---

## Principios

Los mismos seis principios de `docs/architecture/apis.md` aplican aquí sin cambios: la API es la única puerta, el tenant se resuelve una sola vez y temprano, la respuesta es predecible, versionar es más barato que romper, toda operación crítica es idempotente por diseño, y el rate limiting protege al sistema sin castigar al uso legítimo.

---

## Reglas

### Verbos HTTP

- `GET` — lectura, nunca cambia estado. Siempre cacheable en el borde (Cloudflare) cuando el recurso lo permite.
- `POST` — creación de un recurso nuevo, o una acción que no encaja en CRUD (ej. `POST /api/v1/envios/{id}/rastreo/reintentar`).
- `PATCH` — actualización parcial de un recurso existente. No se usa `PUT` en este proyecto (evita ambigüedad sobre reemplazo total vs. parcial).
- `DELETE` — eliminación (lógica, vía `deleted_at`, salvo justificación explícita para borrado físico).

### Códigos de estado

| Código | Cuándo |
|---|---|
| `200` | Lectura o acción exitosa que no crea un recurso nuevo |
| `201` | Creación exitosa; incluye el recurso creado en `data` |
| `202` | Aceptado para procesamiento asíncrono (ej. tarea despachada a cola) |
| `204` | Éxito sin cuerpo de respuesta (ej. `DELETE` exitoso) |
| `400` | Petición malformada (incluye falta de `Idempotency-Key` cuando es obligatorio) |
| `401` | No autenticado (token ausente o inválido) |
| `403` | Autenticado pero sin permiso, o tenant suspendido |
| `404` | Recurso no existe **o** no pertenece al tenant de la petición (nunca se distingue, para no filtrar existencia entre tenants) |
| `409` | Conflicto (ej. `Idempotency-Key` reutilizada con payload distinto) |
| `422` | Entidad válida en formato pero inválida en regla de negocio (ej. `STOCK_INSUFICIENTE`) |
| `429` | Rate limit excedido |
| `5xx` | Error del servidor; siempre logueado con `tenant_id` y trace, nunca expone detalle interno en la respuesta |

### Headers

- `Authorization: Bearer <token>` — autenticación Sanctum, obligatorio en toda ruta no pública.
- `Idempotency-Key: <uuid>` — obligatorio en `POST` de operaciones críticas (crear pedido, confirmar pago, generar guía de envío); generado por el cliente, único por intento de operación.
- `Accept: application/json` — asumido por defecto; la API no sirve otro formato de respuesta.
- El tenant **nunca** se envía como header ni parámetro explícito por el cliente: se resuelve del token o del dominio, tal como fija `apis.md`.

### Forma de la respuesta

- Éxito: `{ "data": ..., "meta": { "version": "v1", ... } }`.
- Error: `{ "error": { "codigo": "...", "mensaje": "...", "detalles": {...} } }`, con `codigo` en español `SCREAMING_SNAKE_CASE` (ver `docs/standards/naming.md`).
- `detalles` es siempre un objeto (puede ser `{}`), nunca `null`, para que el cliente no tenga que chequear su existencia antes de leerlo.

### Paginación

- Cursor (`meta.cursor_siguiente`) para colecciones no acotadas y de alto volumen: pedidos, movimientos de inventario.
- Página/offset (`meta.pagina_actual`, `meta.total_paginas`) para colecciones acotadas: transportadoras configuradas, proveedores.
- Parámro de query: `?cursor=...&limite=50` o `?pagina=1&por_pagina=20`. El límite por defecto es 20; el máximo permitido por parámetro es 100 (una petición con `limite=500` se recorta a 100, no se rechaza).

### Versionado

- Toda ruta bajo `/api/v1/...`. No hay rutas sin prefijo de versión, ni siquiera en desarrollo.
- Un campo nuevo opcional o un endpoint nuevo no requiere versión nueva. Quitar un campo, cambiar su tipo, o cambiar el significado de un código de estado sí la requiere.

### Idempotencia

- Endpoints que la exigen obligatoriamente: `POST /api/v1/pedidos`, `POST /api/v1/pagos/confirmar`, `POST /api/v1/envios/{id}/guia`. La lista crece con cada operación crítica nueva; se actualiza aquí al agregar una.
- La clave se guarda junto al `tenant_id` y al hash del payload, con expiración de 24 horas.

---

## Ejemplos

Ver ejemplos completos en `docs/architecture/apis.md` (creación de pedido con `Idempotency-Key`, resolución de tenant por dominio, error de negocio `STOCK_INSUFICIENTE`, rate limiting diferenciado para el endpoint de descripción con IA).

- Respuesta paginada por cursor:
  ```json
  {
    "data": [ { "id": "ped_1" }, { "id": "ped_2" } ],
    "meta": { "version": "v1", "cursor_siguiente": "eyJpZCI6InBlZF8yIn0=" }
  }
  ```

---

## Casos límite

Los mismos que `docs/architecture/apis.md` (token de tenant suspendido, `Idempotency-Key` reutilizada con payload distinto, falta de `Idempotency-Key` en endpoint obligatorio, consumidor en versión retirada) — este documento no los repite en detalle, solo asegura que los códigos de estado usados (`403`, `409`, `400`) coincidan con la tabla de arriba.

---

## Decisiones futuras

- Catálogo completo de códigos `codigo` de error por módulo, a medida que cada módulo se construye (hoy solo existen los ejemplos de `apis.md`).
- Límites numéricos exactos de rate limiting por endpoint (pendiente de datos reales de tráfico del piloto, según `apis.md`).
- Si se adopta OpenAPI como fuente formal generada automáticamente desde el código, en vez de mantener este catálogo a mano.

---

## Referencias

- `docs/architecture/apis.md` — el contrato y su porqué; este documento es su detalle operativo.
- `docs/standards/naming.md` — convención de nombres de rutas y claves JSON.
- `docs/standards/security.md` — validación de entrada obligatoria y manejo de autenticación.
- `templates/nuevo-endpoint.md` — checklist para documentar un endpoint nuevo siguiendo estas reglas.

---

## Historial

- **2026-07-27** — Primera versión.
