# Investigación: Competidores y Alternativas

## Propósito

Documentar 2-4 competidores/alternativas reales que un emprendedor de comercio electrónico en Colombia/Latinoamérica podría elegir en vez de Proyecto Alfa, y qué diferencia (o debería diferenciar) a Proyecto Alfa de cada uno, para orientar decisiones de producto y priorización.

---

## Objetivo

Responder, con evidencia y no supuestos:

1. ¿Qué plataformas usa hoy realmente un emprendedor de e-commerce en Colombia/LatAm?
2. ¿Qué modelo de negocio (precio, comisión) tiene cada una?
3. ¿Qué le falta a cada alternativa que Proyecto Alfa podría cubrir (según su visión de producto: multicanal centralizado, logística comparada, IA transversal)?
4. ¿Dónde es Proyecto Alfa estructuralmente más débil frente a estos competidores (para no subestimar el reto)?

---

## Alcance

**Incluye:** Shopify, Tiendanube/Nuvemshop, WooCommerce y VTEX — cuatro alternativas reales con presencia confirmada en Colombia/Latinoamérica, cubriendo el espectro desde "plataforma SaaS global" hasta "open source auto-hospedado" y "enterprise regional".

**No incluye:** un análisis financiero de viabilidad de negocio de Proyecto Alfa (eso es `docs/business/`), ni la investigación detallada de cada plataforma (Shopify ya tiene su propio documento en `docs/research/shopify.md`, referenciado aquí, no repetido).

---

## Problema que resuelve

Proyecto Alfa se define a sí mismo como una plataforma que centraliza gestión, logística, multicanal e IA para emprendedores (`vision-producto.md`). Esa propuesta de valor solo es real si se compara contra lo que un emprendedor puede conseguir hoy con alternativas ya maduras. Sin esta comparación, el equipo corre el riesgo de construir funcionalidades que la competencia ya resuelve razonablemente bien, o de subestimar dónde la competencia es fuerte y Proyecto Alfa, como proyecto nuevo, parte en desventaja.

---

## Principios

- **El espectro de competidores no es uniforme: hay SaaS puro, open source y enterprise.** Shopify (SaaS global, sin necesidad de infraestructura propia), Tiendanube/Nuvemshop (SaaS regional, más asequible, enfocado en LatAm), WooCommerce (open source, gratis pero requiere gestión técnica propia) y VTEX (enterprise, para empresas medianas/grandes con operación compleja) cubren segmentos de mercado distintos — Proyecto Alfa debe decidir explícitamente en cuál compite primero (todo apunta, por el piloto, al segmento de Tiendanube: emprendedor pequeño/mediano en LatAm).
- **Tiendanube/Nuvemshop es el competidor más directo geográfica y funcionalmente.** Es la plataforma de e-commerce líder en Latinoamérica (fundada en 2011, con más de 180,000 tiendas activas en Argentina, México, Colombia, Chile y Brasil), con desembarco confirmado en Colombia y Chile a fines de 2024 — el mismo mercado inicial de Proyecto Alfa. Su modelo de precios (planes desde ~24,900 COP/mes en Colombia, sin comisión por transacción en los planes) es la referencia de precio más cercana a la que Proyecto Alfa se enfrentará directamente.
- **WooCommerce compite por ser gratis, no por ser mejor gestionado.** Con aproximadamente 66% de cuota de mercado entre plataformas de e-commerce y más de tres millones de sitios activos, WooCommerce gana por volumen porque es gratuito y corre sobre WordPress — pero traslada toda la complejidad de hosting, seguridad, logística e integraciones al emprendedor o a quien lo implemente. Ahí es exactamente donde la propuesta de Proyecto Alfa (todo centralizado, con IA) tiene una ventaja de producto clara si logra mantenerse simple de usar.
- **VTEX define el techo del mercado, no el piso.** Es una plataforma enterprise brasileña con presencia en 38 países y miles de tiendas activas, pensada para medianas/grandes empresas con operación omnicanal compleja — no es competencia directa del emprendedor individual que es el foco actual de Proyecto Alfa, pero marca el techo hacia el que Proyecto Alfa podría crecer si su modelo SaaS escala (Fase 5 del roadmap).
- **Ningún competidor investigado combina, de fábrica, comparador de transportadoras + directorio de proveedores + IA transversal + multicanal centralizado en un solo panel**, tal como lo define el objetivo específico de Proyecto Alfa — cada uno resuelve una parte (Shopify vía App Store de terceros, Tiendanube con sus propias integraciones regionales, WooCommerce vía plugins, VTEX vía módulos enterprise). La oportunidad de diferenciación de Proyecto Alfa está en la integración nativa de esas piezas para el segmento de emprendedor pequeño/mediano en LatAm, no en inventar una funcionalidad que nadie más tiene.

---

## Reglas

- Toda funcionalidad nueva propuesta para Proyecto Alfa debe evaluarse primero contra si Tiendanube/Nuvemshop (el competidor de segmento y geografía más directa) ya la resuelve de fábrica, para no reinventar lo que el mercado ya considera estándar mínimo.
- El pricing de Proyecto Alfa en su fase SaaS (aún no definido, ver `vision-producto.md`) debe evaluarse con Tiendanube Colombia como referencia de piso de mercado (planes desde ~24,900 COP/mes sin comisión por transacción), no con Shopify (mercado global, precio de entrada más alto en USD).
- Si Proyecto Alfa decide abrir un ecosistema de integraciones de terceros en el futuro (aprendizaje de `docs/research/shopify.md`), debe considerar que WooCommerce y Shopify ya tienen ese modelo maduro y una base de desarrolladores establecida — competir ahí requeriría una estrategia de adopción explícita, no solo tener la capacidad técnica.
- La comparación contra VTEX no debe usarse para justificar alcance de producto en el MVP piloto (Fase 2); es una referencia de hacia dónde podría escalar Proyecto Alfa en fases posteriores, no un objetivo de corto plazo.

---

## Ejemplos

- **Un emprendedor colombiano evaluando plataformas hoy** probablemente compara Tiendanube (fácil de configurar, sin comisión, soporte en español, presencia local) contra WooCommerce (gratis pero requiere contratar a alguien que lo configure y mantenga) — Proyecto Alfa entra a esa misma decisión con la promesa adicional de logística comparada, multicanal centralizado e IA integrada desde el día uno.
- **Un emprendedor que ya vende en Mercado Libre y quiere una tienda propia** encuentra en Tiendanube integraciones ya construidas hacia marketplaces (mencionado también en la investigación de logística: Interrapidísimo ya se integra con Tiendanube) — es el tipo de integración que Proyecto Alfa debe igualar o superar en su fase multicanal (Fase 4).
- **Una empresa mediana que crece y necesita omnicanalidad compleja** eventualmente migra hacia VTEX — ese es el techo de mercado al que Proyecto Alfa podría aspirar únicamente si su modelo SaaS multi-tenant madura lo suficiente.

---

## Casos límite

- **Tiendanube no cobra comisión por transacción en sus planes base**, lo cual pone presión directa sobre cualquier modelo de monetización de Proyecto Alfa basado en comisión por venta (una de las opciones abiertas en "Decisiones futuras" de `vision-producto.md`) — si Proyecto Alfa cobra comisión y Tiendanube no, debe justificarse con valor adicional claro (logística, IA, multicanal) o el emprendedor lo percibirá como más caro sin razón evidente.
- **WooCommerce es gratis pero no "sin costo real"**: el emprendedor termina pagando hosting, plugins premium, mantenimiento y a veces un desarrollador — es un competidor que gana por percepción de gratuidad, no por costo total real más bajo. Proyecto Alfa debe comunicar bien su propuesta de valor frente a esta percepción, no solo comparar precios de lista.
- **La cifra de cuota de mercado de WooCommerce (66%) mide plataformas de e-commerce en general, no específicamente el segmento de emprendedores pequeños en Colombia** — debe tomarse como una señal de tamaño de ecosistema, no como participación exacta en el mercado objetivo de Proyecto Alfa.
- **Ninguna de estas cuatro plataformas fue evaluada aquí con acceso a datos internos o de uso real en Colombia** (solo información pública de marketing/documentación) — antes de tomar decisiones de pricing o de alcance de producto basadas en este documento, conviene validar con entrevistas reales a emprendedores del segmento objetivo, como ya indica el principio "el piloto manda" de `vision-producto.md`.

---

## Decisiones futuras

- Confirmar contra qué competidor(es) se posiciona explícitamente Proyecto Alfa en su mensaje de producto/marketing cuando llegue a fase de comercialización (Fase 5).
- Definir el modelo de pricing SaaS de Proyecto Alfa considerando el piso de precio ya establecido por Tiendanube en Colombia.
- Evaluar si conviene investigar competidores adicionales no cubiertos aquí (ej. Jumpseller, Wix eCommerce, PrestaShop) si en el futuro se identifican como relevantes para el segmento objetivo.
- Decidir si Proyecto Alfa compite explícitamente por integraciones de marketplace ya resueltas por Tiendanube (ej. con Interrapidísimo) o si construye las propias desde cero.

---

## Referencias

- [Mejores tiendas online colombianas creadas con Tiendanube 2026](https://www.tiendanube.com/blog/tiendas-online-colombianas-creadas-con-tiendanube/)
- [Planes y precios de Tiendanube](https://www.tiendanube.com/planes-y-precios)
- [Compañía | Conocé la historia de Tiendanube](https://www.tiendanube.com/compania)
- [Tiendanube: costos, planes y comisiones para tu marca (2026)](https://www.tiendanube.com/blog/tiendanube-costos/)
- [WordPress Ecommerce Plugin - WooCommerce](https://woocommerce.com/wordpress-ecommerce/)
- [About WooCommerce - WooCommerce.com](https://woocommerce.com/about/)
- [VTEX: la plataforma de comercio electrónico líder en Latinoamérica — Sparklabs](https://sparklabs.com.mx/en/vtex-la-plataforma-de-comercio-electronico-lider-en-latinoamerica/)
- [VTEX: ¿qué es y qué diferencias tiene con Tiendanube? — Tiendanube](https://www.tiendanube.com/blog/vtex-o-tiendanube/)
- [Logística eCommerce — Inter Rapidísimo (integración con Tiendanube, WooCommerce, etc.)](https://interrapidisimo.com/logistica-ecommerce/)
- Ver también `docs/research/shopify.md` para el detalle de Shopify como competidor/referencia.

---

## Historial

- **2026-07-27** — Primera versión, basada en investigación web.
