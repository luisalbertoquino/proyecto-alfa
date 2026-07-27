# Diccionario del Negocio

## Propósito

Fijar, sin ambigüedad, el significado de cada término de negocio usado en Proyecto Alfa. Este documento es la **fuente única de verdad terminológica**: todos los demás documentos de negocio y arquitectura (README, `vision-producto.md`, `actores.md`, `casos-de-uso.md`, `modulos.md`, `reglas-de-negocio.md`, `roadmap.md`, `product-backlog.md`, y los documentos técnicos que los desarrollan) deben usar estos términos exactamente con este significado. Si un documento usa una palabra de forma distinta a como está definida aquí, el documento está mal — no el diccionario.

---

## Objetivo

Eliminar la ambigüedad que ya existe en el uso suelto de palabras como "cliente" (¿el emprendedor que paga la plataforma, o quien compra en su tienda?) y "pedido" vs. "orden", para que cualquier persona — humana o IA — que lea la documentación, escriba código o defina un modelo de datos, use siempre la misma palabra para la misma cosa.

## Alcance

**Incluye:** términos de negocio y de dominio de comercio electrónico que aparecen o van a aparecer en documentos de negocio, arquitectura funcional y modelo de datos: roles de personas, entidades del ecosistema (tenant, canal, pedido, SKU, proveedor, transportadora, campaña) y los conceptos multi-tenant y multicanal que los atraviesan.

**No incluye:** vocabulario puramente técnico de infraestructura (contenedor, cola, caché, migración de base de datos) — ese vocabulario ya es estándar de la industria y no requiere glosario propio; si en el futuro genera confusión, se documentará en un glosario técnico dentro de `docs/architecture/`.

---

## Problema que resuelve

El README y `vision-producto.md` ya usan "cliente", "pedido", "canal", "tenant" y "proveedor" de forma suelta, apoyándose en que el lector infiere el significado por contexto. Eso funciona mientras el equipo es una persona y el sistema es single-tenant. Deja de funcionar en cuanto:

- Se documenta el modelo SaaS multi-tenant y hay que distinguir claramente entre "el negocio que paga por usar la plataforma" y "la persona que le compra a ese negocio".
- Se integra un segundo canal de venta (marketplace) que usa su propia palabra para "pedido" (ej. Mercado Libre lo llama "orden") y hay que decidir si eso es el mismo concepto o no.
- Entra una persona nueva al proyecto — o un modelo de IA generando código o documentación — sin el contexto tácito que hoy solo existe en la cabeza del fundador.

Este diccionario resuelve eso fijando una palabra canónica por concepto, antes de que la ambigüedad se filtre al modelo de datos o al código.

---

## Principios

- **Un concepto, una palabra canónica.** Cuando existan sinónimos válidos en el lenguaje común (ej. "pedido" y "orden"), se elige uno como canónico para el proyecto y el otro se documenta explícitamente como sinónimo no preferido, no se prohíbe su existencia en el mundo real (ej. en la respuesta de un marketplace).
- **"Cliente" nunca es el tenant.** Es la fuente de confusión más probable del proyecto porque en el lenguaje SaaS genérico "cliente" suele significar "quien paga la suscripción". En Proyecto Alfa esa persona es el **emprendedor**, dueño de un **tenant**. "Cliente" se reserva siempre para el comprador final.
- **Los nombres del modelo de datos siguen al diccionario, no al revés.** Cuando se diseñen las tablas (`docs/architecture/base-de-datos.md`), los nombres de entidades y columnas deben reflejar estos términos (ej. tabla `pedidos`, no `orders` ni `ordenes`, salvo que se decida inglés como estándar técnico general).
- **El glosario crece por decisión explícita.** Un término nuevo se agrega aquí cuando se usa por primera vez en un documento de negocio o arquitectura, no después de que ya se usó de tres formas distintas en tres documentos.

---

## Reglas

Definiciones canónicas — usar exactamente estos términos en toda la documentación:

- **Tenant / negocio.** Cuenta de un emprendedor dentro de la plataforma Proyecto Alfa: es el "cliente" de la plataforma SaaS en el sentido de negocio-a-negocio, pero en esta documentación **no se le llama "cliente"** para evitar confusión con el comprador final (ver "Cliente" abajo). Cada tenant tiene su propio catálogo, inventario, pedidos, canales, proveedores y campañas, aislados de los demás tenants mediante `tenant_id` en el modelo de datos. "Tenant" y "negocio" son sinónimos intercambiables en el texto; "tenant" se prefiere en contexto técnico/arquitectónico, "negocio" en contexto de producto o de conversación con el emprendedor. Hoy existe un único tenant (el negocio piloto).
- **Cliente.** El comprador final: la persona que compra un producto en la tienda de un tenant, en cualquiera de sus canales (tienda propia, TikTok Shop, Mercado Libre). El cliente **no** tiene cuenta en el panel administrativo ni es el sujeto del modelo SaaS — es el usuario final de la tienda de un tenant. Nunca se usa "cliente" para referirse al tenant ni al emprendedor.
- **Emprendedor / vendedor.** La persona (o equipo) que opera un tenant: gestiona catálogo, inventario, pedidos, logística, proveedores y publicidad desde el panel administrativo. "Emprendedor" se usa en contexto de producto/negocio; "vendedor" es sinónimo aceptado, especialmente al hablar de su relación con un canal específico (ej. "el vendedor en Mercado Libre"). En el piloto, el emprendedor es el equipo que opera el negocio piloto; en el modelo SaaS, es cualquier usuario que crea un tenant en la plataforma.
- **Administrador de la plataforma (Platform Admin).** Persona del equipo de Proyecto Alfa (no de un tenant específico) que opera la plataforma SaaS a nivel global: da soporte técnico, gestiona incidencias multi-tenant, administra facturación de la plataforma. No debe confundirse con el emprendedor, que administra únicamente su propio tenant. Ver `actores.md`.
- **Canal.** Cada lugar donde un tenant pone en venta su catálogo y recibe pedidos: tienda propia (`apps/web`), TikTok Shop, Mercado Libre, y cualquier marketplace que se integre después. Un canal tiene su propio flujo de publicación de producto y su propia forma de recibir pedidos, pero comparte el mismo inventario del tenant a través del sistema unificado de sincronización (ver `vision-producto.md`).
- **SKU (Stock Keeping Unit).** Identificador único de una variante vendible de un producto dentro de un tenant (ej. "Camiseta cuello redondo, talla M, color azul" = un SKU; talla L del mismo diseño = otro SKU distinto). El inventario y la sincronización multicanal operan siempre a nivel de SKU, nunca a nivel de "producto" genérico cuando el producto tiene variantes.
- **Pedido.** Término canónico para la solicitud de compra de uno o más SKU hecha por un cliente en un canal, desde que se confirma hasta que se entrega o cancela. Un pedido pertenece siempre a un tenant y a un canal. **"Orden" es sinónimo no preferido**: se usa únicamente cuando se cita literalmente la terminología de un canal externo (ej. Mercado Libre expone el recurso `orders`/"órdenes" en su API); todo pedido proveniente de un canal externo se traduce a "pedido" en cuanto entra al sistema de Proyecto Alfa.
- **Transportadora.** Empresa externa de logística que realiza el transporte físico de un pedido desde el punto de despacho del tenant (o de un proveedor, en envíos directos) hasta el cliente (ej. Servientrega, Coordinadora, Interrapidísimo, en el mercado colombiano). El comparador de transportadoras compara costo, tiempo y cobertura entre varias transportadoras para un mismo pedido.
- **Proveedor.** Persona o empresa externa que abastece de mercancía o insumos a un tenant para que este pueda tener producto disponible para vender. No debe confundirse con "proveedor de servicio técnico" (ej. proveedor de IA, pasarela de pago) ni con la transportadora. El directorio de proveedores es un módulo que ayuda al emprendedor a encontrar y evaluar proveedores confiables.
- **Campaña.** Conjunto de anuncios pautados por un tenant en una plataforma de publicidad digital (Meta Ads, Google Ads, u otra que se integre después), con presupuesto, objetivo (ej. tráfico, conversión) y periodo definidos, gestionado desde el módulo de publicidad digital y medido en el dashboard de inteligencia comercial.

---

## Ejemplos

- "El **emprendedor** revisa en su panel los **pedidos** que llegaron hoy por sus tres **canales**: tienda propia, TikTok Shop y Mercado Libre." — no "el cliente revisa sus órdenes" (ambiguo: ¿qué cliente?).
- "Un **cliente** compró el **SKU** *Camiseta-Azul-M* en TikTok Shop; el inventario de ese SKU se descuenta también en la tienda propia porque ambos canales comparten el mismo stock del **tenant**." — aquí "cliente" es inequívocamente el comprador final.
- "El **administrador de la plataforma** atiende un ticket de un **emprendedor** que no puede conectar su **canal** de Mercado Libre." — distingue con claridad los dos roles administrativos distintos.
- "El sistema recibe una **orden** desde la API de Mercado Libre y la guarda internamente como un **pedido** más, indistinguible en el panel de un pedido nacido en la tienda propia."

---

## Casos límite

- **Un tenant que también es "cliente" de otro tenant** (ej. un emprendedor de Proyecto Alfa le compra a otro emprendedor de Proyecto Alfa en su tienda): en ese contexto puntual sí actúa como cliente, y el documento debe aclararlo explícitamente ("actuando como cliente de otro tenant") en vez de asumir que el lector lo infiere.
- **Pedido con SKUs de dos proveedores distintos:** sigue siendo un único pedido (una sola relación con el cliente y con la transportadora), aunque internamente involucre a más de un proveedor para el abastecimiento.
- **Canal que no es ni tienda propia ni marketplace tradicional** (ej. venta por WhatsApp o redes sociales sin checkout propio): mientras no genere un pedido rastreable en el sistema, no se considera "canal" en el sentido de este glosario — es un canal de *marketing*, no de venta, hasta que se integre formalmente.
- **"Proveedor" técnico vs. "proveedor" de negocio:** cuando se hable de un proveedor de servicios técnicos (proveedor de IA, pasarela de pago, proveedor de hosting), el documento debe calificarlo explícitamente (ej. "proveedor de IA") para no confundirlo con el proveedor de mercancía definido aquí.

---

## Decisiones futuras

- Si el volumen de integraciones lo justifica, definir un término canónico para "ítem de línea de pedido" (line item) cuando se documente el modelo de datos de pedidos en detalle.
- Definir si, en el modelo SaaS, el rol "emprendedor" se subdivide en sub-roles (ej. dueño del tenant vs. empleado con acceso limitado al panel) y cómo se nombra esa distinción sin invadir el término "administrador de la plataforma".
- Evaluar si se necesita un término propio para "cliente recurrente" o "cliente registrado" vs. "comprador ocasional sin cuenta", una vez se defina el alcance del portal de clientes mencionado como aspiracional en `vision-producto.md`.

---

## Referencias

- [`README.md`](../../README.md) — visión global del proyecto.
- [`docs/business/vision-producto.md`](vision-producto.md) — objetivos que motivan estos términos.
- [`docs/architecture/vision-tecnica.md`](../architecture/vision-tecnica.md) — decisión de multi-tenant desde el modelo de datos que sustenta la definición de "tenant".
- `docs/business/actores.md` — desarrolla en detalle los roles definidos aquí (emprendedor, cliente, administrador de la plataforma, proveedor, transportadora, soporte).
- `docs/architecture/base-de-datos.md` (en construcción) — debe usar estos términos como nombres de entidades.

---

## Historial

- **2026-07-27** — Primera versión.
