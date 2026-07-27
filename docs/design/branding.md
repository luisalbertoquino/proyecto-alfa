# Branding

## Propósito

Definir la identidad de marca del negocio piloto de Proyecto Alfa — tono, personalidad y valores que debe transmitir en `apps/web` — y fijar cómo esa identidad se implementa de forma que no quede "quemada" en el sistema de diseño: un futuro tenant del SaaS debe poder aplicar la suya propia sobre la misma base de componentes sin que nadie tenga que reescribir código.

---

## Objetivo

Que cualquier pantalla nueva de la tienda transmita, sin necesidad de instrucción adicional, la misma personalidad de marca — y que esa personalidad viva en tokens configurables (`docs/design/design-system.md`), nunca en valores fijos dentro de un componente de `packages/ui`.

---

## Alcance

**Incluye:**

- Tono de voz, personalidad y valores de marca del negocio piloto, aplicados a `apps/web` (superficie orientada a cliente final).
- Principio de theming: por qué la identidad de marca se implementa como capa de tokens sobre `packages/ui`, y no como estilos particulares de cada componente.
- Qué de la identidad del piloto es específico de ese negocio y qué es un valor genérico que cualquier tienda construida sobre Proyecto Alfa compartiría (ej. claridad, honestidad en precios).

**No incluye** (vive en otros documentos):

- Principios generales de UX que anteceden a la marca → `docs/design/ux-principles.md`.
- Valores concretos de tokens de color/tipografía y arquitectura técnica de theming → `docs/design/design-system.md`.
- Identidad visual de `apps/admin`: el panel operativo usa el mismo sistema de tokens pero no está diseñado para proyectar "marca" al usuario final — sus decisiones de tono son de `ux-principles.md` (eficiencia, densidad), no de este documento.
- Logo, naming legal y assets gráficos definitivos del negocio piloto — pendientes, ver Decisiones futuras.

---

## Problema que resuelve

Sin un documento de marca explícito, dos riesgos aparecen en paralelo:

- **Inconsistencia de tono:** cada pantalla nueva de la tienda "suena" distinto (formal en una, informal en otra; alarmista en los mensajes de error, cercana en la portada) porque nadie tiene un criterio escrito de cómo debe sonar la marca.
- **Marca hardcodeada:** si el tono, los colores o las decisiones visuales de la marca del piloto se escriben directamente en los componentes de `packages/ui` (en vez de vivir en tokens), el día que exista un segundo tenant del SaaS con una marca distinta, cambiar de marca implicará reescribir componentes en vez de simplemente cambiar un conjunto de valores — exactamente el mismo error que `vision-tecnica.md` previene para los datos con `tenant_id` desde el inicio, aplicado ahora al diseño visual.

---

## Principios

1. **La marca del piloto es real y concreta hoy; el sistema que la implementa no depende de ella.** Se diseña con valores de marca definidos (ver Reglas), pero cada valor de marca entra al sistema como un token sustituible, nunca como una constante en un componente.
2. **Personalidad de marca, no decoración de marca.** El tono de la marca se expresa sobre todo en el copy (cómo se escribe un mensaje de confirmación, un error, una descripción) y en decisiones de jerarquía visual — no depende de un color específico. Esto es lo que permite que la misma personalidad "clara y directa" sobreviva a un cambio de paleta el día que un tenant use otra.
3. **Confianza antes que personalidad.** Ante cualquier tensión entre "sonar más a la marca" y "ser claro/directo/confiable", gana la claridad — coherente con el principio de confianza de `ux-principles.md`. La marca del piloto se construye alrededor de ser una tienda seria y confiable en un mercado donde comprar en línea todavía genera dudas, no alrededor de ser la más llamativa.
4. **Un tenant del SaaS no hereda la marca del piloto — hereda el sistema.** Lo que un futuro tenant reutiliza es la arquitectura de tokens, los componentes y los principios de `ux-principles.md`; no el logo, los colores ni el tono de voz del negocio piloto, que son de ese negocio específico.
5. **La marca se documenta en tokens versionados, no en la memoria de quien la diseñó.** Cualquier valor de marca (color primario, tipografía, tono de voz) tiene una única fuente de verdad escrita — este documento para tono/valores, `design-system.md` para tokens técnicos — y un cambio de marca es un cambio a esa fuente, no una decisión implícita al construir una pantalla nueva.

---

## Reglas

- **Personalidad de marca del piloto** (provisional, ver Decisiones futuras para el proceso de validación formal con negocio):
  - **Cercana, no informal:** se dirige al comprador de tú a tú, en español neutro colombiano, sin tecnicismos ni jerga corporativa, pero sin perder seriedad — nunca usa humor que pueda leerse como falta de profesionalismo en un flujo de pago.
  - **Directa:** dice el precio total, el tiempo de envío y las condiciones sin letra pequeña ni ambigüedad. Nunca usa técnicas de presión artificial (temporizadores falsos, "solo quedan 2" sin que sea cierto).
  - **Confiable:** cada mensaje que involucre dinero, envío o datos personales se redacta para reducir ansiedad, no para generar urgencia.
  - **Práctica:** prioriza que el visitante encuentre y compre rápido sobre construir una experiencia "de autor" — coherente con el principio de velocidad de `ux-principles.md`.
- Ningún componente de `packages/ui` referencia un color, tipografía o texto de marca por valor fijo (hex, nombre de fuente literal); toda referencia de marca pasa por un token (`--color-brand-*`, `--font-brand-*`, etc. — definidos en `design-system.md`).
- El tono de voz se aplica de forma distinta según el punto de contacto: marketing/portada puede ser más expresivo dentro del tono cercano-directo; mensajes transaccionales (confirmación de pedido, error de pago) priorizan claridad absoluta sobre personalidad.
- `apps/admin` no proyecta la personalidad de marca del piloto hacia el usuario final — su tono es neutro y funcional (ver `ux-principles.md`, principio 1); usa los mismos tokens técnicos pero no el tono de voz de cliente.
- Todo texto de interfaz nuevo en `apps/web` se revisa contra la personalidad de marca antes de publicarse, igual que se revisa contra accesibilidad o performance — no es un paso opcional de "pulido".

---

## Ejemplos

- Mensaje de error de pago rechazado: **"No pudimos procesar tu pago. Verifica los datos de tu tarjeta o intenta con otro medio de pago."** — directo, sin culpar al usuario, sin tecnicismo ("error de gateway"), sin alarmismo.
- Confirmación de pedido: **"¡Listo! Tu pedido #1234 está confirmado. Te escribimos cuando salga hacia tu ciudad."** — cercano, concreto, marca el siguiente paso.
- Lo que la marca del piloto **no** haría: "¡ÚLTIMAS UNIDADES! ¡Compra YA antes de que se acaben!" con temporizador de cuenta regresiva si no refleja una escasez real — contradice el principio de confiabilidad.
- Theming por tokens en la práctica: el componente `Button` de `packages/ui` usa `background: var(--color-brand-600)`. Cambiar la marca del piloto a otra paleta, o dar de alta un tenant con paleta propia, es cambiar el valor de `--color-brand-600` en la capa de tema — el componente `Button` no se toca.

---

## Casos límite

- **Un futuro tenant define una paleta que no cumple el contraste mínimo de accesibilidad** (ver `ui-guidelines.md`): el sistema de theming necesita una validación (automática o manual) antes de publicar esa paleta — mecanismo aún no diseñado, ver Decisiones futuras.
- **El negocio piloto cambia de nombre o identidad visual antes del MVP:** al vivir la marca en tokens, el costo de ese cambio debería ser actualizar valores de marca y assets, no reescribir componentes; este documento se actualiza como fuente de verdad del nuevo tono si eso ocurre.
- **Tenant sin logo o sin assets de marca propios al darse de alta** (escenario SaaS futuro): el sistema necesita un estado de marca por defecto (genérico, neutro, no el del piloto) que permita operar una tienda mínimamente presentable antes de que el tenant suba sus propios assets.
- **Tensión entre tono de marketing y tono transaccional en la misma pantalla** (ej. una página de producto con copy persuasivo que también debe mostrar condiciones de envío exactas): la parte transaccional siempre prioriza claridad sobre personalidad, aunque conviva en la misma pantalla con copy de marketing.

---

## Decisiones futuras

- Nombre comercial definitivo, logo y paleta de marca del negocio piloto — hoy no están fijados en ningún documento del repositorio; los valores de tono de este documento son la base de trabajo hasta que negocio los valide formalmente.
- Manual de marca completo (logo en sus variantes, uso incorrecto, tipografía de marca si difiere de la tipografía de interfaz) — hoy vive solo el tono de voz, falta la identidad gráfica formal.
- Proceso y responsable de validar/aprobar la paleta de un nuevo tenant del SaaS (incluye el mecanismo de validación de accesibilidad mencionado en Casos límite).
- Nivel de personalización de marca que un tenant podrá aplicar en el futuro (solo color y logo, vs. tipografía, vs. layout) — hoy es una intención de arquitectura (tokens en vez de valores fijos), no un alcance de producto comprometido.

---

## Referencias

- [`docs/design/ux-principles.md`](ux-principles.md) — principios de UX de los que se deriva el criterio "confianza antes que personalidad".
- [`docs/design/design-system.md`](design-system.md) — implementación técnica de los tokens de marca y arquitectura de theming multi-tenant.
- [`docs/business/vision-producto.md`](../business/vision-producto.md) — visión SaaS multi-tenant que motiva que la marca no esté hardcodeada.
- [`resources/`](../../resources/) — carpeta destinada a logos, mockups e íconos (pendiente de contenido).

---

## Historial

- **2026-07-27** — Primera versión.
