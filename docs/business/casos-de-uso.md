# Casos de Uso

## Propósito

Describir, agrupados por módulo del ecosistema, los casos de uso principales de Proyecto Alfa: quién los inicia, qué ocurre y qué resultado deja en el sistema. Usa la terminología de [`diccionario-del-negocio.md`](diccionario-del-negocio.md) y los actores de [`actores.md`](actores.md) sin desviarse de ellos.

---

## Objetivo

Servir de puente entre la visión de producto (el "por qué") y el detalle funcional/técnico (el "cómo" en `docs/architecture/arquitectura-funcional.md`), documentando el "qué pasa" de cada flujo relevante en lenguaje de negocio, verificable por cualquier persona sin conocimiento técnico.

## Alcance

**Incluye:** casos de uso de negocio de los módulos ya definidos en `vision-producto.md`: tienda virtual, panel administrativo, comparador de transportadoras, gestión logística, directorio de proveedores, publicidad digital, inteligencia comercial, integración multicanal, automatización con IA, y operación multi-tenant (SaaS).

**No incluye:** diagramas de secuencia, contratos de API o modelo de datos — eso vive en `docs/architecture/arquitectura-funcional.md` y `docs/architecture/apis.md`. Tampoco incluye historias de usuario con criterios de aceptación de sprint — eso vive en `product-backlog.md`.

---

## Problema que resuelve

La visión de producto describe objetivos e intenciones; sin casos de uso concretos, cada quien imagina un flujo distinto para "el comparador de transportadoras" o "la sincronización multicanal". Este documento fija, con ejemplos reales de comercio electrónico, cómo se ve cada flujo en la práctica, para que el diseño técnico tenga algo preciso que implementar y el negocio tenga algo preciso que validar.

---

## Principios

- **Cada caso de uso nombra sus actores explícitamente**, usando los definidos en `actores.md`.
- **Cada caso de uso describe un resultado verificable**, no solo una intención ("el inventario queda sincronizado en los dos canales", no "se mejora la gestión de inventario").
- **Los casos de uso multicanal siempre consideran más de un canal activo**, incluso en el piloto, porque el modelo se diseña multicanal desde el día uno.
- **Ningún caso de uso asume que el sistema tiene un solo tenant**, aunque hoy solo exista uno operando.

---

## Reglas

- Todo módulo listado en `modulos.md` debe tener al menos un caso de uso aquí.
- Un caso de uso nuevo se agrega con el formato: **Actor(es) → Disparador → Flujo → Resultado**.
- Si un caso de uso introduce una regla de negocio específica (ej. cómo se calcula un costo o se resuelve un conflicto), esa regla se documenta en `reglas-de-negocio.md` y aquí solo se referencia.

---

## Ejemplos

A continuación, los casos de uso principales agrupados por módulo.

### Tienda virtual

- **CU-01 — Cliente compra en la tienda propia.** *Actor:* cliente. *Disparador:* el cliente agrega un SKU al carrito y paga. *Flujo:* el sistema valida stock disponible, procesa el pago, crea el pedido y descuenta inventario. *Resultado:* pedido visible en el panel del emprendedor, inventario del SKU actualizado en todos los canales.

### Panel administrativo

- **CU-02 — Emprendedor gestiona el catálogo.** *Actor:* emprendedor. *Disparador:* necesita publicar un producto nuevo. *Flujo:* crea el producto, define SKUs (variantes), precio y stock inicial. *Resultado:* el producto queda disponible para publicarse en cualquier canal conectado.
- **CU-03 — Emprendedor revisa pedidos de todos los canales en un solo lugar.** *Actor:* emprendedor. *Disparador:* entra al panel a iniciar su jornada. *Flujo:* el panel muestra pedidos de tienda propia, TikTok Shop y Mercado Libre en una sola bandeja, con su canal de origen visible. *Resultado:* el emprendedor no necesita entrar a cada plataforma externa para saber qué vendió.

### Comparador de transportadoras

- **CU-04 — El emprendedor compara transportadoras para un pedido.** *Actor:* emprendedor, sistema. *Disparador:* un pedido queda listo para despacho. *Flujo:* el sistema consulta costo, tiempo estimado y cobertura de varias transportadoras para la dirección del cliente; el emprendedor elige una o el sistema sugiere la de mejor relación costo/tiempo. *Resultado:* se genera la guía de envío con la transportadora elegida. *Regla asociada:* ver `reglas-de-negocio.md` → cálculo de costo de envío mostrado al comprador.

### Gestión logística

- **CU-05 — Seguimiento de un pedido en tránsito.** *Actor:* sistema, cliente, emprendedor. *Disparador:* la transportadora reporta un cambio de estado (recogido, en tránsito, entregado, novedad). *Flujo:* el sistema actualiza el estado del pedido y notifica al cliente; si hay novedad (ej. dirección errónea), alerta al emprendedor. *Resultado:* cliente y emprendedor siempre saben el estado real del pedido sin llamar a la transportadora.

### Directorio de proveedores

- **CU-06 — Emprendedor busca proveedor para un producto de alta rotación.** *Actor:* emprendedor, sistema. *Disparador:* el sistema detecta baja disponibilidad de un SKU de alta rotación. *Flujo:* el emprendedor consulta el directorio de proveedores filtrando por categoría de producto y ve condiciones y confiabilidad de cada uno. *Resultado:* el emprendedor contacta a un proveedor y gestiona el reabastecimiento (hoy, fuera del sistema).

### Publicidad digital

- **CU-07 — Emprendedor lanza una campaña y mide su resultado.** *Actor:* emprendedor. *Disparador:* quiere promocionar un producto o colección. *Flujo:* crea una campaña en Meta Ads o Google Ads desde el módulo de publicidad, definiendo presupuesto y objetivo; el sistema trae las métricas de la campaña al dashboard de inteligencia comercial. *Resultado:* el emprendedor ve, junto a sus ventas, cuánto le costó cada pedido atribuible a esa campaña.

### Dashboard de inteligencia comercial

- **CU-08 — El sistema sugiere reabastecer un producto de alta rotación.** *Actor:* sistema, emprendedor. *Disparador:* análisis periódico de ventas detecta que un SKU se agotará pronto según su velocidad de venta. *Flujo:* el sistema genera una alerta/sugerencia de reabastecimiento en el dashboard, con la cantidad estimada recomendada. *Resultado:* el emprendedor decide reabastecer (posiblemente iniciando CU-06) o descartar la sugerencia.

### Integración multicanal

- **CU-09 — Cliente compra en TikTok Shop y el inventario se descuenta también en la tienda propia.** *Actor:* cliente, sistema. *Disparador:* una venta se confirma en TikTok Shop. *Flujo:* el canal notifica el pedido vía integración; el sistema lo registra como pedido interno y descuenta el SKU vendido del stock compartido del tenant. *Resultado:* la tienda propia y Mercado Libre reflejan el nuevo stock disponible sin intervención manual del emprendedor. *Caso límite asociado:* ver "Casos límite" abajo — venta simultánea del último ítem en dos canales.

### Automatización con IA

- **CU-10 — El sistema genera la ficha de un producto nuevo.** *Actor:* emprendedor, sistema/IA. *Disparador:* el emprendedor sube fotos y datos básicos de un producto nuevo. *Flujo:* la IA genera título, descripción y atributos sugeridos; el emprendedor revisa y aprueba o edita antes de publicar. *Resultado:* ficha de producto lista para publicarse en todos los canales conectados, con menos tiempo invertido por el emprendedor.

### Operación multi-tenant (SaaS — Fase 5)

- **CU-11 — Un emprendedor nuevo crea su tenant en la plataforma.** *Actor:* emprendedor, administrador de la plataforma. *Disparador:* un emprendedor se registra en Proyecto Alfa. *Flujo:* el sistema crea un tenant nuevo, aislado de los demás por `tenant_id`, y guía al emprendedor a configurar su primer canal. *Resultado:* el emprendedor opera su propio negocio en la plataforma sin ver ni afectar datos de otros tenants.

---

## Casos límite

- **Venta simultánea del último ítem de stock en dos canales (CU-09):** puede ocurrir que un cliente compre en TikTok Shop y otro en la tienda propia el mismo SKU con solo una unidad disponible, en una ventana de segundos antes de que la sincronización se propague. La regla de desempate está definida en `reglas-de-negocio.md`.
- **Un canal externo cae o cambia su API (CU-09, CU-03):** el sistema debe seguir mostrando y vendiendo en los demás canales; el canal afectado se marca como "desconectado" en el panel, sin bloquear el resto de la operación.
- **El emprendedor rechaza la sugerencia de reabastecimiento (CU-08) repetidamente para el mismo SKU:** el sistema no debe seguir generando la misma alerta de forma idéntica sin aprendizaje; se documenta como mejora futura del módulo de IA, no como comportamiento actual comprometido.
- **La IA genera contenido incorrecto o inapropiado para un producto (CU-10):** el emprendedor siempre revisa antes de publicar; el sistema nunca publica contenido generado por IA sin aprobación humana en el estado actual del producto.

---

## Decisiones futuras

- Definir si el cliente final tendrá cuenta propia (portal de clientes) que le permita ver historial de compras entre canales, hoy aspiracional según `vision-producto.md`.
- Definir el flujo exacto de onboarding de un tenant nuevo (CU-11) cuando se diseñe la Fase 5 en detalle.
- Definir si la sugerencia de reabastecimiento (CU-08) evoluciona a generar automáticamente una orden de compra al proveedor, o si siempre requiere iniciativa del emprendedor.

---

## Referencias

- [`docs/business/diccionario-del-negocio.md`](diccionario-del-negocio.md) — terminología usada en cada caso de uso.
- [`docs/business/actores.md`](actores.md) — actores referenciados.
- [`docs/business/modulos.md`](modulos.md) — módulos a los que pertenece cada caso de uso.
- [`docs/business/reglas-de-negocio.md`](reglas-de-negocio.md) — reglas de negocio detalladas que algunos casos de uso referencian.
- `docs/architecture/arquitectura-funcional.md` (en construcción) — traducción técnica de estos casos de uso.

---

## Historial

- **2026-07-27** — Primera versión.
