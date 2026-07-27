# Convenciones de Nombres

## Propósito

Fijar cómo se nombra cada cosa en Proyecto Alfa — módulos, clases, tablas, columnas, rutas de API, componentes React, ramas de git, variables de entorno — para que ningún desarrollador tenga que decidir "¿en español o en inglés?" o "¿singular o plural?" cada vez que crea algo nuevo. Sin esta regla, el mismo tipo de cosa termina nombrado de formas distintas en cada módulo y el costo de leer código ajeno sube con cada archivo nuevo.

---

## Objetivo

Que un nombre — de clase, tabla, ruta o rama — permita predecir a qué pertenece y qué hace sin tener que abrir el archivo, y que dos desarrolladores distintos, sin coordinarse, nombren la misma cosa de la misma forma.

---

## Alcance

Cubre convenciones de nombres para: namespaces de módulo, clases PHP (y sus sufijos técnicos), tablas y columnas de PostgreSQL, rutas de la API, componentes y hooks de React/Next.js, ramas de git, y variables de entorno.

No cubre: el formato del mensaje de commit (`docs/standards/commits.md`), el contrato completo de la API (`docs/standards/api.md`), ni el detalle de migraciones (`docs/standards/database.md`) — este documento solo fija cómo se llaman las cosas, no cómo se construyen.

---

## Problema que resuelve

`principios-de-arquitectura.md` ya fijó los nombres de los nueve módulos de dominio (`Catalogo`, `Pedidos`, `Inventario`, `Envios`, `Proveedores`, `Publicidad`, `Analitica`, `Canales`, `IA`) en español, y ya usa ejemplos como `InventarioService::verificarDisponibilidad()`, `TransportadoraInterface` con métodos `cotizar()`, `generarGuia()`, `rastrear()`, y el evento `StockActualizado`. `apis.md` ya fija rutas como `/api/v1/pedidos` y respuestas JSON con claves en español (`estado`, `mensaje`, `detalles`). Y `vision-tecnica.md` ya fija `tenant_id` en inglés como columna estándar. Sin una regla explícita que reconcilie esto, cada desarrollador nuevo tendrá que adivinar por qué unas cosas están en español y otras en inglés, y terminará mezclando ambos dentro de un mismo módulo.

---

## Principios

1. **El idioma del nombre depende de qué representa, no de dónde vive el archivo.** Vocabulario de negocio (lo que un dueño de tienda reconocería en una conversación: pedido, envío, proveedor, inventario) va en español. Vocabulario técnico de framework (lo que existe por decisión de Laravel, Eloquent o convención REST, no por el negocio: controlador, servicio, timestamp, id) va en inglés.
2. **Consistencia dentro de una categoría, no uniformidad forzada entre categorías.** No se traduce todo a un solo idioma "por prolijidad" cuando eso rompería una convención de framework (ej. `created_at`) o un nombre ya fijado en un documento de arquitectura (ej. `Pedidos`, `tenant_id`).
3. **Un nombre nuevo primero busca si ya existe un precedente** en `vision-tecnica.md`, `principios-de-arquitectura.md`, `apis.md` o en un módulo ya construido, antes de inventar una convención distinta.
4. **Nada de abreviaturas ambiguas.** `Inventario`, no `Inv`; `descripcion`, no `desc`; una abreviatura solo se usa si ya es estándar de la industria (`id`, `url`, `IA`).

---

## Reglas

### Namespaces de módulo (backend)

- Los nueve módulos de dominio usan exactamente los nombres ya fijados, sin tildes, en `PascalCase`, en español: `Catalogo`, `Pedidos`, `Inventario`, `Envios`, `Proveedores`, `Publicidad`, `Analitica`, `Canales`, `IA`.
- No se crean nuevos módulos de nivel superior sin pasar antes por `templates/nuevo-modulo.md`.

### Clases PHP (Laravel)

- **Nombre de negocio + sufijo técnico en inglés**, ambos en `PascalCase`: `PedidoController`, `InventarioService`, `TransportadoraInterface`, `EnvioCreado` (evento), `ProcesarPagoJob`.
- Sufijos técnicos estándar (siempre en inglés): `Controller`, `Service`, `Repository`, `Interface`, `Request`, `Resource`, `Job`, `Event`, `Listener`, `Policy`, `Exception`, `Middleware`.
- Modelos Eloquent: sustantivo de negocio en singular, español, `PascalCase`: `Pedido`, `Producto`, `Envio`, `Proveedor`. Sin sufijo.
- Métodos que representan una regla o acción de negocio: verbo en español, `camelCase`: `verificarDisponibilidad()`, `cotizar()`, `generarGuia()`, `rastrear()`.
- Métodos que existen por convención del framework (acciones CRUD de un controlador de recurso, `handle()` de un job/listener, hooks del ciclo de vida de Eloquent): se dejan en inglés tal como Laravel los define (`index`, `store`, `update`, `destroy`, `handle`, `boot`).

### Tablas y columnas (PostgreSQL)

- Nombre de tabla: plural, `snake_case`, español, derivado del nombre del modelo — `pedidos`, `productos`, `envios`, `proveedores`. Esto es lo que Eloquent infiere automáticamente del nombre del modelo, así que no requiere declarar `$table` a mano.
- Columnas de contenido de negocio: `snake_case`, español — `estado`, `fecha_entrega`, `direccion_destino`.
- Columnas estándar de sistema/framework: siempre en inglés, sin excepción — `id`, `tenant_id`, `created_at`, `updated_at`, `deleted_at`, y cualquier llave foránea con sufijo `_id` (`pedido_id`, `producto_id`). Estas existen por convención de Laravel/Eloquent, no por vocabulario de negocio, y `tenant_id` ya quedó fijado así en `vision-tecnica.md`.
- Detalle completo de convenciones de migración en `docs/standards/database.md`.

### Rutas de API

- Recurso en español, plural, `kebab-case` si es compuesto: `/api/v1/pedidos`, `/api/v1/catalogo/productos/{id}/descripcion-ia`, tal como ya lo fija `apis.md`.
- Verbo HTTP (GET/POST/PATCH/DELETE) comunica la acción; el nombre del recurso nunca incluye un verbo.
- Claves del cuerpo JSON (request y response): español, `snake_case` — `estado`, `mensaje`, `pagina_actual`, como ya lo fija `apis.md`. Códigos de error: español, `SCREAMING_SNAKE_CASE` — `STOCK_INSUFICIENTE`, `TENANT_SUSPENDIDO`.
- Detalle completo en `docs/standards/api.md`.

### Componentes y hooks de React/Next.js (`apps/web`, `apps/admin`, `packages/ui`)

- Nombre de componente: inglés técnico, `PascalCase`, describe el elemento de UI, no el texto que muestra: `ProductCard`, `OrderStatusBadge`, `ShippingQuoteForm`.
- Hooks: inglés, `camelCase`, prefijo `use`: `useCart`, `useTenant`.
- Props: inglés, `camelCase`.
- El **texto visible** dentro del componente (labels, mensajes, placeholders) va en español, porque es contenido de negocio/UI, no código — igual que el resto de la interfaz de la tienda y el panel.

### Ramas de git

- Ver convención completa en `docs/standards/branches.md`. Resumen: prefijo técnico en inglés (`feature/`, `fix/`, `chore/`, `docs/`, `refactor/`) + descripción corta en `kebab-case`. La descripción puede usar palabras en español si describe un concepto de negocio (`feature/comparador-transportadoras`) o en inglés si es puramente técnico (`chore/upgrade-laravel-11`).

### Variables de entorno

- Inglés, `SCREAMING_SNAKE_CASE`, sin excepción: `DB_HOST`, `REDIS_URL`, `APP_ENV`, `SANCTUM_STATEFUL_DOMAINS`. Es la convención universal de Laravel, Next.js y de cualquier herramienta que lea un `.env`; desviarse aquí rompe compatibilidad con el ecosistema, no aporta claridad de negocio.

---

## Ejemplos

| Elemento | Nombre correcto | Nombre incorrecto | Por qué |
|---|---|---|---|
| Módulo | `Envios` | `Shipping` | Módulo de dominio ya fijado en español |
| Clase de servicio | `EnvioService` | `ShippingService` / `Envio_Service` | Sustantivo de negocio en español + sufijo técnico inglés, `PascalCase` |
| Interfaz de integración | `TransportadoraInterface` | `CarrierInterface` | Vocabulario de negocio ya usado en `principios-de-arquitectura.md` |
| Tabla | `envios` | `Envios` / `shipments` | `snake_case`, plural, deriva del modelo `Envio` |
| Columna de sistema | `tenant_id` | `id_tenant` / `tenant_id_empresa` | Convención Eloquent, ya fijada en `vision-tecnica.md` |
| Ruta | `/api/v1/envios/{id}/rastreo` | `/api/v1/get-tracking` | Recurso en español, sin verbo en la ruta |
| Componente | `ShippingQuoteForm` | `FormularioCotizarEnvio` | Componente técnico en inglés; el texto que renderiza sí va en español |
| Rama | `feature/comparador-transportadoras` | `feature/CompararTransportadoras` | Prefijo inglés, descripción en `kebab-case` |
| Variable de entorno | `REDIS_URL` | `redis_url` / `UrlRedis` | `SCREAMING_SNAKE_CASE` en inglés, sin excepción |

---

## Casos límite

- **Un concepto de negocio no tiene traducción natural al español sin sonar forzado** (ej. "dashboard", "checkout"): se usa el término ya adoptado por el equipo en `docs/business/diccionario-del-negocio.md` tal cual, sin forzar una traducción artificial.
- **Un módulo nuevo no encaja claramente en el vocabulario de negocio existente:** se discute el nombre en la documentación del módulo (`templates/nuevo-modulo.md`) antes de escribir código, no se decide ad hoc dentro de una clase.
- **Una librería de terceros impone su propia convención de nombres** (ej. un paquete de Composer que espera un método `handle()` o un nombre de clase específico): la convención de la librería gana sobre esta guía; se documenta la excepción en un comentario breve en el código.

---

## Decisiones futuras

- Si el proyecto adopta un linter de nombres (ej. regla de PHP-CS-Fixer/ESLint personalizada) que verifique estas convenciones automáticamente, una vez el número de módulos lo justifique.
- Si `IA` como nombre de módulo debe ampliarse (ej. `InteligenciaArtificial`) cuando el módulo crezca en submódulos.

---

## Referencias

- `docs/architecture/principios-de-arquitectura.md` — origen de los nombres de módulo y de los ejemplos de clases/métodos en español.
- `docs/architecture/vision-tecnica.md` — origen de `tenant_id` como columna estándar en inglés.
- `docs/architecture/apis.md` — origen de las convenciones de rutas y forma de respuesta JSON.
- `docs/standards/api.md`, `docs/standards/database.md`, `docs/standards/branches.md` — desarrollo detallado de cada categoría de nombre.
- `docs/business/diccionario-del-negocio.md` — vocabulario de negocio de referencia.

---

## Historial

- **2026-07-27** — Primera versión.
