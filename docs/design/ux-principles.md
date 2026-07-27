# Principios de UX

## Propósito

Este documento fija los principios de experiencia de usuario que gobiernan toda decisión de diseño en Proyecto Alfa, en ambas superficies (`apps/web` y `apps/admin`). Es la base de la que se derivan `branding.md`, `design-system.md` y `ui-guidelines.md`: ningún token, componente o guía de interfaz debería contradecir lo que aquí se establece. Si una decisión de diseño no puede justificarse con un principio de este documento, el principio falta o la decisión está mal — se discute, no se aplica en silencio.

---

## Objetivo

Dar a cualquier persona que diseñe o construya una pantalla — hoy o dentro de tres años, en el piloto o en un tenant del SaaS — un criterio explícito para resolver preguntas de UX sin tener que reinventarlo cada vez: ¿esto ayuda a vender más rápido en la tienda? ¿esto ayuda a operar más rápido en el panel? ¿esto lo puede usar alguien sin conocimientos técnicos?

---

## Alcance

**Incluye:**

- Principios que gobiernan `apps/web` (tienda pública orientada a conversión) y `apps/admin` (panel operativo denso en datos), y cómo difieren entre sí.
- Principios transversales a ambas: accesibilidad, rendimiento, uso por personas no técnicas, idioma y mercado objetivo (Colombia/Latinoamérica hispanohablante).
- Cómo se resuelve un conflicto entre principios de una superficie y la otra cuando comparten `packages/ui`.

**No incluye** (vive en otros documentos):

- Identidad de marca, tono y voz → `docs/design/branding.md`.
- Tokens concretos, arquitectura de theming y estructura de `packages/ui` → `docs/design/design-system.md`.
- Estados de componente, accesibilidad técnica (contraste, foco, teclado) y reglas responsive → `docs/design/ui-guidelines.md`.
- Flujos de negocio específicos (checkout, gestión de inventario) → documentos de `docs/business/`.

---

## Problema que resuelve

Sin principios explícitos y compartidos, un proyecto con dos superficies tan distintas como `apps/web` y `apps/admin` termina en uno de dos extremos, ambos costosos:

- **Un solo estándar visual "de tienda" impuesto al admin:** animaciones, espaciado generoso y jerarquía visual pensada para convencer a un visitante terminan sobrando y estorbando a alguien que revisa 200 pedidos por día y solo quiere que la tabla cargue rápido y sea escaneable.
- **Un solo estándar "de panel" impuesto a la tienda:** una interfaz densa, funcional y sin cuidado de percepción de marca no genera la confianza que un comprador de Colombia/Latinoamérica necesita para dar su tarjeta a una tienda que no conoce.

Este documento evita ambos extremos fijando qué es negociable (estética, densidad, tono) y qué no lo es (accesibilidad, rendimiento, usabilidad para no técnicos) en cada superficie.

---

## Principios

1. **La tienda vende; el panel opera.** Cada superficie se diseña para su propia métrica de éxito: `apps/web` se mide en tasa de conversión de visitante a comprador; `apps/admin` se mide en tiempo para completar una tarea repetitiva (procesar un pedido, actualizar stock, revisar el dashboard del día). Ninguna decisión de diseño es válida "porque se ve bien" si no sirve a esa métrica.
2. **Velocidad de carga es una característica de UX, no un detalle técnico.** En `apps/web`, cada segundo adicional de carga es conversión perdida y posición perdida en buscadores. Ante la duda entre una animación vistosa y una carga instantánea, gana la carga instantánea. El admin no compite por SEO ni por primera impresión, pero la lentitud percibida en tareas repetidas frustra al mismo nivel: una tabla de pedidos que tarda en responder cuesta minutos acumulados cada día.
3. **Cero fricción para quien no es técnico.** El usuario objetivo — hoy el equipo del negocio piloto, mañana un emprendedor del SaaS — no tiene formación técnica. Todo flujo (publicar un producto, marcar un pedido como despachado, configurar un canal) debe poder completarse sin explicación previa ni soporte humano. Si un flujo necesita un tutorial para usarse la primera vez, el flujo está mal diseñado, no falta el tutorial.
4. **Confianza es una funcionalidad, no un adorno.** En el mercado objetivo (Colombia/Latinoamérica), la desconfianza hacia comprar en una tienda en línea desconocida es una barrera real de conversión. `apps/web` debe hacer visibles, en cada pantalla relevante, las señales que reducen esa fricción: precio total sin sorpresas, tiempo y costo de envío claros antes del pago, medios de pago reconocidos localmente, política de devolución accesible, evidencia social (reseñas, unidades vendidas) cuando exista.
5. **Consistencia de sistema, no uniformidad de superficie.** `apps/web` y `apps/admin` comparten `packages/ui` — mismos tokens, mismo lenguaje de interacción, mismo nivel de calidad — pero eso no obliga a que luzcan igual. Un componente puede tener una variante "tienda" (más aire, más énfasis visual) y una variante "admin" (más densa, más neutra) sin dejar de ser el mismo componente subyacente.
6. **Minimizar clics y recorrido en tareas repetitivas del admin.** Quien usa `apps/admin` lo hace muchas veces al día, no una vez. Cada paso adicional en un flujo frecuente (buscar un pedido, cambiar un estado, ajustar stock) tiene un costo que se multiplica por el número de veces que se repite al día. El admin prioriza atajos de teclado, acciones en lote, valores por defecto inteligentes y minimizar navegación entre pantallas por encima de la estética.
7. **Accesibilidad y rendimiento son requisitos, no mejoras posteriores.** Ambos se diseñan desde el primer componente, no se "arreglan" después de construir. Ver el detalle técnico en `ui-guidelines.md`.
8. **Diseñar para un mercado hispanohablante desde el copy hasta el layout.** Textos, mensajes de error, formatos de fecha/moneda (COP como referencia, con soporte para otras monedas latinoamericanas a futuro) y longitud de palabras en español (más larga que en inglés) se consideran desde el diseño de cada componente, no se ajustan después de traducir.
9. **Ninguna decisión de UX asume una sola marca para siempre.** El piloto tiene una identidad visual concreta hoy, pero ningún principio ni patrón de interacción debe depender de esa identidad específica de forma que un futuro tenant del SaaS no pueda aplicar la suya. Ver `branding.md` y `design-system.md` para el mecanismo (theming por tokens).

---

## Reglas

- Ninguna funcionalidad de `apps/web` se lanza sin haber sido evaluada en una conexión móvil típica de Colombia (3G/4G de gama media) — no solo en el entorno de desarrollo del equipo.
- Ningún flujo de `apps/admin` que se ejecute más de una vez por sesión de trabajo se diseña sin considerar atajo de teclado o acción en lote.
- Ningún texto de interfaz (botón, error, confirmación) se escribe asumiendo que quien lo lee sabe qué es una "API", un "SKU" o un "webhook", salvo en pantallas explícitamente técnicas (si existieran para desarrolladores del SaaS).
- Toda decisión de diseño que mejore la estética a costa del rendimiento medible (peso de página, tiempo de interacción) en `apps/web` se documenta y se justifica explícitamente antes de implementarse; no se asume que "se ve mejor" es suficiente.
- Toda pantalla nueva de `apps/web` que involucre pago, envío o datos personales incluye al menos una señal de confianza visible sin necesidad de hacer scroll adicional.

---

## Ejemplos

- Un componente de tarjeta de producto en `apps/web` usa una variante con imagen grande, espaciado generoso y microinteracción de hover suave, porque su trabajo es generar deseo de compra. El mismo componente base, en `apps/admin`, se usa en modo compacto dentro de una tabla de inventario, sin hover decorativo, porque su trabajo es dejar escanear 50 filas rápido.
- El flujo de "publicar producto nuevo" en `apps/admin` permite duplicar un producto existente y editar solo lo que cambia, en vez de obligar a llenar el formulario completo de nuevo — ahorra clics en una tarea que se repite a diario.
- El checkout de `apps/web` muestra el costo de envío estimado antes de pedir datos de pago, no como sorpresa en el último paso — principio de confianza aplicado a un punto de fricción conocido en e-commerce.
- Un mensaje de error de `apps/admin` dice "No se pudo guardar el producto: el precio debe ser mayor a 0" en vez de "Error de validación 422" — cero fricción para no técnicos, incluso en un panel operativo.

---

## Casos límite

- **Dueño del negocio revisando pedidos desde el celular.** `apps/admin` no es mobile-first (se diseña asumiendo uso principal en escritorio, sesiones largas, múltiples paneles de datos), pero las tareas críticas y urgentes (ver detalle de un pedido, marcar como despachado, consultar el dashboard del día) deben seguir siendo usables en una pantalla de teléfono, aunque la densidad de información se reduzca.
- **Conflicto entre velocidad y riqueza visual en la tienda.** Cuando una funcionalidad de `apps/web` (por ejemplo, un configurador visual de producto) no puede ser a la vez rica visualmente y liviana, se prioriza la versión liviana para el 80% del tráfico (mayoría en móvil, muchos en conexión media) y se evalúa una versión enriquecida solo si no compromete el rendimiento base.
- **Usuario con lector de pantalla comprando en la tienda.** El principio de "cero fricción para no técnicos" incluye explícitamente a usuarios con discapacidad: el flujo de compra completo (buscar, agregar al carrito, pagar) debe ser operable sin mouse y sin depender de percepción visual del color o la posición.
- **Tenant del SaaS con catálogo o volumen de datos muy distinto al piloto** (por ejemplo, un tenant con 5 productos frente a uno con 5,000): los principios de densidad y minimización de clics del admin deben seguir funcionando en ambos extremos, no solo en el volumen actual del piloto.

---

## Decisiones futuras

- Umbral concreto de rendimiento (ej. Core Web Vitals objetivo por página) que convierte el principio 2 en una métrica verificable en CI — hoy es un principio cualitativo, falta el número acordado con negocio.
- Alcance real de uso móvil de `apps/admin`: si con el tiempo el volumen de "gestión desde el celular" crece lo suficiente, se evaluará una versión con mayor inversión mobile, no solo "usable de emergencia".
- Soporte multi-idioma más allá de español (si el SaaS se expande fuera de Latinoamérica hispanohablante) — hoy el diseño asume español como único idioma de interfaz.
- Soporte multi-moneda más allá de COP en flujos de precio/checkout — depende de la expansión geográfica del SaaS, aún no comprometida en el roadmap.

---

## Referencias

- [`README.md`](../../README.md) — visión global del proyecto.
- [`docs/business/vision-producto.md`](../business/vision-producto.md) — objetivos de negocio de los que se derivan estos principios.
- [`docs/architecture/vision-tecnica.md`](../architecture/vision-tecnica.md) — restricciones técnicas (API-first, multi-tenant, stateless) que estos principios deben respetar.
- [`docs/design/branding.md`](branding.md) — identidad de marca derivada de estos principios.
- [`docs/design/design-system.md`](design-system.md) — tokens y `packages/ui` que implementan estos principios.
- [`docs/design/ui-guidelines.md`](ui-guidelines.md) — guías técnicas de interfaz derivadas de estos principios.

---

## Historial

- **2026-07-27** — Primera versión.
