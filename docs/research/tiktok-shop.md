# Investigación: TikTok Shop (API para vendedores)

## Propósito

Documentar qué expone la API de TikTok Shop para vendedores (TikTok Shop Partner Center / Seller API), qué se requiere para integrarse, y qué limitaciones reales existen — en particular su disponibilidad geográfica — como insumo directo para diseñar la integración multicanal de Proyecto Alfa.

---

## Objetivo

Responder, con evidencia y no supuestos:

1. ¿Qué operaciones permite la API de TikTok Shop sobre catálogo, inventario, pedidos y logística?
2. ¿Cómo es el modelo de autenticación/autorización y qué se necesita para que una app quede aprobada?
3. ¿Qué límites de uso (rate limits) y restricciones de aprobación existen?
4. ¿TikTok Shop está disponible como canal de venta para un vendedor en Colombia hoy?

---

## Alcance

**Incluye:** capacidades de la API pública para vendedores (Seller API) vía TikTok Shop Partner Center: productos, inventario, pedidos, cumplimiento (fulfillment), logística, webhooks, autenticación OAuth, rate limits, requisitos de aprobación de app y de tienda, disponibilidad regional en Latinoamérica.

**No incluye:** la API de Afiliados/Creadores de TikTok Shop, la API de contenido/marketing de TikTok Ads (eso es otro dominio), ni el diseño de cómo Proyecto Alfa sincronizará inventario internamente (eso vive en `docs/business/reglas-de-negocio.md` y en la arquitectura del módulo multicanal).

---

## Problema que resuelve

`vision-producto.md` lista a TikTok Shop como uno de los canales objetivo de la integración multicanal, y como una de las dos decisiones futuras pendientes ("¿cuál se conecta primero, TikTok Shop vs. Mercado Libre?"). Esa decisión no se puede tomar sin saber si TikTok Shop siquiera opera como canal de venta formal en el mercado inicial de Proyecto Alfa (Colombia). Este documento responde esa pregunta antes de comprometer trabajo de ingeniería.

---

## Principios

- **La API cubre el ciclo completo de venta, no solo catálogo.** El Seller API de TikTok Shop expone gestión de vendedor, productos, promociones, pedidos, cumplimiento (fulfillment), logística, devoluciones/reembolsos, finanzas, analítica y servicio/mensajería al cliente — un alcance comparable al de un ERP de canal, no solo un feed de productos.
- **Autorización por tienda, no por cuenta de desarrollador.** El flujo OAuth de TikTok Shop autoriza acceso a una tienda específica (shop) y entrega, además del access token y refresh token, un "shop cipher" — un identificador que debe enviarse en cada llamada para indicar a qué tienda pertenece la operación. Esto es clave si Proyecto Alfa un día maneja múltiples tiendas TikTok Shop (multi-tenant): el modelo de autorización ya es "multi-shop aware" de origen.
- **Rate limit dinámico, no fijo.** A diferencia de un límite fijo por app, el cupo de requests de TikTok Shop se calcula dinámicamente según el número de tiendas autorizadas por la app — más tiendas conectadas, más cupo. Esto es relevante para el diseño de una integración multi-tenant: el rate limit escala con el crecimiento, pero también hay que monitorearlo por app, no asumirlo fijo.
- **Aprobación de app + aprobación de tienda son dos procesos distintos.** No basta con que el vendedor tenga tienda aprobada en TikTok Shop (verificación de negocio: licencia, identidad, datos bancarios); la aplicación/integrador también debe pasar revisión de TikTok (estabilidad, seguridad, cumplimiento de políticas) antes de poder usarse en producción.
- **La región del negocio se fija una sola vez.** Al configurar la cuenta de vendedor, el campo de región de negocio solo puede seleccionarse una vez y no se puede cambiar después — una decisión operativa que Proyecto Alfa debe advertir claramente al emprendedor antes de que la tome.

---

## Reglas

- Antes de comprometer una fecha de integración con TikTok Shop en el roadmap, se debe confirmar la disponibilidad de TikTok Shop como canal de venta directo para vendedores locales en Colombia (ver Casos límite — a julio 2026 no está confirmado como mercado activo).
- La integración de Proyecto Alfa con TikTok Shop debe construirse contra el Seller API v2 (Partner Center), no contra endpoints legacy, y debe implementar manejo explícito de HTTP 429 (rate limit) con backoff, dado que el límite es dinámico y depende del número de tiendas conectadas.
- El módulo de sincronización de inventario de Proyecto Alfa (regla ya definida en `vision-producto.md`: "todo módulo que toque inventario debe pasar por el sistema unificado") debe tratar el `shop cipher` de TikTok Shop como parte de la identidad del canal, no como un dato opcional.
- Cualquier caída, cambio de política o revocación de autorización de TikTok Shop debe degradar el canal de forma aislada (principio ya definido en `vision-producto.md` sobre caída de API de marketplace), sin bloquear la sincronización de otros canales.

---

## Ejemplos

- **Flujo de autorización real:** el vendedor aprueba la app de Proyecto Alfa desde TikTok Shop → TikTok redirige a la URL de retorno de Proyecto Alfa con un código de autorización → Proyecto Alfa envía ese código junto con su App Key y App Secret al endpoint de token de TikTok → recibe `access_token`, `refresh_token` y `shop_cipher` → usa esos tres datos en cada llamada posterior a la API para esa tienda específica.
- **Sincronización de producto:** la API permite crear, actualizar y consultar productos (incluyendo variantes, imágenes, precios) y sincronizar inventario en tiempo real — el mismo patrón que necesita el "sistema unificado de inventarios" descrito en `vision-producto.md`.
- **Ambiente sandbox:** TikTok Shop ofrece un entorno de pruebas (sandbox) con límite unificado de 1,000 QPH (queries por hora) para todas las tiendas de prueba, permitiendo desarrollar y probar la integración antes de solicitar acceso de producción.

---

## Casos límite

- **Disponibilidad regional limitada en Latinoamérica.** A julio de 2026, TikTok Shop para vendedores locales opera de forma madura en México, y Brasil se lanzó como segundo mercado latinoamericano (mayo de 2025). Colombia, Chile y Perú aparecen descritos como mercados en construcción de volumen "a través de pilotos y flujos indirectos", no como mercados con onboarding de vendedor local plenamente abierto. **Esto es una limitación crítica para Proyecto Alfa**, cuyo mercado inicial asumido es Colombia: la integración con TikTok Shop podría no ser viable como canal de venta directo local en el corto plazo, y debe verificarse con la documentación oficial vigente y con TikTok directamente antes de comprometerse en el roadmap.
- **Aprobación de tienda toma días, no minutos.** El registro de una tienda de vendedor (documentación de negocio: licencia comercial, identidad, datos bancarios) toma aproximadamente 2-3 días hábiles de revisión antes de aprobarse — debe considerarse en cualquier estimación de tiempo de onboarding de un emprendedor al canal.
- **Aprobación de la app integradora es independiente y puede rechazarse.** TikTok revisa la app de Proyecto Alfa por estabilidad, seguridad y cumplimiento de políticas antes de autorizar su uso en producción; no es un simple registro automático.
- **Rate limiting por ventana deslizante de un minuto.** Si se supera el umbral de requests en la ventana, TikTok responde con HTTP 429 y limita nuevas solicitudes — la integración debe implementar colas/reintentos con backoff, no asumir throughput ilimitado.
- **El campo de región de negocio es irreversible** una vez seleccionado en el onboarding del vendedor.

---

## Decisiones futuras

- Confirmar con documentación oficial actualizada (o directamente con TikTok) si a la fecha de implementación existe onboarding de vendedor local para Colombia; si no, evaluar si la integración se pospone o si Proyecto Alfa la construye igual pensando en expansión a México/Brasil.
- Definir si Proyecto Alfa se integra primero con TikTok Shop o con Mercado Libre (decisión pendiente ya listada en `vision-producto.md`) — este documento aporta evidencia de que, para el mercado inicial (Colombia), Mercado Libre es hoy la opción de integración más segura (ver `docs/research/mercado-libre.md`).
- Evaluar el costo/complejidad de mantener credenciales de app (App Key/App Secret) y shop ciphers por cada tienda conectada en un modelo multi-tenant futuro.

---

## Referencias

- [TikTok Shop Partner Center — Seller API overview](https://partner.tiktokshop.com/docv2/page/seller-api-overview)
- [API rate limits - TikTok Shop Partner Center](https://partner.tiktokshop.com/docv2/page/rate-limits)
- [TikTok Shop API: Complete Integration Guide for Sellers](https://www.keyapi.ai/blog/tiktok-shop-api-integration-guide-sellers/)
- [TikTok Shop Across LatAm: Where It's Live and What's Coming](https://blog.m2ecloud.com/tiktok-shop-across-latam-where-it-s-live-and-what-s-coming/)
- [TikTok Shop - Enable API Access – DropStream](https://support.getdropstream.com/hc/en-us/articles/33561757875219-TikTok-Shop-Enable-API-Access)
- [15 companies from Fulfillment Mexico TikTok Shop | Cubbo](https://www.cubbo.com/en/posts/best-fulfillment-companies-mexico-tiktok-shop)

---

## Historial

- **2026-07-27** — Primera versión, basada en investigación web.
