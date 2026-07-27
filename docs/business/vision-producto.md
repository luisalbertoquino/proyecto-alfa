# Visión de Producto

## Propósito

Este documento es la fuente de verdad sobre **qué es Proyecto Alfa y por qué existe**, desde la perspectiva de producto y negocio. Cualquier decisión de arquitectura, diseño o desarrollo debe poder trazarse hasta un objetivo o principio descrito aquí. Si una funcionalidad no sirve a ninguno de los objetivos listados, no pertenece al producto — o este documento está desactualizado y debe corregirse primero.

---

## Objetivo

**Objetivo general**

Desarrollar una plataforma integral para emprendedores y comercios electrónicos que centralice la gestión de ventas, inventarios, logística, publicidad y analítica de múltiples canales digitales, incorporando herramientas de inteligencia artificial para automatizar procesos, optimizar la operación y aumentar las oportunidades de venta.

**Objetivos específicos**

1. Diseñar una tienda virtual moderna orientada a maximizar la conversión de visitantes en clientes.
2. Implementar un panel administrativo para gestionar productos, inventario, pedidos, clientes y ventas.
3. Integrar un comparador de transportadoras para elegir envío según costo, tiempo y cobertura.
4. Centralizar la gestión logística: despacho y seguimiento de pedidos.
5. Incorporar un directorio de proveedores confiables para facilitar el abastecimiento.
6. Desarrollar un módulo de publicidad digital (Meta Ads, Google Ads, futuras integraciones) para planificar, administrar y medir campañas.
7. Implementar un dashboard de inteligencia comercial: ventas, comportamiento de clientes, tendencias de productos.
8. Integrar múltiples canales de venta (tienda propia, TikTok Shop, Mercado Libre, otros marketplaces) y **centralizar en un único panel** la información de pedidos que llega de todos ellos.
9. Implementar un sistema unificado de inventarios que sincronice existencias entre canales, para reducir sobreventa.
10. Analizar historial de ventas, tendencias de mercado y rotación de productos para sugerir qué reabastecer o incorporar al catálogo.
11. Entregar reportes y métricas en tiempo real que soporten decisiones de ventas, inventario, logística, rentabilidad y crecimiento.
12. Automatizar procesos repetitivos con IA: creación de páginas de producto, descripciones, respuestas a preguntas frecuentes, administración general.
13. Diseñar la arquitectura para operar como SaaS (licencias, suscripciones o comisión por venta), permitiendo que otros emprendedores creen sus propias tiendas.
14. Usar el negocio piloto de Proyecto Alfa como caso de estudio para validar funcionalidades y medir resultados antes de comercializar.

---

## Alcance

**Incluye** (visión de producto, el "qué" y el "para quién"):

- El ecosistema completo descrito en los objetivos: tienda, pedidos, comparador de envíos, proveedores, dashboard de analítica, IA, multicanal, publicidad, reportes.
- La relación entre el negocio piloto (single-tenant, uso real) y la futura oferta SaaS (multi-tenant, para terceros).
- Los usuarios objetivo: en el piloto, el equipo que opera el negocio; en el SaaS, emprendedores de comercio electrónico sin conocimientos técnicos.

**No incluye** (vive en otros documentos):

- Decisiones de arquitectura técnica, stack o modelo de datos → `docs/architecture/vision-tecnica.md`, stack tecnológico, `docs/architecture/base-de-datos.md`.
- Identidad visual y sistema de componentes → `docs/design/design-system.md`.
- Fechas, hitos y secuencia de entregas → `docs/business/roadmap.md`.
- Reglas de negocio detalladas (ej. cómo se calcula el costo de envío) → `docs/business/reglas-de-negocio.md`.

---

## Problema que resuelve

Un emprendedor de comercio electrónico hoy opera con herramientas fragmentadas:

- Vende en varios canales sin un lugar único donde ver todos sus pedidos.
- Controla inventario manualmente o por canal, con riesgo constante de sobreventa.
- Elige transportadora sin comparar costo, tiempo y cobertura.
- No tiene visibilidad de qué productos rotan o qué tendencias está perdiendo.
- Pierde tiempo en tareas repetitivas: descripciones de producto, preguntas frecuentes, reportes manuales.

Proyecto Alfa existe para que ese emprendedor pase de operar disperso, a mano y por intuición, a operar desde un solo sistema con datos.

---

## Principios

- **El piloto manda.** Ninguna funcionalidad se generaliza al modelo SaaS sin haber sido validada primero operando el negocio piloto real.
- **Multicanal por diseño, no como añadido.** El modelo de datos y de pedidos se piensa desde el inicio para múltiples canales, aunque al principio solo se use uno.
- **Automatizar antes que administrar.** Ante una tarea repetitiva, la primera pregunta es si la IA puede hacerla, no cómo hacer más fácil hacerla a mano.
- **Multi-tenant desde el diseño, aunque el piloto sea single-tenant.** La arquitectura no debe requerir una reescritura para pasar de "un negocio" a "muchos negocios".
- **Módulos desacoplados.** Cada capacidad (logística, proveedores, publicidad, BI) debe poder evolucionar o incluso apagarse sin romper las demás.

---

## Reglas

- Todo objetivo específico nuevo debe mapear a uno de los módulos listados en "Alcance"; si no encaja en ninguno, se discute como cambio de alcance, no se agrega silenciosamente.
- Ninguna integración de canal (marketplace, transportadora, ads) se construye "a medida" del piloto si eso impide añadir un segundo proveedor del mismo tipo después.
- Todo módulo que toque inventario debe pasar por el sistema unificado de sincronización — no se permiten actualizaciones de stock que solo escriban a un canal.
- Las decisiones de monetización SaaS (licencia vs. suscripción vs. comisión) no bloquean el desarrollo del piloto; se documentan como decisión futura, no como requisito actual.

---

## Ejemplos

- Un cliente compra en TikTok Shop y en la tienda propia el mismo producto el mismo día: el inventario se descuenta en ambos canales desde el mismo dato de stock, sin sobreventa.
- El dashboard de inteligencia comercial señala que un producto tuvo alta rotación la última semana y sugiere reabastecerlo antes de que se agote.
- El emprendedor compara tres transportadoras para un mismo pedido y el sistema recomienda la de mejor relación costo/tiempo para esa zona de cobertura.
- La IA genera automáticamente la descripción de un producto nuevo a partir de una foto y datos básicos, lista para publicar en la tienda y en los marketplaces conectados.

---

## Casos límite

- **Caída o cambio de API de un marketplace** (TikTok Shop, Mercado Libre): el sistema debe degradar con gracia — dejar de sincronizar ese canal sin afectar los demás, y alertar en vez de fallar en silencio.
- **Venta simultánea en dos canales sobre el último ítem de stock:** resuelto — gana el pedido con pago confirmado primero; el perdedor pasa a "pendiente de confirmación" con notificación inmediata al emprendedor (ver Regla 1 en `docs/business/reglas-de-negocio.md`).
- **Emprendedor sin conocimientos técnicos** en el escenario SaaS: cualquier flujo de configuración de canal, envío o publicidad debe poder completarse sin asistencia técnica.
- **Negocio piloto creciendo antes de que exista el modelo SaaS:** el sistema debe soportar el crecimiento del piloto sin depender de que el multi-tenant ya esté construido.

---

## Decisiones futuras

- Modelo de monetización SaaS: licencia fija, suscripción o comisión por venta (o combinación).
- Proveedor de IA para automatización (modelo propio vs. proveedor externo tipo Claude/OpenAI) y hasta dónde llega la automatización sin supervisión humana.
- Alcance exacto del "portal de proveedores" y "portal de clientes" mencionados en la visión a largo plazo — hoy son aspiracionales, no comprometidos en el roadmap.

**Resueltas desde la primera versión de este documento:**
- ~~Orden de integración de marketplaces~~ → Mercado Libre primero (mercado piloto: Colombia, donde Mercado Libre opera y TikTok Shop aún no tiene onboarding de vendedor local abierto). Ver `docs/research/tiktok-shop.md` y `docs/business/roadmap.md` (Fase 4).
- ~~Regla de desempate para sobreventa multicanal~~ → definida en `docs/business/reglas-de-negocio.md` (Regla 1).

---

## Referencias

- [`README.md`](../../README.md) — visión global del proyecto.
- `docs/architecture/vision-tecnica.md` — cómo se construye (en construcción).
- `docs/business/roadmap.md` — fases y secuencia (en construcción).
- `docs/business/documento-maestro.md` — documento maestro de negocio (en construcción).
- `docs/business/modulos.md` — desglose de cada módulo del ecosistema (en construcción).

---

## Historial

- **2026-07-27** — Primera versión. Consolida el objetivo general y los objetivos específicos originales del proyecto con las adiciones y la reformulación aportadas por Angie durante la sesión de fundación del repositorio.
