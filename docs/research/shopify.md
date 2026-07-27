# Investigación: Shopify

## Propósito

Documentar qué es Shopify, cómo funciona su arquitectura multi-tenant, qué funcionalidades clave ofrece y qué se puede aprender de su modelo de extensibilidad (App Store, APIs), para que Proyecto Alfa tenga una referencia real — no supuesta — al diseñar su propia plataforma SaaS multi-tenant.

---

## Objetivo

Entender:

1. Cómo Shopify implementa multi-tenancy (un tenant = una tienda) sobre infraestructura compartida.
2. Qué funcionalidades "core" tiene una plataforma de e-commerce madura, para usarlas como vara de comparación del alcance de Proyecto Alfa.
3. Cómo Shopify expone su plataforma a terceros (App Store, APIs) y qué modelo de monetización usa con esos terceros — como insumo para la futura fase SaaS de Proyecto Alfa.

---

## Alcance

**Incluye:** modelo de negocio y de arquitectura de alto nivel de Shopify, catálogo de APIs públicas (Admin API, Storefront API, Functions, extensiones), modelo de precios de planes y de revenue share del App Store.

**No incluye:** una comparación exhaustiva funcionalidad-por-funcionalidad contra Proyecto Alfa (eso vive en `docs/research/competidores.md`), ni una decisión de si Proyecto Alfa debe imitar la arquitectura interna de Shopify (eso es una decisión de `docs/architecture/`).

---

## Problema que resuelve

Proyecto Alfa aspira a ser una plataforma SaaS multi-tenant (Fase 5 del roadmap) sin haber operado nunca una a esta escala. Diseñar esa fase "a ciegas" arriesga descubrir demasiado tarde limitaciones de arquitectura ya conocidas por quien construyó la plataforma multi-tenant de e-commerce más grande del mundo. Este documento adelanta ese aprendizaje.

---

## Principios

- **Un tenant = una tienda, con aislamiento de datos pero infraestructura compartida.** Shopify aloja millones de tiendas sobre una base de código y de infraestructura uniforme; cada tienda (tenant) tiene su propio dashboard, productos, pedidos y clientes, pero corre sobre el mismo "core". Esto valida el principio ya escrito en `vision-producto.md`: "multi-tenant desde el diseño, aunque el piloto sea single-tenant".
- **La extensibilidad no es un módulo, es la plataforma.** Shopify no construye cada funcionalidad de negocio internamente: expone APIs (Admin API GraphQL, Storefront API, Functions, Webhooks) para que un ecosistema de apps de terceros cubra necesidades verticales (envíos, contabilidad, marketing). Esto es una lección directa para el "directorio de proveedores" y las integraciones de transportadoras/marketplaces de Proyecto Alfa: pensarlas desde el inicio como integraciones desacopladas vía API, no como código hardcodeado al negocio piloto (coincide con la regla ya escrita en `vision-producto.md`).
- **GraphQL como interfaz preferida sobre REST.** Shopify migró su ecosistema de desarrollo hacia GraphQL Admin API como interfaz principal, dejando el REST Admin API como "legacy" y desaconsejado para integraciones nuevas. Es una señal de hacia dónde evolucionan las APIs de plataformas de e-commerce grandes.
- **Backend logic personalizable sin forkear el core (Shopify Functions).** Permite a un desarrollador de tienda cambiar lógica de descuentos, pagos o entrega sin tocar el núcleo de la plataforma — un patrón de extensibilidad seguro (sandboxed) útil si Proyecto Alfa algún día permite personalización por tenant.
- **Monetización de terceros alineada a su éxito, no a su intento.** El modelo de revenue share de Shopify exime del cobro a un desarrollador de app hasta el primer millón de USD de ingresos, y cobra 15% después. Es un patrón replicable si Proyecto Alfa abre su propio ecosistema de integraciones/plugins en el futuro.

---

## Reglas

- Cuando Proyecto Alfa diseñe el modelo de datos multi-tenant (Fase 5), debe evaluar el patrón de aislamiento por tenant que usa Shopify (separación lógica sobre infraestructura compartida) como una opción válida, documentándolo en `docs/architecture/base-de-datos.md` cuando se escriba.
- Toda integración externa (marketplace, transportadora, pasarela de pago) debe diseñarse pensando en una interfaz de API propia y estable dentro de Proyecto Alfa (equivalente conceptual a un "Admin API" interno), de forma que agregar un proveedor nuevo del mismo tipo no requiera tocar el núcleo — igual que Shopify no reescribe su core por cada app nueva del App Store.
- Las funcionalidades "core" de tienda virtual de Proyecto Alfa (catálogo, checkout, cliente, pedidos) deben, como mínimo, cubrir lo que Shopify ofrece de fábrica en su plan de entrada, antes de considerarse "a la par" de la competencia (ver `docs/research/competidores.md`).
- Si en el futuro Proyecto Alfa abre un ecosistema de apps/plugins de terceros, la política de revenue share debe documentarse como decisión de negocio explícita (no heredarse implícitamente del ejemplo de Shopify).

---

## Ejemplos

- **Multi-tenancy en acción:** un mismo código de aplicación de Shopify sirve tanto a una tienda con 5 productos como a una con 50.000, sin que el dueño de la tienda pequeña note que comparte infraestructura con miles de otras tiendas.
- **Extensión vía Shopify Functions:** un comerciante activa una app de descuentos por volumen que se ejecuta en el proceso de checkout sin que Shopify haya construido esa lógica en su core — la app "engancha" en un punto de extensión definido.
- **App Store como canal de distribución:** un desarrollador tercero construye una app de sincronización de inventario con un ERP externo, la publica en el Shopify App Store, y accede a todos los comerciantes de Shopify como canal de distribución, pagando revenue share solo si supera el umbral de ingresos.

---

## Casos límite

- El **revenue share de Shopify cambió su modelo**: pasó de un esquema con reinicio anual a un umbral acumulado de por vida (USD 1,000,000), con 15% sobre el excedente. Desarrolladores con más de USD 20,000,000 de ingresos anuales por el App Store, o compañías con más de USD 100,000,000 de ingresos brutos, pagan 15% desde el primer dólar (no aplica la exención). Esto indica que los modelos de revenue share de plataformas grandes son dinámicos y deben revisarse periódicamente, no asumirse fijos.
- Los **planes pagos de Shopify** (2026) parten en **USD 39/mes (Basic)**, **USD 105/mes (Grow)** y **USD 399/mes (Advanced)**, con 25% de descuento en facturación anual — es la referencia de precio de entrada contra la que Proyecto Alfa deberá posicionarse si compite por el mismo segmento de emprendedor.
- Shopify desalienta activamente el uso del REST Admin API para integraciones nuevas: cualquier evaluación técnica futura de "integrar como Shopify" debe partir de GraphQL, no de REST.

---

## Decisiones futuras

- Si Proyecto Alfa, en su fase SaaS, abrirá un ecosistema de apps/plugins de terceros al estilo App Store, y bajo qué modelo de revenue share.
- Si el aislamiento de datos multi-tenant de Proyecto Alfa será por esquema, por base de datos o por fila (row-level), y cómo se compara con el enfoque de Shopify — decisión que corresponde a `docs/architecture/base-de-datos.md`.
- Si Proyecto Alfa expondrá una API pública propia (equivalente a Admin API/Storefront API) para que terceros construyan integraciones, y en qué fase del roadmap.

---

## Referencias

- [Multi-Tenant Architecture: Best Practices for App Developers (2026) - Shopify](https://www.shopify.com/blog/multi-tenant-architecture)
- [Shopify APIs, libraries, and tools](https://shopify.dev/docs/api)
- [Shopify Help Center | Building and monetizing apps with Shopify's APIs](https://help.shopify.com/en/partners/build-integrate/making-apps)
- [Revenue share for Shopify App Store developers](https://shopify.dev/docs/apps/launch/distribution/revenue-share)
- [Update to Shopify's app developer revenue share - Shopify developer changelog](https://shopify.dev/changelog/update-to-shopifys-app-developer-revenue-share)
- [Shopify Help Center | Shopify Partner earnings](https://help.shopify.com/en/partners/partner-program/how-to-earn)
- [Shopify Pricing (2026) – Plans, Fees & Cost Breakdown](https://www.demandsage.com/shopify-pricing/)
- [Ecommerce APIs: Types and Integration Guide (2026) - Shopify](https://www.shopify.com/enterprise/blog/ecommerce-api)

---

## Historial

- **2026-07-27** — Primera versión, basada en investigación web.
