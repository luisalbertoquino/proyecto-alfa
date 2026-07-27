# Guías de Interfaz

## Propósito

Fijar las guías concretas y verificables de interfaz que todo componente de `packages/ui` y toda pantalla de `apps/web`/`apps/admin` debe cumplir: estados de componente, accesibilidad, comportamiento responsive y rendimiento de imágenes. Es el nivel más técnico y operativo del diseño — implementa `docs/design/ux-principles.md` (accesibilidad y rendimiento como requisitos, no extras) usando los tokens definidos en `docs/design/design-system.md`.

---

## Objetivo

Que cualquier persona que construya un componente o una pantalla tenga una checklist objetiva — no una impresión subjetiva — para saber si esa pieza cumple el estándar del proyecto en accesibilidad, comportamiento y rendimiento, antes de integrarla.

---

## Alcance

**Incluye:**

- Los estados obligatorios de un componente interactivo y qué debe comunicar cada uno.
- Reglas de accesibilidad técnica: contraste, navegación por teclado, foco visible, semántica.
- Estrategia responsive/mobile-first y breakpoints de uso.
- Reglas de rendimiento de imágenes, por su peso crítico en `apps/web`.

**No incluye** (vive en otros documentos):

- Por qué importa la accesibilidad/rendimiento (ya son principios, no se vuelven a justificar aquí) → `docs/design/ux-principles.md`.
- Los valores de los tokens que estas guías consumen (colores, espaciado, breakpoints como escala) → `docs/design/design-system.md`.
- Tono de los mensajes de error o de confirmación → `docs/design/branding.md`.

---

## Problema que resuelve

Sin guías explícitas y verificables, "accesible" y "rápido" se vuelven opiniones en vez de estándares: cada persona decide a su criterio si un contraste "se ve bien", si un botón "responde rápido", si un formulario "es usable en el celular". El resultado, en un proyecto que va a crecer a muchas páginas y muchos componentes, es una interfaz inconsistente donde algunas pantallas son accesibles y rápidas por buena suerte, y otras no, sin que nadie pueda decir con certeza cuáles.

Esto es particularmente costoso en `apps/web`: una tienda pública que vive de tráfico de buscadores y de conversión no puede permitirse imágenes pesadas, layout inestable o formularios inoperables por teclado — cada uno de esos problemas es, directamente, ventas perdidas.

---

## Principios

1. **Todo componente interactivo tiene sus seis estados definidos antes de darse por terminado**, no solo el estado feliz por defecto.
2. **La accesibilidad se verifica, no se asume.** Contraste, foco y navegación por teclado son requisitos con un valor objetivo (ratio de contraste, orden de tabulación), no una sensación de "se ve usable".
3. **Mobile-first en `apps/web`, con adaptación consciente en `apps/admin`.** La tienda se diseña primero para la pantalla más pequeña y se expande; el admin se diseña primero para la densidad de escritorio y se adapta a pantallas menores para las tareas críticas (ver `ux-principles.md`, Casos límite).
4. **Ninguna imagen se sirve sin optimizar en `apps/web`.** El peso de imagen es, en una tienda de e-commerce, la causa más común y más evitable de carga lenta.
5. **El layout no debe moverse mientras carga.** Todo elemento que carga de forma asíncrona (imagen, dato remoto) reserva su espacio desde el primer render para no desplazar el contenido alrededor (estabilidad visual).

---

## Reglas

### Estados de componente

Todo componente interactivo de `packages/ui` (botón, input, select, tarjeta clicable, etc.) contempla estos estados, con una diferencia perceptible entre ellos que no dependa únicamente del color:

| Estado | Qué debe comunicar |
|---|---|
| **Default** | Apariencia base, afordancia clara de que es interactivo si aplica. |
| **Hover** | Solo en dispositivos con puntero; nunca es el único indicador de interactividad (los táctiles no tienen hover). |
| **Focus** | Anillo de foco visible (ver Accesibilidad) — obligatorio, nunca se suprime sin reemplazo. |
| **Active/Pressed** | Feedback inmediato de que la interacción fue recibida (ej. mientras se mantiene presionado). |
| **Disabled** | Visualmente distinguible sin depender solo de opacidad; no es foco-navegable; comunica (por `aria-disabled` o texto asociado) por qué está deshabilitado si no es evidente. |
| **Loading** | Estado explícito con indicador (spinner/skeleton), nunca un botón que simplemente deja de responder; si la acción tarda más de ~1s se comunica progreso o al menos persistencia del estado de carga. |
| **Error** | Mensaje específico y accionable asociado al campo o componente (no solo un borde rojo); anunciado a tecnología asistiva (ver Accesibilidad). |
| **Empty** (para contenedores de datos: tablas, listas, resultados de búsqueda) | Mensaje claro de que no hay datos, con una acción siguiente cuando aplique (ej. "Aún no tienes pedidos" + enlace relevante), nunca una tabla en blanco sin explicación. |

### Accesibilidad

- **Contraste mínimo:** 4.5:1 para texto normal, 3:1 para texto grande (≥18px regular o ≥14px bold) y para elementos gráficos/UI no decorativos (bordes de input, iconos funcionales) — línea base WCAG 2.1 AA. Todo token de color semántico de `design-system.md` que se use para texto sobre un fondo se valida contra este mínimo antes de aceptarse.
- **Foco visible siempre.** Ningún componente ni pantalla usa `outline: none` (o equivalente) sin sustituir por un indicador de foco igual o más visible. El anillo de foco tiene su propio token (`--focus-ring`) y contraste suficiente contra el fondo en el que aparece.
- **Navegación completa por teclado.** Todo flujo, sin excepción — incluido el checkout completo de `apps/web` — debe poder completarse solo con teclado: orden de tabulación lógico (sigue el orden visual/de lectura), sin trampas de foco, con `Escape` cerrando overlays (modal, dropdown) y devolviendo el foco a quien lo abrió.
- **Semántica antes que ARIA.** Se usa el elemento HTML nativo correcto primero (`button`, `a`, `label`, `table`) y ARIA solo para lo que la semántica nativa no cubre. Todo campo de formulario tiene un `label` asociado (visible o, si el diseño lo omite visualmente, presente para tecnología asistiva) — nunca solo un `placeholder` como sustituto de etiqueta.
- **Errores de formulario anunciados y ubicados.** Un error de validación se asocia al campo con `aria-describedby`, se anuncia a lectores de pantalla, y al fallar el envío el foco se mueve al primer campo con error.
- **El color nunca es el único portador de significado.** Un estado de error, éxito o advertencia se comunica también con texto o ícono, no solo con un cambio de color (relevante para daltonismo, ~8% de hombres en la población general).
- **Objetivo mínimo de toque:** 44×44px para cualquier elemento interactivo en contextos táctiles — crítico en `apps/web`, donde la mayoría del tráfico es móvil.

### Responsive / mobile-first

- **`apps/web` se diseña mobile-first de forma literal:** el CSS base (sin media query) define la experiencia de la pantalla más pequeña; los breakpoints (`sm 640 · md 768 · lg 1024 · xl 1280 · 2xl 1536`, definidos en `design-system.md`) añaden progresivamente, nunca se usa un enfoque desktop-first con overrides hacia abajo.
- **`apps/admin` se diseña desktop-first para la densidad de datos**, con adaptación explícita para pantallas menores solo en las tareas marcadas como críticas para uso ocasional en móvil (ver `ux-principles.md`, Casos límite) — no se persigue paridad completa mobile/desktop en el panel.
- La zona de pulgar (thumb zone) se considera en `apps/web` en móvil: acciones primarias (agregar al carrito, continuar en checkout) se ubican donde el pulgar alcanza cómodamente en un teléfono sostenido con una mano, no arriba de la pantalla.
- Ninguna pantalla de `apps/web` depende de hover para revelar información o una acción necesaria para completar una tarea — en táctil no existe hover.
- Todo layout se prueba, como mínimo, en los anchos de referencia 360px (móvil de gama media común en el mercado objetivo), 768px (tablet) y 1280px (desktop) antes de darse por responsive.

### Rendimiento de imágenes

- Toda imagen de producto o de contenido en `apps/web` se sirve a través de un componente de imagen que genere tamaños responsivos (`srcset`) y formatos modernos (WebP/AVIF con fallback), nunca una etiqueta `<img>` con un único archivo de tamaño fijo.
- Toda imagen declara sus dimensiones (`width`/`height` o `aspect-ratio`) desde el primer render para reservar su espacio y evitar desplazamiento de layout mientras carga.
- Carga diferida (`lazy loading`) para toda imagen fuera de la primera pantalla visible; la imagen principal visible al cargar (ej. hero, primera imagen de producto) se prioriza para carga inmediata, no diferida.
- Las imágenes se sirven optimizadas y cacheadas a través del CDN (Cloudflare, según `docs/architecture/vision-tecnica.md`), no directamente desde el origen en cada request.
- Un producto sin imagen disponible muestra un placeholder de dimensiones fijas coherentes con el resto de la grilla — nunca un hueco vacío o un layout que colapsa.

---

## Ejemplos

- Un `Input` de `packages/ui` en estado `error` muestra borde rojo (`--color-danger`) **y** un texto de ayuda bajo el campo **y** un ícono, para no depender solo del color — y ese texto está enlazado al input vía `aria-describedby`.
- El botón "Agregar al carrito" en `apps/web` mantiene su tamaño y posición mientras pasa de `default` a `loading` (spinner interno) a `success` momentáneo — nunca cambia de tamaño ni de posición entre estados, para no mover el layout alrededor.
- La grilla de productos de la tienda reserva el espacio de cada imagen con `aspect-ratio` antes de que la imagen cargue, evitando que el texto y precio "salten" cuando la imagen aparece.
- La tabla de pedidos de `apps/admin` es completamente operable con teclado: `Tab` recorre las filas de forma lógica, `Enter` abre el detalle del pedido enfocado, `Escape` cierra el modal de detalle y devuelve el foco a la fila de origen.

---

## Casos límite

- **Carga de datos que tarda más de lo esperado** (ej. generación de una descripción con IA en el admin, o cálculo de envío en el checkout): el estado `loading` debe comunicar progreso o al menos seguir vivo visualmente (no "congelarse"); si supera un umbral razonable (a definir, ver Decisiones futuras) se ofrece la opción de cancelar o se informa la demora explícitamente.
- **Zoom del navegador al 200% o fuente del sistema aumentada por el usuario:** el layout no debe romperse ni recortar contenido — se usan unidades relativas (`rem`) para tipografía y espaciado, no píxeles fijos, precisamente para soportar este caso.
- **Conexión lenta en `apps/web`:** con imágenes diferidas y priorización de la imagen principal, la primera pantalla debe quedar utilizable (texto, precio, botón de compra) antes de que todas las imágenes terminen de cargar — degradación con gracia, coherente con `vision-tecnica.md`.
- **Formulario largo con múltiples errores de validación a la vez** (ej. checkout): todos los errores se muestran simultáneamente asociados a su campo, no uno a la vez; el foco va al primero, pero el resto queda visible sin que el usuario tenga que reenviar el formulario para descubrirlos.
- **Modo alto contraste del sistema operativo o extensión de accesibilidad del navegador:** los estados que dependen de sombras sutiles (ej. `--shadow-sm`) deben tener un respaldo (borde) que siga siendo perceptible cuando las sombras no se renderizan con suficiente contraste.

---

## Decisiones futuras

- Umbral exacto de tiempo (ej. 1s, 3s) a partir del cual un estado `loading` debe mostrar progreso explícito u opción de cancelar, en vez de solo un spinner indefinido.
- Herramienta de verificación automática de accesibilidad en CI (ej. `axe-core`, `eslint-plugin-jsx-a11y`) y el momento de incorporarla al pipeline (`docs/development/ci-cd.md`, aún en construcción).
- Presupuesto de rendimiento objetivo por tipo de página de `apps/web` (ej. peso máximo de imagen por página de producto, Core Web Vitals objetivo) — hoy la regla es cualitativa ("siempre optimizada"), falta el número acordado.
- Nivel de soporte de `apps/admin` en pantallas móviles más allá de las tareas críticas ya identificadas (ver `ux-principles.md`).
- Estrategia de imágenes generadas o subidas por un tenant del SaaS a futuro (validación de tamaño/formato en el momento de subida, no solo en el momento de servirlas).

---

## Referencias

- [`docs/design/ux-principles.md`](ux-principles.md) — principios que fijan accesibilidad y rendimiento como requisitos.
- [`docs/design/design-system.md`](design-system.md) — tokens (color, espaciado, breakpoints) que estas guías consumen.
- [`docs/design/branding.md`](branding.md) — tono de los mensajes de error/confirmación referidos en los ejemplos de estados.
- [`docs/architecture/vision-tecnica.md`](../architecture/vision-tecnica.md) — CDN/Cloudflare y degradación con gracia ante fallos, referenciados en rendimiento de imágenes y casos límite.

---

## Historial

- **2026-07-27** — Primera versión.
