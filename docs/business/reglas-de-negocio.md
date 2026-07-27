# Reglas de Negocio

## Propósito

Fijar reglas de negocio explícitas y accionables que hoy solo existen como supuestos implícitos o quedan sin resolver en `vision-producto.md` (ej. la regla de desempate para sobreventa multicanal, marcada ahí como "Decisión futura"). Este documento convierte esos vacíos en reglas concretas o, cuando aún no hay información suficiente, en una regla provisional explícita y su condición de revisión.

---

## Objetivo

Que ninguna regla de negocio crítica dependa de la interpretación de quien esté implementando una funcionalidad en un momento dado; que esté escrita una sola vez, en un solo lugar, con su lógica y su motivo.

## Alcance

**Incluye:** reglas operativas concretas sobre conflictos multicanal, cálculo de costos de envío, manejo de fallas de canales externos, y políticas mínimas de devoluciones/cancelaciones.

**No incluye:** la descripción de los módulos que aplican estas reglas (→ `modulos.md`), los casos de uso donde se activan (→ `casos-de-uso.md`), ni su implementación técnica (→ `docs/architecture/`).

---

## Problema que resuelve

`vision-producto.md` ya identifica varios de estos vacíos como "casos límite" o "decisiones futuras" sin resolver (ej. "regla de desempate para sobreventa multicanal — aún sin definir"). Sin una regla explícita, cada implementación futura tendría que inventar su propia respuesta, con el riesgo de que el comportamiento del sistema sea inconsistente entre módulos o, peor, entre distintos momentos del desarrollo.

---

## Principios

- **Toda regla de negocio tiene un motivo escrito**, no solo un "qué hacer" — para que se pueda revisar cuando cambien las condiciones que la originaron.
- **Ante la duda entre proteger al cliente o proteger la operación del emprendedor, se protege al cliente primero** en el momento de la venta (ej. nunca se le cobra por algo que no se le puede entregar), y se resuelve el costo operativo internamente.
- **Toda regla es válida por defecto para todos los tenants**, salvo que se documente explícitamente como configurable por tenant (relevante de cara al modelo SaaS).
- **Una regla sin datos suficientes para decidirse bien se marca como "regla provisional"**, con una lógica simple y segura, en vez de dejarse sin definir.

---

## Reglas

### 1. Sobreventa multicanal — regla de desempate

Cuando dos pedidos de canales distintos (o del mismo canal) reclaman la última unidad disponible de un SKU antes de que la sincronización de inventario se propague entre canales:

- **Gana el pedido cuyo pago se confirmó primero** (timestamp de confirmación de pago, no de creación del carrito o de la orden en el marketplace).
- El pedido que pierde el desempate **no se cancela automáticamente sin aviso**: se marca como "pendiente de confirmación de stock" y el sistema notifica de inmediato al emprendedor, quien decide entre (a) ofrecer al cliente perdedor un reemplazo o reembolso, o (b) conseguir la unidad faltante (ej. contactar al proveedor) si el plazo de entrega lo permite.
- Todo canal debe reportar el pedido perdedor como cancelado o modificado en cuanto se tome la decisión, para que no quede una discrepancia de inventario entre el canal y el sistema central.

### 2. Cálculo del costo de envío mostrado al comprador

- El costo de envío mostrado al cliente en el checkout es el **costo real cotizado por la transportadora elegida** para esa dirección y ese peso/volumen de pedido, sin margen oculto adicional por defecto.
- Si el emprendedor configura un margen o una política de envío gratis (ej. "envío gratis desde $X"), esa política se aplica de forma visible antes del pago, nunca se descubre después de que el cliente ya pagó.
- Si ninguna transportadora tiene cobertura para la dirección del cliente, el checkout no debe permitir continuar sin informar claramente que no hay envío disponible para esa zona — nunca se acepta un pedido que no se puede despachar.

### 3. Falla temporal de un canal externo

- Si un canal externo (TikTok Shop, Mercado Libre, una transportadora) deja de responder o cambia su comportamiento, el sistema debe **aislar la falla a ese canal**: los demás canales siguen operando con normalidad.
- El canal afectado se marca como "desconectado" o "con incidencia" visible para el emprendedor, con la hora del último dato sincronizado correctamente.
- Ningún pedido ya confirmado se pierde por una caída del canal: el sistema reintenta la sincronización cuando el canal se recupera, y el pedido permanece en el estado en que estaba antes de la falla.
- El sistema nunca inventa o asume datos de un canal caído (ej. no asume que un pedido se entregó solo porque dejó de recibir actualizaciones).

### 4. Política mínima de devoluciones y cancelaciones

*(Regla provisional — base mínima válida mientras no exista una política específica por categoría de producto o por tenant.)*

- Un pedido se puede **cancelar sin costo para el cliente** mientras no haya sido despachado por la transportadora.
- Una vez despachado, la cancelación se convierte en **solicitud de devolución**, sujeta a que el producto no esté en una categoría explícitamente marcada como no retornable (ej. perecederos, personalizados).
- El plazo mínimo por defecto para solicitar una devolución después de la entrega es de **5 días hábiles**, salvo que la ley local aplicable exija un plazo mayor — en cuyo caso prevalece la ley.
- El costo de envío de una devolución por **falla o error del tenant** (producto incorrecto, defectuoso) lo asume el tenant; el costo de una devolución por **cambio de decisión del cliente** se define por política de cada tenant, mostrada antes de la compra.

---

## Ejemplos

- Dos clientes compran la última unidad de *Camiseta-Azul-M*: uno en Mercado Libre a las 10:03:12 y otro en la tienda propia a las 10:03:15 (ambos timestamps de confirmación de pago). Gana el de Mercado Libre; el sistema marca el pedido de la tienda propia como "pendiente de confirmación de stock" y notifica al emprendedor de inmediato.
- Un cliente en una zona rural revisa el checkout y ninguna transportadora tiene cobertura: el sistema bloquea el paso de pago y muestra "Envío no disponible para tu zona" en vez de dejarlo pagar y fallar después.
- Mercado Libre cae por dos horas: los pedidos de tienda propia y TikTok Shop se siguen procesando con normalidad; al recuperarse Mercado Libre, el sistema sincroniza los pedidos y el inventario acumulados durante la caída.

---

## Casos límite

- **El pedido perdedor del desempate por sobreventa (Regla 1) ya fue pagado por el cliente:** el reembolso debe iniciarse automáticamente si el emprendedor decide no reemplazar la unidad, sin requerir que el cliente lo reclame primero.
- **Dos pedidos con timestamp de pago idéntico al segundo (Regla 1):** se usa el timestamp con mayor precisión disponible (milisegundos) como desempate final; si aun así son iguales, se prioriza el canal con menor costo operativo de reversión para el emprendedor (ej. es más simple cancelar en la tienda propia que en un marketplace con penalización por cancelación).
- **Producto retornable por política pero dañado por mal manejo del cliente (Regla 4):** queda fuera de la política mínima por defecto; se resuelve caso a caso por el emprendedor hasta que exista una regla específica documentada.
- **Un tenant quiere una política de devoluciones distinta a la de otros tenants (Regla 4, cara al SaaS):** la regla mínima aquí es el piso por defecto; permitir configuración por tenant es una decisión futura, no un compromiso actual.

---

## Decisiones futuras

- Definir si la Regla 1 (desempate por sobreventa) debe considerar también la rentabilidad del pedido (ej. priorizar el de mayor margen) en vez de solo el orden de pago, una vez haya datos suficientes del piloto para evaluarlo.
- Definir reglas de negocio específicas por categoría de producto para devoluciones (Regla 4), cuando el catálogo del piloto lo requiera.
- Definir si el margen de envío (Regla 2) puede variar por canal (ej. absorber más costo en marketplaces por su comisión) como estrategia de precios.
- Evaluar si estas reglas necesitan variar por país, una vez la plataforma opere fuera de Colombia.

---

## Referencias

- [`docs/business/vision-producto.md`](vision-producto.md) — casos límite y decisiones futuras que originan estas reglas.
- [`docs/business/casos-de-uso.md`](casos-de-uso.md) — casos de uso donde estas reglas se activan.
- [`docs/business/diccionario-del-negocio.md`](diccionario-del-negocio.md) — terminología usada (pedido, canal, tenant, cliente).
- `docs/architecture/vision-tecnica.md` — manejo de fallas de servicios externos a nivel técnico (circuit breaker/reintentos).

---

## Historial

- **2026-07-27** — Primera versión. Resuelve explícitamente los vacíos marcados como "decisión futura" en `vision-producto.md` respecto a sobreventa multicanal, con una regla provisional donde aún no hay datos suficientes del piloto.
