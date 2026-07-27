# Investigación: Proveedores de IA para Automatización

## Propósito

Comparar, a alto nivel y sin comprometerse a uno solo, los proveedores de modelos de IA razonables para automatizar tareas de Proyecto Alfa: generación de descripciones de producto, respuesta a preguntas frecuentes, y generación de imágenes/contenido — como insumo para la decisión futura (aún pendiente según `vision-producto.md`) de qué proveedor de IA usar.

---

## Objetivo

Responder, con evidencia y no supuestos:

1. ¿Qué proveedores de IA de texto (LLM) son viables hoy para generar descripciones de producto y responder preguntas frecuentes?
2. ¿Qué proveedores de generación de imágenes existen y qué tan caro/maduro es cada uno?
3. ¿Cuáles son los órdenes de magnitud de costo (pricing) de cada opción, para poder estimar el costo operativo de automatizar con IA a escala?
4. ¿Qué falta resolver (según la arquitectura de Proyecto Alfa) antes de comprometerse a un proveedor único?

---

## Alcance

**Incluye:** comparación a alto nivel de Claude (Anthropic), GPT (OpenAI) y Gemini (Google) para generación de texto e imágenes, con datos de pricing y capacidades vigentes a julio de 2026.

**No incluye:** una recomendación final de proveedor único (`vision-producto.md` deja esto explícitamente como "decisión futura"), ni el diseño del prompt/pipeline de generación de contenido, ni evaluación de modelos open-source auto-hospedados (fuera del alcance de esta primera investigación).

---

## Problema que resuelve

`vision-producto.md` señala la IA como "eje transversal" del producto y lista como decisión futura pendiente: "proveedor de IA para automatización (modelo propio vs. proveedor externo tipo Claude/OpenAI) y hasta dónde llega la automatización sin supervisión humana." No se puede evaluar esa decisión sin comparar qué ofrece cada proveedor externo hoy, a qué costo, y con qué capacidades reales. Este documento arma esa comparación.

---

## Principios

- **Los tres proveedores mayores (Anthropic, OpenAI, Google) resuelven texto de forma comparable; la diferencia real está en imágenes y en el modelo de costos.** Los tres exponen modelos de lenguaje capaces de generar descripciones de producto y responder preguntas frecuentes con calidad suficiente para un caso de uso de e-commerce — la elección no se decide solo por calidad de texto, sino por costo, límites de uso, e integración con el resto del stack.
- **Claude no genera imágenes nativamente; GPT y Gemini sí.** Anthropic no ofrece generación de imágenes en su API (solo visión: leer/analizar imágenes). Si Proyecto Alfa quiere automatizar generación de imágenes de producto (ej. fondos, variaciones, contenido de marketing) con un solo proveedor, GPT (vía GPT Image) o Gemini (vía Imagen / "Nano Banana") son las opciones directas; usar Claude para texto implicaría combinar proveedores.
- **El pricing de texto se mide en costo por millón de tokens y varía por tamaño de modelo.** Todos los proveedores ofrecen un espectro de modelos "chicos/baratos" a "grandes/caros" (ej. Claude Haiku vs. Opus, Gemini Flash-Lite vs. Pro, y el equivalente en GPT), lo que permite optimizar costo según la criticidad de la tarea: un modelo barato puede bastar para responder preguntas frecuentes repetitivas, mientras uno más caro se reserva para generación de contenido de marca que requiere más calidad.
- **El pricing de imágenes se mide por imagen generada, no por token, y varía mucho según resolución/calidad.** GPT Image y los modelos de Google (Imagen 4, Gemini/"Nano Banana") cobran por imagen, con un rango amplio según la calidad/resolución solicitada — relevante porque el volumen de imágenes de producto en un catálogo de e-commerce puede ser alto, y el costo se acumula por unidad, no se diluye como el texto.
- **Optimización de costo de texto vía caching y batch processing.** Anthropic (y en general la industria) ofrece mecanismos de prompt caching (grandes ahorros cuando se repite contexto, por ejemplo el mismo catálogo de productos en cada llamada) y procesamiento por lotes (batch, con descuento) — relevante si Proyecto Alfa genera contenido para muchos productos de forma masiva en vez de uno a uno en tiempo real.
- **No conviene atarse a un solo proveedor desde el diseño.** Dado que los tres proveedores compiten activamente en precio y capacidades (los tres bajaron precios o lanzaron modelos nuevos en el último año según la evidencia), el módulo de IA de Proyecto Alfa debería diseñarse con una capa de abstracción que permita cambiar de proveedor sin reescribir el resto del sistema — coherente con el principio general de "módulos desacoplados" de `vision-producto.md`.

---

## Reglas

- El módulo de automatización con IA de Proyecto Alfa debe diseñarse detrás de una interfaz propia (ej. un servicio interno "GeneradorDeContenido" o similar) que abstraiga el proveedor subyacente, para poder cambiar o combinar proveedores sin reescribir los módulos que consumen IA (descripciones, FAQ, imágenes).
- Toda automatización de IA que genere contenido publicado directamente al cliente (descripciones de producto, respuestas a preguntas) debe pasar por un paso de revisión o aprobación humana en el MVP piloto, hasta que se documente explícitamente lo contrario como decisión de producto — en línea con la pregunta abierta en `vision-producto.md` sobre "hasta dónde llega la automatización sin supervisión humana".
- Si se automatiza generación de imágenes, el costo por imagen generada debe estimarse contra el volumen real de SKUs del negocio piloto antes de habilitarlo en producción, dado que el costo escala por unidad y no se diluye como el de texto.
- Cualquier tarea de bajo riesgo y alto volumen (ej. respuestas a preguntas frecuentes repetitivas) debe evaluarse primero contra el modelo más económico disponible del proveedor elegido, reservando modelos más caros para tareas de mayor impacto en marca (ej. descripciones de producto que el cliente lee antes de comprar).

---

## Ejemplos

- **Generación de descripción de producto:** a partir de una foto y datos básicos (nombre, categoría, atributos), un modelo con capacidad de visión (Claude, GPT o Gemini, los tres la tienen) genera una descripción de producto lista para publicar en la tienda y en los marketplaces conectados — el ejemplo ya descrito en `vision-producto.md`.
- **Respuesta a preguntas frecuentes:** un modelo económico (ej. Claude Haiku, Gemini Flash-Lite o el equivalente de GPT) responde preguntas repetitivas de compradores (ej. en Mercado Libre, ver `docs/research/mercado-libre.md`) usando el catálogo de producto como contexto, con caching del contexto repetido para abaratar el costo por respuesta.
- **Generación de imagen de producto:** usando GPT Image o Imagen 4 de Google, se genera una variación de fondo o una imagen de marketing a partir de una foto base del producto — Claude no cubre este caso de uso de forma nativa.

---

## Casos límite

- **Pricing cambia con frecuencia y varía por región/moneda**: los precios de referencia relevados (ej. Claude Sonnet con precio introductorio válido hasta agosto de 2026, o el retiro de DALL-E 2/3 de la API de OpenAI en mayo de 2026) muestran que este mercado se mueve rápido — cualquier estimación de costo operativo de IA debe revalidarse contra la documentación oficial vigente en el momento de implementar, no asumirse fija desde esta investigación.
- **Claude no genera imágenes**: si Proyecto Alfa elige Claude como proveedor principal de texto por calidad/costo, necesitará un segundo proveedor (GPT o Gemini) solo para imágenes — implica un diseño multi-proveedor desde el inicio si se quiere cubrir ambos casos de uso.
- **El costo de imágenes varía mucho por calidad/resolución** (en el caso de GPT Image, de fracciones de centavo a ~20 centavos de dólar por imagen según la investigación) — un catálogo de miles de SKUs con múltiples variaciones de imagen puede generar un costo operativo no trivial si no se controla el tier de calidad usado.
- **Los modelos "grandes" (Opus, Pro, GPT de gama alta) son sensiblemente más caros por token que los "chicos"** — usar el modelo más potente para todas las tareas de IA sin diferenciación es una forma fácil de sobrecostar la operación.

---

## Decisiones futuras

- Elegir el/los proveedor(es) de IA definitivo(s) para producción — explícitamente pendiente en `vision-producto.md`.
- Definir el nivel de supervisión humana requerido antes de publicar contenido generado por IA (aprobación obligatoria vs. publicación automática con revisión posterior).
- Definir presupuesto operativo mensual de IA por tenant, una vez el modelo SaaS esté más definido, para dimensionar qué tareas se automatizan con qué tier de modelo.
- Evaluar si conviene una capa de abstracción tipo "router" que elija automáticamente el proveedor/modelo según la tarea (costo vs. calidad), en vez de fijar un proveedor único por módulo.

---

## Referencias

- [Models overview - Claude Platform Docs](https://platform.claude.com/docs/en/about-claude/models/overview)
- [Anthropic API Pricing in 2026: Complete Guide — Finout](https://www.finout.io/blog/anthropic-api-pricing)
- [Claude AI overview 2026: Models, pricing, and key limitations | eesel AI](https://www.eesel.ai/blog/claude-overview)
- [OpenAI Pricing in 2026 for Individuals, Orgs & Developers - Finout](https://www.finout.io/blog/openai-pricing-in-2026)
- [OpenAI Image Generation API Pricing in 2026: GPT Image 1.5 and Mini](https://www.aifreeapi.com/en/posts/openai-image-generation-api-pricing)
- [DALL-E API Pricing 2026 - TokenMix Blog](https://tokenmix.ai/blog/dall-e-api-pricing)
- [Gemini Developer API pricing | Gemini API | Google AI for Developers](https://ai.google.dev/gemini-api/docs/pricing)
- [Gemini API Pricing Guide 2026: Flash, Pro, and Vertex AI | Curlscape](https://curlscape.com/blog/google-gemini-api-pricing-guide-2026)

---

## Historial

- **2026-07-27** — Primera versión, basada en investigación web.
