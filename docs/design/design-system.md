# Design System

## Propósito

Definir el sistema de diseño de Proyecto Alfa: los tokens (color, tipografía, espaciado, radios, sombras), la arquitectura de theming multi-tenant, la estructura de `packages/ui` y cómo se documentan y prueban sus componentes. Es la implementación técnica de `docs/design/ux-principles.md` y `docs/design/branding.md` — si un token o componente contradice esos dos documentos, el token o componente está mal, no al revés.

---

## Objetivo

Que `apps/web` y `apps/admin` construyan cualquier pantalla nueva reutilizando un único inventario de tokens y componentes, sin duplicar decisiones visuales, sin que una superficie fuerce sus necesidades a la otra, y sin que la identidad visual del negocio piloto quede fija en el código de forma que un futuro tenant del SaaS no la pueda cambiar.

---

## Alcance

**Incluye:**

- Los tokens base del sistema (color, tipografía, espaciado, radios, sombras, breakpoints) y su arquitectura de tres capas (primitivos → semánticos → componente).
- El mecanismo de theming multi-tenant: cómo un tema se resuelve en tiempo de ejecución sin tocar el código de los componentes.
- La estructura de carpetas y capas de `packages/ui`.
- Cómo se documentan y prueban visualmente los componentes (incluida la decisión pendiente de Storybook).

**No incluye** (vive en otros documentos):

- Por qué se diseña así (principios de UX) → `docs/design/ux-principles.md`.
- Tono de voz y personalidad de marca → `docs/design/branding.md`.
- Estados de componente, accesibilidad técnica y reglas responsive detalladas → `docs/design/ui-guidelines.md`.
- Justificación de Next.js/Laravel como stack → `docs/architecture/vision-tecnica.md` y ADRs.

---

## Problema que resuelve

Sin un sistema de diseño formal y una arquitectura de tokens explícita, un proyecto con dos apps front-end y una visión SaaS multi-tenant termina, con alta probabilidad, en:

- **Divergencia visual entre `apps/web` y `apps/admin`**: cada equipo/feature define su propio espaciado, su propia paleta de grises, su propio radio de borde, porque no hay una fuente de verdad compartida — el resultado es una interfaz que no se siente construida por el mismo producto.
- **Colores y tipografías "quemados"**: valores de marca escritos directamente en componentes (`background: #16A34A` dentro de un `Button`) que obligan a tocar decenas de archivos el día que cambia la marca del piloto o que un tenant nuevo necesita la suya — el mismo problema que `vision-tecnica.md` resuelve para datos con `tenant_id`, aplicado a diseño.
- **Componentes sin un lugar único de verdad**: cada pantalla reimplementa su propio botón o su propia tabla con pequeñas variaciones, y arreglar un bug de accesibilidad o de estilo exige encontrar y corregir todas las copias.
- **Nadie sabe si un componente ya existe**: sin documentación visual navegable, es más rápido crear un componente duplicado que buscar si ya hay uno.

---

## Principios

1. **Tokens antes que valores.** Ningún componente de `packages/ui` contiene un color, tamaño de fuente, espaciado o radio como valor literal; todo pasa por un token. Esto es lo que permite el theming multi-tenant sin reescribir componentes (ver `branding.md`).
2. **Tres capas de tokens: primitivos → semánticos → componente.** Los primitivos son la paleta completa sin significado de negocio (`--color-green-600`); los semánticos les dan intención (`--color-brand-primary`, `--color-danger`); los de componente son específicos de un componente si hace falta (`--button-radius`). Un tema (el del piloto, o el de un tenant futuro) se implementa remapeando la capa semántica sobre los mismos primitivos o sobre primitivos propios — los componentes solo conocen la capa semántica.
3. **Un componente, variantes por superficie — no un fork por superficie.** Cuando `apps/web` y `apps/admin` necesitan comportamientos visuales distintos del "mismo" componente (una tabla densa en admin, una lista de productos espaciosa en la tienda), se resuelve con props/variantes dentro de un único componente en `packages/ui`, nunca duplicando el componente en cada app.
4. **El sistema sirve a ambas apps; ninguna lo captura.** Ningún cambio a `packages/ui` se acepta si mejora `apps/web` a costa de romper un caso de uso real de `apps/admin`, o viceversa. Si una necesidad es exclusiva de una superficie, se resuelve con una variante o con composición en la app, no forzando el componente base.
5. **Accesibilidad y rendimiento se prueban en el componente, no en cada pantalla que lo usa.** Un componente de `packages/ui` que cumple contraste, foco visible y navegación por teclado lo garantiza en todas las pantallas que lo usan; es más barato invertir una vez en el componente que auditar cada pantalla.
6. **Documentado y visible antes que memorizado.** Todo componente publicado en `packages/ui` debe poder verse, en sus estados y variantes, sin tener que levantar una pantalla real de `apps/web` o `apps/admin` que lo use — para eso existe la documentación visual (ver Reglas).

---

## Reglas

### Tokens (valores provisionales — ver Decisiones futuras para paleta de marca definitiva)

**Color** — capa primitiva (ejemplo, escala 50–900 por familia):

| Familia | Uso previsto | 500 (referencia) |
|---|---|---|
| `gray` | Texto, bordes, fondos neutros | `#71717A` |
| `green` | Candidato a marca piloto (confianza, "adelante") | `#16A34A` |
| `red` | Error, destructivo | `#DC2626` |
| `amber` | Advertencia | `#D97706` |
| `blue` | Información, enlaces | `#2563EB` |

Capa semántica (consumida por los componentes, remapeable por tema):

```
--color-brand-primary:     var(--color-green-600)
--color-brand-primary-hover: var(--color-green-700)
--color-text-default:      var(--color-gray-900)
--color-text-muted:        var(--color-gray-500)
--color-surface:           #FFFFFF
--color-surface-sunken:    var(--color-gray-50)
--color-border:            var(--color-gray-200)
--color-success:           var(--color-green-600)
--color-danger:            var(--color-red-600)
--color-warning:           var(--color-amber-600)
--color-info:              var(--color-blue-600)
```

**Tipografía:**

- Fuente de interfaz: familia sans-serif de rendimiento alto (candidata: Inter, variable, con buen soporte de acentos y ñ del español) — se autohospeda, nunca se carga desde un CDN de terceros en `apps/web` (evita bloqueo de render y coste de una petición externa; ver `ui-guidelines.md`, rendimiento de assets).
- Escala tipográfica (base 16px):

| Token | Tamaño | Uso |
|---|---|---|
| `--font-size-xs` | 12px | Metadatos, etiquetas pequeñas |
| `--font-size-sm` | 14px | Texto secundario, admin denso |
| `--font-size-base` | 16px | Cuerpo de texto |
| `--font-size-lg` | 18px | Cuerpo destacado |
| `--font-size-xl` | 20px | Subtítulos |
| `--font-size-2xl` | 24px | Títulos de sección |
| `--font-size-3xl` | 30px | Títulos de página (admin) |
| `--font-size-4xl` | 36px | Títulos hero (tienda) |
| `--font-size-5xl` | 48px | Hero grande (tienda, marketing) |

- Pesos: `400` (regular, cuerpo), `500` (medium, énfasis/labels), `600` (semibold, títulos y botones). No se usan pesos por debajo de 400 (legibilidad) ni fuentes decorativas para texto funcional.

**Espaciado** — escala base 4px, consumida como `--space-{n}`:

`0, 1(4px), 2(8px), 3(12px), 4(16px), 5(20px), 6(24px), 8(32px), 10(40px), 12(48px), 16(64px), 20(80px), 24(96px)`

**Radios:**

| Token | Valor | Uso |
|---|---|---|
| `--radius-sm` | 4px | Inputs, chips |
| `--radius-md` | 8px | Botones, tarjetas admin |
| `--radius-lg` | 12px | Tarjetas de producto (tienda) |
| `--radius-xl` | 16px | Modales, contenedores destacados |
| `--radius-full` | 9999px | Avatares, badges circulares |

**Sombras** — se usan con criterio distinto por superficie (principio 3 de `ux-principles.md`: la tienda usa elevación para jerarquía y confianza; el admin prefiere bordes planos para densidad y evita sombra decorativa salvo en overlays):

| Token | Uso previsto |
|---|---|
| `--shadow-sm` | Hover sutil, admin (excepcional) |
| `--shadow-md` | Tarjeta de producto, tienda |
| `--shadow-lg` | Modal, dropdown, ambas apps |
| `--shadow-xl` | Elemento flotante destacado (tienda) |

**Breakpoints** (mobile-first, ver `ui-guidelines.md` para su aplicación):

`sm: 640px · md: 768px · lg: 1024px · xl: 1280px · 2xl: 1536px`

### Theming multi-tenant

- Cada tema (el del piloto, o el de un tenant futuro) es un conjunto de valores para la capa semántica de tokens, expuesto como variables CSS en tiempo de ejecución (ej. `[data-tenant="piloto"] { --color-brand-primary: ... }` o inyección de variables por tenant resuelto en el layout raíz de `apps/web`).
- Ningún componente de `packages/ui` importa un tema directamente; los componentes solo referencian tokens semánticos. Cambiar de tema no requiere cambio de código en ningún componente.
- `apps/admin` usa la misma arquitectura de tokens pero, por defecto, un tema neutro propio del panel (no el de marca del tenant que administra) — coherente con `branding.md`: el admin no proyecta personalidad de marca al operador.
- Todo tema debe declarar el conjunto completo de tokens semánticos (no un subconjunto); un tema incompleto usa el tema por defecto del piloto como fallback para los tokens que falten, para que la tienda nunca quede visualmente rota por un tema mal configurado (ver Casos límite).

### Estructura de `packages/ui`

> Nota de estado: a la fecha de esta versión, el repositorio está en Fase 1 (fundación/documentación, ver `README.md`); `apps/` y `packages/` aún no existen como código. La estructura siguiente es la que se implementará al iniciar Fase 2 (MVP piloto).

```
packages/ui/
├── src/
│   ├── tokens/          # fuente de verdad de tokens (primitivos, semánticos), exportados a CSS vars y, si se adopta, a config de un framework de utilidades
│   ├── themes/           # definición de temas por tenant (piloto = tema por defecto)
│   ├── primitives/        # átomos: Button, Input, Select, Checkbox, Radio, Badge, Spinner...
│   ├── components/        # moléculas/organismos: Card, Table, Modal, Toast, Pagination, FormField...
│   ├── patterns/          # patrones compuestos reutilizables: ProductCard (tienda), DataTable con filtros (admin), EmptyState
│   ├── icons/
│   └── index.ts
├── .storybook/            # decisión futura, ver más abajo
└── package.json
```

- `primitives/` no conoce negocio ni superficie (no sabe qué es un "pedido"); `patterns/` sí puede ser consciente de dominio (`ProductCard` sabe que existe un precio y una imagen de producto).
- Ni `apps/web` ni `apps/admin` estilizan directamente un primitivo fuera de `packages/ui`: una necesidad visual nueva se resuelve añadiendo una variante al componente compartido, no con estilos locales que lo sobrescriban desde la app.

### Documentación y pruebas de componentes

- Todo componente de `packages/ui` se documenta con: sus variantes, sus props, sus estados (ver `ui-guidelines.md`) y un ejemplo de uso mínimo — antes de darse por "publicado" para las apps.
- Se evalúa **Storybook** (u otra herramienta equivalente de catálogo visual aislado) como mecanismo de documentación y como entorno de prueba visual/accesibilidad por componente — ver Decisiones futuras para el momento de adopción.
- Todo componente nuevo se revisa contra accesibilidad (contraste, foco, teclado — ver `ui-guidelines.md`) antes de integrarse, no después de detectarse un problema en producción.

---

## Ejemplos

- El componente `Button` expone variantes `primary | secondary | ghost | danger` y tamaños `sm | md | lg`; ambas apps lo consumen igual, pero `apps/admin` usa predominantemente `sm` (densidad) y `apps/web` predominantemente `md`/`lg` (llamadas a la acción visibles).
- Dar de alta un tenant nuevo del SaaS (a futuro) consiste en crear una entrada en `themes/` con sus valores de marca — ningún componente de `packages/ui` se toca para que ese tenant tenga su propia paleta.
- `Table` (en `components/`) tiene una variante `dense` usada por `apps/admin` para pedidos/inventario y una variante `comfortable` usada donde `apps/web` necesite tabular información (ej. tabla de tallas de un producto).

---

## Casos límite

- **Un componente que ambas apps necesitan pero con comportamiento visual muy distinto** (ej. una tabla): se resuelve con una variante explícita del mismo componente (`density="compact" | "comfortable"`), nunca con un fork del componente en cada app — si una variante no alcanza, es señal de que en realidad son dos componentes distintos y se nombran como tales.
- **Fallo o tema no resuelto en tiempo de ejecución** (ej. error al cargar la configuración de tema de un tenant): la tienda cae al tema por defecto del piloto en vez de romper visualmente — nunca se sirve una pantalla sin tokens semánticos resueltos.
- **Contenido de tenant que rompe el layout** (ej. HTML enriquecido en una descripción de producto con estilos propios): se sanitiza y se encapsula su alcance de estilos para que no se filtre a otros componentes de la página.
- **Necesidad real y distinta entre `apps/web` y `apps/admin` que no cabe en una variante razonable:** se documenta como dos componentes con nombres distintos en `patterns/` en vez de forzar un solo componente con demasiadas responsabilidades.

---

## Decisiones futuras

- Paleta de marca definitiva del negocio piloto (los valores de color de este documento son provisionales, elegidos para ilustrar la arquitectura, no validados con negocio) — ver también `branding.md`.
- Adopción formal de Storybook (o alternativa) y momento de incorporarlo al flujo de trabajo (¿desde el primer componente de Fase 2, o cuando `packages/ui` alcance cierto tamaño?).
- Framework de utilidades CSS (ej. Tailwind u otro) para consumir los tokens — no está decidido en ningún ADR todavía; hoy los tokens se definen framework-agnósticos (CSS custom properties) para no bloquear esa decisión.
- Mecanismo de validación automática de contraste al dar de alta el tema de un nuevo tenant (rechazar o auto-ajustar una paleta que no cumpla accesibilidad).
- Estrategia de pruebas de regresión visual (ej. Chromatic, Playwright screenshot testing) para `packages/ui`.
- Tipografía definitiva de marca para `apps/web` si difiere de la tipografía de interfaz (hoy se asume una sola familia para ambas).

---

## Referencias

- [`docs/design/ux-principles.md`](ux-principles.md) — principios de los que se derivan estas decisiones técnicas.
- [`docs/design/branding.md`](branding.md) — identidad de marca que la capa de tema del piloto implementa.
- [`docs/design/ui-guidelines.md`](ui-guidelines.md) — estados de componente, accesibilidad y responsive que todo componente de `packages/ui` debe cumplir.
- [`docs/architecture/vision-tecnica.md`](../architecture/vision-tecnica.md) — principio de multi-tenant desde el diseño, aplicado aquí a tokens en vez de a `tenant_id`.
- [`README.md`](../../README.md) — estructura de monorepo (`apps/`, `packages/`) y estado actual del repositorio (Fase 1).

---

## Historial

- **2026-07-27** — Primera versión.
