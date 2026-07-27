# Módulos

## Propósito

Describir cada módulo del ecosistema de Proyecto Alfa desde el ángulo de **negocio**: qué hace, para quién es, qué problema resuelve y qué valor entrega. Este documento no entra en implementación técnica — la traducción de cada módulo a arquitectura, endpoints y modelo de datos vive en `docs/architecture/arquitectura-funcional.md`.

---

## Objetivo

Que cualquier decisión de producto o de priorización pueda apoyarse en una descripción de negocio clara de cada módulo, independiente de cómo esté (o vaya a estar) implementado.

## Alcance

**Incluye:** los diez módulos listados en `README.md` y `vision-producto.md`, descritos en términos de negocio (propósito, usuario, problema, valor).

**No incluye:** diagramas de arquitectura, esquema de datos, contratos de API, decisiones de framework o librería — eso vive en `docs/architecture/`. Tampoco incluye el detalle de casos de uso paso a paso (→ `casos-de-uso.md`) ni las reglas de negocio específicas de cada módulo (→ `reglas-de-negocio.md`).

---

## Problema que resuelve

Sin una descripción de negocio por módulo, la tentación natural es empezar a diseñar la base de datos o los endpoints de un módulo antes de tener claro qué problema de negocio resuelve exactamente y para quién. Este documento obliga a responder esas preguntas primero.

---

## Principios

- **Cada módulo tiene un dueño de problema, no solo una lista de funciones.** Se describe primero el problema del emprendedor o del cliente que el módulo resuelve, no la lista de pantallas.
- **Módulos desacoplados también en la documentación de negocio.** Cada módulo se describe de forma que pueda leerse, priorizarse o incluso posponerse de forma independiente, coherente con el principio arquitectónico de módulos desacoplados (`principios-de-arquitectura.md`).
- **El valor se expresa en términos del negocio del emprendedor**, no en términos de la plataforma (ej. "reduce el riesgo de vender algo que no tiene" es mejor que "sincroniza inventario").

---

## Reglas

- Todo módulo de esta lista debe tener al menos un caso de uso correspondiente en `casos-de-uso.md`.
- Si un módulo se divide en submódulos de negocio (ej. "logística" podría dividirse en "despacho" y "seguimiento"), esa división se documenta aquí antes que en la arquitectura técnica.
- Ningún módulo se documenta con funcionalidad que no esté respaldada por un objetivo específico en `vision-producto.md`.

---

## Ejemplos

A continuación, la descripción de negocio de cada módulo.

### 1. Tienda virtual

**Qué hace:** es el canal de venta propio del tenant: catálogo, ficha de producto, carrito y checkout. **Para quién:** el cliente que compra directamente al tenant (sin pasar por un marketplace). **Problema que resuelve:** hoy muchos emprendedores dependen solo de marketplaces, donde compiten por precio y no controlan la experiencia de compra ni la relación con el cliente. **Valor que entrega:** un canal propio, orientado a conversión, donde el emprendedor controla marca, experiencia y márgenes.

### 2. Panel administrativo

**Qué hace:** centraliza la gestión de productos, inventario, pedidos, clientes y ventas de todos los canales en un solo lugar. **Para quién:** el emprendedor. **Problema que resuelve:** operar disperso entre varias plataformas, sin una vista única del negocio. **Valor que entrega:** el emprendedor toma decisiones desde una sola pantalla en vez de alternar entre la tienda propia, TikTok Shop y Mercado Libre.

### 3. Comparador de transportadoras

**Qué hace:** compara costo, tiempo estimado y cobertura entre varias transportadoras para un pedido concreto. **Para quién:** el emprendedor, al momento de despachar. **Problema que resuelve:** elegir transportadora "a ojo" o por costumbre, pagando de más o entregando más lento de lo necesario. **Valor que entrega:** mejores márgenes en envío y mejor experiencia de entrega para el cliente, sin que el emprendedor tenga que investigar tarifas manualmente.

### 4. Gestión logística

**Qué hace:** centraliza el despacho y seguimiento de pedidos desde que se genera la guía hasta que se entrega (o se reporta una novedad). **Para quién:** el emprendedor y, de forma indirecta, el cliente que hace seguimiento a su compra. **Problema que resuelve:** falta de visibilidad sobre dónde está un pedido después de despachado, y tiempo perdido consultando cada transportadora por separado. **Valor que entrega:** trazabilidad de principio a fin y menos tiempo del emprendedor respondiendo "¿dónde está mi pedido?".

### 5. Directorio de proveedores

**Qué hace:** un catálogo de proveedores con su información de contacto, categoría de producto y nivel de confiabilidad. **Para quién:** el emprendedor que necesita abastecerse o diversificar sus fuentes de producto. **Problema que resuelve:** encontrar proveedores confiables hoy depende de contactos personales o búsquedas informales, sin forma de comparar. **Valor que entrega:** reduce el tiempo y el riesgo de encontrar un proveedor nuevo, especialmente para reabastecer productos de alta rotación.

### 6. Publicidad digital

**Qué hace:** permite planear, administrar y medir campañas en Meta Ads y Google Ads (y futuras plataformas) desde la misma plataforma que administra ventas e inventario. **Para quién:** el emprendedor que invierte en publicidad para atraer clientes. **Problema que resuelve:** gestionar campañas en herramientas separadas de las ventas, sin poder cruzar fácilmente cuánto costó cada venta atribuible a publicidad. **Valor que entrega:** visibilidad del retorno real de cada campaña, junto a las ventas que generó.

### 7. Dashboard de inteligencia comercial

**Qué hace:** consolida ventas, comportamiento de clientes y tendencias de productos en reportes y métricas en tiempo real. **Para quién:** el emprendedor que necesita decidir qué vender, reabastecer o descontinuar. **Problema que resuelve:** decisiones tomadas por intuición en vez de datos, por falta de un lugar único donde verlos. **Valor que entrega:** decisiones de inventario, precio y publicidad basadas en el comportamiento real del negocio, no en percepción.

### 8. Integración multicanal

**Qué hace:** conecta tienda propia, TikTok Shop, Mercado Libre y futuros marketplaces, con inventario sincronizado entre todos. **Para quién:** el emprendedor que vende (o quiere vender) en más de un canal. **Problema que resuelve:** sobreventa por no saber cuánto stock queda realmente después de ventas simultáneas en distintos canales. **Valor que entrega:** el emprendedor vende en varios canales sin arriesgarse a vender lo que ya no tiene.

### 9. Automatización con IA

**Qué hace:** automatiza tareas repetitivas — generación de fichas y descripciones de producto, respuestas a preguntas frecuentes, apoyo en reportes — usando inteligencia artificial. **Para quién:** el emprendedor, especialmente uno con equipo pequeño o sin tiempo para tareas de contenido. **Problema que resuelve:** tiempo valioso del emprendedor consumido en tareas que no requieren su criterio único. **Valor que entrega:** más tiempo del emprendedor disponible para decisiones que sí requieren su criterio (precio, proveedor, estrategia).

### 10. Arquitectura SaaS multi-tenant

**Qué hace:** la capacidad de la plataforma de operar para muchos tenants (emprendedores) de forma aislada y simultánea, cada uno con su propio catálogo, inventario, pedidos y canales. **Para quién:** todo emprendedor que, a partir de la Fase 5, quiera usar Proyecto Alfa para su propio negocio, no solo el piloto. **Problema que resuelve:** que la plataforma sirva solo al negocio piloto y no pueda ofrecerse a otros sin reescribirse. **Valor que entrega:** convierte el aprendizaje validado en el piloto en un producto que otros emprendedores pueden usar.

---

## Casos límite

- **Un módulo que depende de otro para tener valor** (ej. "publicidad digital" es más útil si "inteligencia comercial" ya existe para medir el retorno): se documenta la dependencia de valor aquí, aunque la dependencia técnica se resuelva de forma desacoplada en la arquitectura.
- **Un módulo que el piloto no necesita todavía** (ej. "arquitectura SaaS multi-tenant" no cambia la experiencia del negocio piloto hoy): se documenta igual, porque es una decisión de arquitectura de datos que debe existir desde el día uno aunque su valor de negocio se materialice después (Fase 5).
- **Dos módulos que compiten por la misma prioridad de negocio:** la decisión de cuál va primero se resuelve en `roadmap.md` y `product-backlog.md`, no en este documento.

---

## Decisiones futuras

- Evaluar si "gestión logística" se divide formalmente en submódulos de negocio ("despacho" y "postventa/novedades") cuando el volumen de pedidos lo justifique.
- Evaluar si "publicidad digital" se expande a incluir otros canales de pauta más allá de Meta Ads y Google Ads (ej. TikTok Ads), dado que TikTok ya es canal de venta.
- Definir si "directorio de proveedores" evoluciona a permitir pedidos de compra gestionados dentro del sistema (hoy la relación con el proveedor ocurre fuera del sistema).

---

## Referencias

- [`docs/business/vision-producto.md`](vision-producto.md) — objetivos que originan cada módulo.
- [`docs/business/casos-de-uso.md`](casos-de-uso.md) — casos de uso por módulo.
- [`docs/business/documento-maestro.md`](documento-maestro.md) — resumen de todos los módulos en una tabla.
- `docs/architecture/arquitectura-funcional.md` (en construcción) — traducción técnica de cada módulo.

---

## Historial

- **2026-07-27** — Primera versión.
