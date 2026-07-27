# Arquitectura Funcional

## Propósito

Dar el mapa de alto nivel de los módulos funcionales del sistema — Catálogo, Pedidos, Inventario, Envíos, Proveedores, Publicidad, Analítica, Canales, IA — y cómo se relacionan entre sí: quién depende de quién, y qué eventos cruzan de un módulo a otro. Es la vista **técnica** de esa relación; el mismo mapa visto desde ángulo de negocio (qué resuelve cada módulo para el emprendedor, qué valor entrega) vive en el futuro `docs/business/modulos.md`. Este documento no repite ese ángulo — se enfoca en dependencias, eventos y flujo de datos entre módulos.

---

## Objetivo

Que cualquier persona pueda ver, sin leer código, qué módulo depende de cuál, qué eventos existen entre ellos y qué pasa (en términos de sistema) cuando ocurre una acción de negocio — para poder razonar sobre el impacto de un cambio o una caída antes de que ocurra.

---

## Alcance

Cubre: los nueve módulos funcionales, sus responsabilidades a alto nivel, sus dependencias entre sí, y el catálogo de eventos de dominio principales que cruzan de un módulo a otro.

No cubre: el detalle de negocio de cada módulo (qué necesita el emprendedor, `docs/business/modulos.md`), la estructura de carpetas y capas Laravel de cada módulo (`arquitectura-backend.md`), ni el esquema de tablas (`base-de-datos.md`).

---

## Problema que resuelve

En un monolito modular, la disciplina de mantener fronteras claras (ver `principios-de-arquitectura.md`) solo funciona si existe un mapa explícito de cómo se relacionan los módulos — de lo contrario, cada desarrollador infiere las dependencias leyendo código, y es fácil introducir un acoplamiento oculto (ej. `Publicidad` empezando a leer directamente tablas de `Pedidos` para calcular ROI, en vez de pasar por una interfaz). Este documento existe para que ese mapa sea explícito y se pueda auditar.

---

## Principios

- **Las dependencias fluyen en una dirección predecible**, de módulos más "operativos" (Catálogo, Inventario, Pedidos) hacia módulos más "analíticos o de canal" (Analítica, Publicidad, Canales, IA), y no al revés salvo por eventos.
- **Ningún módulo de negocio central depende de un módulo de integración externa.** `Pedidos` no sabe si el pedido vino de TikTok Shop o de la tienda propia; eso lo resuelve `Canales` antes de entregarle el pedido en forma unificada.
- **Los eventos son el mecanismo por defecto de relación entre módulos que no necesitan respuesta inmediata**; las llamadas directas a servicio se reservan para cuando un módulo necesita una respuesta síncrona para decidir algo ahora mismo.
- **Un módulo puede apagarse sin tumbar el resto.** Si `Publicidad` o `IA` fallan o se desactivan, `Catálogo`, `Pedidos` e `Inventario` siguen operando.

---

## Reglas

- Todo nuevo módulo o submódulo se agrega primero a este mapa (dependencias y eventos que produce/consume) antes de implementarse.
- Una dependencia nueva entre dos módulos existentes (ej. `Proveedores` empezando a necesitar algo de `Envios`) se documenta aquí como parte del mismo cambio de código que la introduce.
- Ningún módulo "central" (Catálogo, Pedidos, Inventario) depende de un módulo "periférico" (Publicidad, IA, Analítica) — la dependencia siempre va de periférico a central o vía evento.

---

## Mapa de módulos

| Módulo | Responsabilidad | Depende de (síncrono) | Eventos que emite | Eventos que consume |
|---|---|---|---|---|
| **Catálogo** | Productos, variantes, precios, categorías, contenido de ficha. | — | `ProductoCreado`, `ProductoActualizado`, `PrecioActualizado` | `DescripcionGeneradaIA` |
| **Inventario** | Stock unificado por producto/variante, disponibilidad. | Catálogo (existencia del producto) | `StockActualizado`, `StockBajo` | `PedidoConfirmado`, `PedidoCancelado`, `SincronizacionCanalFallida` |
| **Pedidos** | Ciclo de vida del pedido: creación, confirmación, cancelación, estado de despacho. | Inventario (verificar disponibilidad), Envíos (cotización/guía) | `PedidoCreado`, `PedidoConfirmado`, `PedidoCancelado`, `PedidoDespachado` | `PedidoRecibidoDeCanal`, `GuiaGenerada` |
| **Envíos** | Comparador de transportadoras, generación de guías, seguimiento. | — | `GuiaGenerada`, `EnvioActualizado` | `PedidoConfirmado` |
| **Proveedores** | Directorio de proveedores confiables, abastecimiento. | Catálogo (qué productos abastece cada proveedor) | `ProveedorRegistrado` | `StockBajo` |
| **Canales** | Integración multicanal (tienda propia, TikTok Shop, Mercado Libre): recibe pedidos externos, propaga stock/catálogo. | Catálogo, Inventario | `PedidoRecibidoDeCanal`, `SincronizacionCanalFallida` | `StockActualizado`, `ProductoActualizado`, `PedidoConfirmado` |
| **Publicidad** | Gestión y medición de campañas (Meta Ads, Google Ads, futuras integraciones). | Catálogo (qué se anuncia), Analítica (rendimiento) | `CampanaCreada`, `CampanaActualizada` | `PedidoConfirmado` (atribución) |
| **Analítica** | Dashboards e inteligencia comercial: ventas, clientes, tendencias, rotación. | — (lee vía proyecciones propias, no acceso directo) | `ReporteGenerado` | `PedidoConfirmado`, `PedidoCancelado`, `StockActualizado`, `CampanaActualizada` |
| **IA** | Automatización: generación de descripciones, respuestas frecuentes, asistencia en reportes. | Catálogo (datos base del producto a describir) | `DescripcionGeneradaIA`, `RespuestaSugerida` | `ProductoCreado` |

---

## Ejemplos

- **Compra confirmada en la tienda propia:** `Pedidos` confirma el pedido tras verificar disponibilidad con `Inventario` → emite `PedidoConfirmado` → `Inventario` lo consume y descuenta stock → `Canales` lo consume y propaga el nuevo stock a TikTok Shop y Mercado Libre → `Analítica` lo consume para actualizar sus proyecciones de venta → `Publicidad` lo consume para atribuir la venta a una campaña si aplica.
- **Pedido recibido desde Mercado Libre:** `Canales` traduce el formato propio del marketplace a la forma interna del sistema y emite `PedidoRecibidoDeCanal` → `Pedidos` lo consume y lo trata igual que un pedido nacido en la tienda propia, sin lógica especial por canal de origen.
- **Producto nuevo creado sin descripción:** `Catálogo` emite `ProductoCreado` → `IA` lo consume, genera una descripción y emite `DescripcionGeneradaIA` → `Catálogo` la consume y la asocia al producto, lista para publicar.
- **Stock bajo detectado:** `Inventario` emite `StockBajo` → `Proveedores` lo consume para sugerir un reabastecimiento desde el directorio de proveedores confiables del producto.

---

## Casos límite

- **Un módulo periférico cae o se desactiva** (ej. `Publicidad` fuera de servicio): los eventos que consumía (`PedidoConfirmado`) se acumulan en su cola y se procesan al volver, sin bloquear a `Pedidos`, `Inventario` ni `Canales`.
- **Dos módulos "centrales" parecen necesitar depender uno del otro** (ej. `Pedidos` necesita `Envios` para cotizar, y `Envios` necesita saber el destino del `Pedido`): la dependencia síncrona va en una sola dirección (`Pedidos` llama a `Envíos` pasándole los datos necesarios como parámetros); `Envíos` nunca consulta a `Pedidos` para obtener esos datos por su cuenta.
- **Evento con múltiples consumidores, uno de los cuales falla** (ej. `PedidoConfirmado` con `Canales` fallando al propagar a un marketplace caído): el fallo de un listener no afecta a los demás — cada listener se ejecuta y reintenta de forma aislada (ver `arquitectura-backend.md`, Jobs).

---

## Decisiones futuras

- Catálogo formal y versionado de eventos de dominio (hoy documentado aquí de forma descriptiva) cuando el número de integraciones cruzadas lo justifique — ver `principios-de-arquitectura.md`, decisiones futuras.
- Si `Proveedores` evoluciona a depender también de `Envios` cuando se modele el flujo de compra a proveedor con transporte propio.
- Alcance exacto de `IA` como módulo único vs. submódulos por capacidad (generación de contenido, respuestas, sugerencias de inventario) a medida que crezca.

---

## Referencias

- `docs/business/modulos.md` — el mismo mapa desde ángulo de negocio (en construcción).
- `docs/architecture/arquitectura-backend.md` — cómo se implementa cada módulo y la comunicación por eventos en Laravel.
- `docs/architecture/principios-de-arquitectura.md` — reglas de fronteras entre módulos que este mapa respeta.
- `docs/business/vision-producto.md` — objetivos de negocio que cada módulo sirve.

---

## Historial

- **2026-07-27** — Primera versión.
