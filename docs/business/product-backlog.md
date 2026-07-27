# Product Backlog

## Propósito

Traducir el objetivo general y los módulos del ecosistema en un backlog inicial de alto nivel, organizado por las cinco fases del roadmap, con historias de usuario que describan valor de negocio — no tareas técnicas de bajo nivel, que corresponden a cada equipo de desarrollo al planear su trabajo.

---

## Objetivo

Dar una lista priorizada y organizada por fase de "qué se construye" en términos de valor para el actor que lo recibe, para que la planeación de cada fase parta de necesidades de negocio y no de una lista de pantallas inventada sobre la marcha.

## Alcance

**Incluye:** historias de usuario de alto nivel (formato "Como [actor], quiero [necesidad], para [beneficio]") agrupadas por fase del roadmap.

**No incluye:** tareas técnicas, estimaciones, asignación a sprints o desarrolladores, criterios de aceptación detallados — eso es responsabilidad de cada equipo al convertir estas historias en tickets de desarrollo.

---

## Problema que resuelve

Sin un backlog de negocio explícito, cada fase del roadmap corre el riesgo de interpretarse de forma distinta por quien la ejecuta. Este documento fija, para cada fase, qué historias de usuario debe poder cumplir el sistema al final de ella, en lenguaje de negocio verificable.

---

## Principios

- **Una historia, un actor, un beneficio.** Toda historia sigue el formato "Como [actor], quiero [necesidad], para [beneficio]", usando los actores de `actores.md`.
- **El backlog sigue al roadmap, no al revés.** Las historias se agrupan según la fase del roadmap donde aportan más valor; si una historia no encaja en ninguna fase, se cuestiona el roadmap antes de forzarla.
- **Multicanal y multi-tenant no se posponen silenciosamente.** Aunque el multi-tenant se materialice en la Fase 5, las historias de fases anteriores no deben redactarse de forma que contradigan esa arquitectura (ej. ninguna historia asume "hay un solo negocio para siempre").

---

## Reglas

- Toda historia nueva debe poder ubicarse en una única fase del roadmap; si aplica a varias, se coloca en la fase más temprana donde aporta valor y se referencia en las posteriores si se amplía.
- Ninguna historia de este backlog contradice una regla ya fijada en `reglas-de-negocio.md`.
- Las historias no incluyen solución técnica en su redacción (ej. "quiero ver mis pedidos de todos los canales en un panel", no "quiero una tabla SQL con todos los pedidos").

---

## Ejemplos

### Fase 1 — Fundación

*(Documentación y bases del proyecto — el backlog de esta fase es de documentación y arquitectura, no de historias de usuario final; se incluyen aquí sus equivalentes de negocio.)*

- Como **fundador del proyecto**, quiero tener la visión de producto, los módulos y las reglas de negocio documentados, para que cualquier decisión de arquitectura o desarrollo tenga una referencia clara y no dependa de memoria individual.
- Como **fundador del proyecto**, quiero que el modelo de datos se piense multi-tenant desde el inicio, para no tener que migrar datos dolorosamente cuando llegue el segundo tenant.

### Fase 2 — MVP piloto

- Como **cliente**, quiero navegar el catálogo y comprar un producto en la tienda propia, para resolver mi necesidad de compra sin fricción.
- Como **emprendedor**, quiero gestionar mi catálogo, inventario, precios y pedidos desde un panel administrativo, para operar mi negocio piloto sin depender de hojas de cálculo.
- Como **emprendedor**, quiero ver el estado de cada pedido (pagado, en preparación, despachado, entregado), para saber en qué punto está cada venta sin preguntarle al cliente.
- Como **cliente**, quiero recibir confirmación de mi pedido y poder rastrear su estado, para tener certeza de que mi compra va a llegar.

### Fase 3 — Logística y proveedores

- Como **emprendedor**, quiero comparar transportadoras por costo, tiempo y cobertura antes de despachar un pedido, para elegir la mejor opción sin investigar manualmente cada vez.
- Como **emprendedor**, quiero generar la guía de envío y hacer seguimiento del pedido hasta la entrega, para tener trazabilidad completa de la logística.
- Como **emprendedor**, quiero consultar un directorio de proveedores confiables filtrado por categoría de producto, para encontrar nuevas fuentes de abastecimiento con menos riesgo.
- Como **cliente**, quiero saber el costo de envío antes de pagar, para decidir mi compra con información completa.

### Fase 4 — Multicanal e inteligencia

- Como **emprendedor**, quiero conectar mi tienda a Mercado Libre y ver todos mis pedidos en un solo panel, para no operar cada canal por separado. (TikTok Shop se agrega cuando abra onboarding de vendedor local en Colombia — ver `docs/research/tiktok-shop.md`.)
- Como **emprendedor**, quiero que mi inventario se sincronice automáticamente entre todos mis canales de venta, para no vender un producto que ya no tengo disponible.
- Como **emprendedor**, quiero un dashboard con ventas, tendencias y comportamiento de clientes, para decidir con datos qué producto reabastecer o descontinuar.
- Como **emprendedor**, quiero planificar y medir campañas de Meta Ads y Google Ads desde la misma plataforma donde veo mis ventas, para entender el retorno real de mi inversión en publicidad.
- Como **emprendedor**, quiero que el sistema me sugiera qué producto reabastecer según su rotación, para anticiparme antes de quedarme sin stock.
- Como **emprendedor**, quiero que la IA me ayude a generar descripciones de producto y responder preguntas frecuentes, para dedicar menos tiempo a tareas repetitivas.

### Fase 5 — SaaS

- Como **emprendedor nuevo**, quiero crear mi propia cuenta (tenant) en la plataforma, para operar mi negocio de forma aislada de otros negocios que usan Proyecto Alfa.
- Como **administrador de la plataforma**, quiero monitorear la salud y el uso de cada tenant, para dar soporte proactivo y detectar problemas antes de que el emprendedor los reporte.
- Como **administrador de la plataforma**, quiero gestionar el modelo de cobro (licencia, suscripción o comisión) por tenant, para sostener económicamente la operación de la plataforma.
- Como **emprendedor nuevo**, quiero un proceso de configuración inicial guiado (tienda, canal, primer producto), para empezar a vender sin necesitar soporte técnico.

---

## Casos límite

- **Una historia de una fase posterior es fácil de construir junto con una de una fase anterior:** se documenta en su fase correspondiente igual; adelantarla es una decisión de planeación de desarrollo, no cambia dónde vive en este backlog.
- **Una historia deja de tener sentido tras aprender algo del piloto** (ej. el comparador de transportadoras resulta necesitar un paso adicional no previsto): se ajusta la historia y se anota el cambio en el historial de este documento, no se elimina silenciosamente.

---

## Decisiones futuras

- Priorización fina dentro de cada fase (qué historia va primero) — pendiente de definir con el fundador al iniciar cada fase.
- Historias de usuario específicas para el "portal de proveedores" y "portal de clientes" cuando se decida su alcance (hoy aspiracional, ver `vision-producto.md`).
- Historias de usuario del equipo de soporte formal, cuando ese rol se active en la Fase 5.

---

## Referencias

- [`docs/business/roadmap.md`](roadmap.md) — fases que organizan este backlog.
- [`docs/business/actores.md`](actores.md) — actores usados en cada historia.
- [`docs/business/casos-de-uso.md`](casos-de-uso.md) — casos de uso detallados detrás de varias de estas historias.
- [`docs/business/modulos.md`](modulos.md) — módulos a los que pertenece cada historia.

---

## Historial

- **2026-07-27** — Primera versión.
