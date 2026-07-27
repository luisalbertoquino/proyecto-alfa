# Investigación: Mercado Libre (API para vendedores)

## Propósito

Documentar qué expone la API pública de Mercado Libre Developers para vendedores: autenticación, gestión de publicaciones, pedidos, inventario, preguntas y envíos (Mercado Envíos), como insumo directo para diseñar la integración multicanal de Proyecto Alfa.

---

## Objetivo

Responder, con evidencia y no supuestos:

1. ¿Cómo funciona la autenticación/autorización de la API de Mercado Libre y qué implica para una app multi-tenant?
2. ¿Qué recursos expone la API para publicar productos, gestionar pedidos, inventario y preguntas de compradores?
3. ¿Qué límites de uso (rate limits) existen y qué tan predecibles son?
4. ¿Qué tan disponible y madura es esta integración para el mercado colombiano, comparada con TikTok Shop?

---

## Alcance

**Incluye:** API REST central de Mercado Libre (items/publicaciones, categorías, órdenes, preguntas, mensajes, usuarios, sitios) y la API de Mercado Envíos (creación de envíos, tracking, etiquetas, logística), modelo OAuth2, rate limits, particularidades del sitio Colombia (MCO).

**No incluye:** Mercado Pago (pasarela de pagos, es otro producto/API), Mercado Ads, ni el diseño interno de cómo Proyecto Alfa mapeará categorías de Mercado Libre a su propio catálogo (eso es una decisión de diseño de datos, no de investigación).

---

## Problema que resuelve

`vision-producto.md` deja como decisión futura pendiente el orden de integración de marketplaces ("TikTok Shop vs. Mercado Libre"). La investigación de `docs/research/tiktok-shop.md` mostró que TikTok Shop no tiene onboarding de vendedor local confirmado en Colombia. Este documento evalúa la alternativa — Mercado Libre, marketplace establecido y operativo en Colombia — para informar esa decisión con datos reales.

---

## Principios

- **OAuth 2.0 estándar, con "Authorization Code Grant" para apps server-side.** Es el flujo recomendado explícitamente por Mercado Libre para aplicaciones que ejecutan código del lado del servidor (como el backend Laravel de Proyecto Alfa) — no requiere flujos alternativos ni SDKs propietarios complejos.
- **Tokens de vida corta, con refresh obligatorio.** El `access_token` expira en 6 horas (21,600 segundos). Cualquier integración debe construirse asumiendo renovación automática vía `refresh_token`, no un token de larga duración — esto afecta el diseño de cualquier job en segundo plano que sincronice pedidos o inventario.
- **Scopes explícitos y granulares.** El modelo de permisos usa los valores `read`, `write` y `offline_access` (este último habilita el uso del refresh_token). Proyecto Alfa debe solicitar solo los scopes que necesita, alineado con el principio general de mínimo privilegio.
- **Un "item" (publicación) no es lo mismo que un "producto" en el catálogo de Mercado Libre.** El modelo de datos de Mercado Libre distingue publicaciones (items, con atributos, variaciones, envío, calificaciones) del catálogo homologado del sitio; publicar bien requiere primero predecir/asignar categoría correctamente (existe un "predictor de categorías" en la API) — un paso adicional que no tiene equivalente en una tienda propia.
- **Mercado Envíos ya cubre el ciclo logístico del marketplace.** La API de Mercado Envíos permite creación de envíos, tracking, generación de etiquetas y gestión de retiro (pickup) directamente integrada a la orden — es decir, cuando se vende por Mercado Libre, la logística del pedido puede resolverse dentro del mismo ecosistema, sin necesariamente pasar por el comparador de transportadoras propio de Proyecto Alfa (ver `docs/research/logistica.md`).
- **Rate limit alto pero no ilimitado, con headroom operable.** El límite documentado (para la API general) es de 1,500 requests por minuto por vendedor, con un header `RateLimit-Remaining` para monitorear consumo en tiempo real — esto es holgado para un solo vendedor pero exige diseño cuidadoso si Proyecto Alfa, en su fase SaaS, multiplica llamadas por tenant.

---

## Reglas

- La integración de Proyecto Alfa con Mercado Libre debe implementar el flujo OAuth2 Authorization Code (server-side) con `offline_access`, y un job de renovación automática del access_token antes de que expire (ventana de 6 horas), nunca depender de renovación manual.
- El sistema unificado de sincronización de inventario (regla ya definida en `vision-producto.md`) debe tratar cada publicación (item_id) de Mercado Libre como una unidad sincronizable propia, dado que el inventario en Mercado Libre se gestiona por publicación/variación, no por SKU global directamente.
- Toda llamada a la API de Mercado Libre debe monitorear el header `RateLimit-Remaining` y aplicar backoff ante HTTP 429, igual que se exige para TikTok Shop.
- Dado que Mercado Envíos resuelve logística dentro del marketplace, el comparador de transportadoras de Proyecto Alfa debe aplicarse solo a los pedidos de tienda propia (y otros canales sin logística integrada), no forzar una comparación redundante sobre pedidos que ya usan Mercado Envíos.
- Antes de comprometer fecha de integración, verificar el catálogo de categorías vigente para el sitio Colombia (MCO) y el proceso de homologación al catálogo de Mercado Libre, ya que puede afectar el flujo de "creación automática de ficha de producto con IA" descrito en `vision-producto.md`.

---

## Ejemplos

- **Publicar un producto:** Proyecto Alfa envía el título del producto al predictor de categorías → recibe la categoría sugerida de Mercado Libre → construye el payload de publicación (item) con atributos requeridos por esa categoría, precio, stock, fotos → publica vía API → Mercado Libre devuelve un `item_id` que Proyecto Alfa guarda como referencia del canal.
- **Sincronizar un pedido:** cuando ocurre una venta en Mercado Libre, la API de órdenes expone el detalle del pedido (comprador, ítems, importe, estado de pago) para que Proyecto Alfa lo refleje en el panel central de pedidos — el mismo patrón que exige el objetivo específico #8 de `vision-producto.md`.
- **Responder una pregunta de comprador:** la API de Preguntas y Respuestas (`api_version=4`) permite leer preguntas pendientes de un producto y responderlas programáticamente — un candidato directo para la automatización de "respuestas a preguntas frecuentes" con IA mencionada en `vision-producto.md`.
- **Generar una guía de envío:** tras confirmar un pedido, la API de Mercado Envíos permite generar la etiqueta de envío y consultar el estado de tracking sin salir del ecosistema de Mercado Libre.

---

## Casos límite

- **Los tokens expiran en 6 horas** — cualquier proceso batch nocturno de sincronización debe verificar vigencia del token antes de ejecutarse, no asumir que un token generado en la mañana sigue siendo válido en la noche.
- **El refresh_token puede invalidarse por eventos ajenos a la integración** (ej. el vendedor cambia su contraseña en Mercado Libre), lo que rompe la sincronización sin aviso previo desde el lado de Proyecto Alfa — el sistema debe detectar error de token inválido y notificar al vendedor para reautorizar, en línea con el principio de "degradar con gracia, alertar en vez de fallar en silencio" de `vision-producto.md`.
- **Rate limit de 1,500 req/min es por vendedor, no por app** — en un escenario SaaS multi-tenant con muchos vendedores conectados simultáneamente, el límite agregado de la app podría requerir arquitectura de colas por tenant para no saturar la cuota global de la aplicación de Proyecto Alfa.
- **Mercado Libre puede aplicar límites discrecionales adicionales** según el volumen o forma de uso de una aplicación específica, más allá del límite documentado — esto significa que el límite "oficial" no es garantía absoluta y debe monitorearse en producción.
- **Múltiples dominios de documentación por país** (`developers.mercadolibre.com.ar`, `.co`, `.cl`, etc. y `global-selling.mercadolibre.com` para Cross-Border Trade) generan riesgo de leer documentación desactualizada o de un sitio distinto a Colombia (MCO); se debe validar contra `developers.mercadolibre.com.co` específicamente al construir la integración.

---

## Decisiones futuras

- Confirmar si, dado que Mercado Libre sí tiene onboarding de vendedor operativo en Colombia (a diferencia de TikTok Shop, ver `docs/research/tiktok-shop.md`), se prioriza como primer marketplace a integrar después de la tienda propia.
- Definir si Proyecto Alfa usará el catálogo homologado de Mercado Libre (mejor visibilidad en el marketplace) o publicaciones libres (más rápido de implementar pero con menor posicionamiento).
- Evaluar si vale la pena integrar Mercado Envíos como opción de despacho dentro del propio comparador de transportadoras de Proyecto Alfa, o mantenerlo fuera de ese módulo por ser específico del canal.
- Diseñar la estrategia de manejo de rate limit y colas para el escenario multi-tenant (Fase 5), antes de que el volumen de vendedores conectados lo haga urgente.

---

## Referencias

- [Mercado Libre Developers — API Docs (Colombia)](https://developers.mercadolibre.com.co/es_ar/guia-para-producto)
- [Authentication and Authorization - Developers - Mercado Libre](https://developers.mercadolibre.com.ar/en_us/authentication-and-authorization)
- [Mercado Libre API Essential Guide - Rollout](https://rollout.com/integration-guides/mercado-libre/api-essentials)
- [Rate limits (429 / local_rate_limited) and shared quotas in CBT](https://global-selling.mercadolibre.com/devsite/manage-questions-answers-global-selling/question-3)
- [Mercado Libre Global Selling Developer Terms and Conditions](https://global-selling.mercadolibre.com/devsite/mercado-libre-global-selling-developer-terms-and-conditions)
- [Preguntas y Respuestas - Developers - Mercado Libre](https://developers.mercadolibre.cl/es_ar/preguntas-y-respuestas)
- [Publicar productos - Developers - Mercado Libre](https://developers.mercadolibre.cl/es_ar/publica-productos)
- [API Docs - Global Selling - Mercado Libre](https://global-selling.mercadolibre.com/devsite/api-docs)

---

## Historial

- **2026-07-27** — Primera versión, basada en investigación web.
