# Arquitectura de Frontend

## Propósito

Describir cómo se organizan `apps/web` (tienda pública) y `apps/admin` (panel administrativo), ambas en Next.js: qué estrategia de renderizado usa cada tipo de página, cómo se gestiona el estado, cómo se accede a la API propia, y cómo se comparte código de interfaz entre las dos aplicaciones a través de `packages/ui`. Aplica el principio API-first de `vision-tecnica.md`: ningún frontend accede a datos que no vengan de la API de Laravel.

---

## Objetivo

Que `apps/web` cargue rápido y posicione bien en buscadores porque renderiza donde debe renderizar, que `apps/admin` sea productivo para el equipo que opera el negocio sin pagar el costo de SSR donde no aporta, y que ninguna de las dos reimplemente componentes de interfaz que la otra ya tiene resueltos.

---

## Alcance

Cubre: estrategia de renderizado por tipo de página en `apps/web` y `apps/admin`, gestión de estado en cada app, capa de acceso a la API, y estructura de `packages/ui`.

No cubre: diseño visual y sistema de componentes en sí (`docs/design/design-system.md`), contrato de la API que se consume (`apis.md`), ni pipeline de build/deploy del frontend (`infraestructura.md`, `docs/development/ci-cd.md`).

---

## Problema que resuelve

Una tienda de comercio electrónico que renderiza todo en el cliente pierde SEO y velocidad de primera carga — justo lo que más pesa en conversión de un visitante nuevo. Un panel administrativo que intenta ser estático o SSR paga complejidad (revalidación, caché) que no necesita, porque solo lo usa el equipo del negocio, autenticado, sin necesidad de indexarse en buscadores. Y dos aplicaciones Next.js separadas sin una capa compartida terminan con dos botones "distintos", dos formas de llamar a la API, y el doble de trabajo para cualquier cambio de marca. Este documento fija la estrategia que evita los tres problemas.

---

## Principios

1. **Renderizar según para quién es la página, no por costumbre.** Una página de producto se renderiza pensando en un comprador anónimo y en Google; una pantalla del panel se renderiza pensando en un usuario autenticado que ya cargó la aplicación.
2. **Ningún frontend habla con la base de datos ni con servicios externos directamente.** Todo pasa por la API propia de Laravel (`vision-tecnica.md`, principio API-first) — ni `apps/web` ni `apps/admin` integran una transportadora, un marketplace o el proveedor de IA por su cuenta.
3. **Lo compartido vive en `packages/ui`, no se copia y pega.** Un componente de interfaz usado en más de una app se promueve al paquete compartido en cuanto existe una segunda necesidad real, no antes.
4. **Estado del servidor y estado de interfaz son cosas distintas y se gestionan distinto.** Los datos que vienen de la API tienen su propia capa de cacheo/revalidación; el estado puramente de UI (un modal abierto, un formulario en edición) no vive en el mismo lugar.
5. **La velocidad percibida importa tanto como la real.** Estados de carga, contenido esqueleto y revalidación en segundo plano son parte del diseño, no un extra.

---

## Reglas

### Estrategia de renderizado — `apps/web` (tienda pública)

- **SSG/ISR** para páginas de catálogo cuyo contenido cambia con poca frecuencia relativa al tráfico que reciben: ficha de producto, listado de categoría, landing de campaña. Se revalidan por intervalo (ISR) o bajo demanda cuando el producto cambia (ej. al recibir el evento `StockActualizado` o `ProductoActualizado` desde el backend).
- **SSR** para páginas cuyo contenido depende de datos que cambian en cada visita o de la identidad del visitante de forma relevante para SEO/conversión: resultados de búsqueda con filtros, disponibilidad de stock en tiempo real en la ficha de producto de alta rotación.
- **CSR** limitado a interacciones puramente de cliente dentro de una página ya renderizada: carrito de compra, selector de variante, formulario de checkout — nunca para el contenido indexable en sí.
- Métricas de SEO y Core Web Vitals (LCP, CLS) son criterio de aceptación de cualquier página nueva de catálogo, no un chequeo posterior.

### Estrategia de renderizado — `apps/admin` (panel administrativo)

- **CSR sobre un shell mínimo**, con autenticación obligatoria antes de cargar cualquier dato: no hay necesidad de SEO ni de first paint sin JavaScript en un panel interno.
- Excepción: páginas de login/recuperación de acceso pueden usar SSR simple por consistencia con el resto de la app y para evitar parpadeos, pero no por necesidad de indexación.
- Priorizar productividad del operador (tablas densas, atajos de teclado, actualizaciones optimistas) sobre técnicas pensadas para visitantes anónimos.

### Gestión de estado

- Estado de servidor (datos que vienen de la API: productos, pedidos, inventario) se gestiona con una librería de fetching con caché y revalidación (ej. React Query/SWR o el mecanismo de datos nativo de Next.js con revalidación), nunca copiado a mano a un store global.
- Estado de UI local (formulario en progreso, modal, filtros no aplicados aún) vive en estado de componente o un store ligero de cliente; no se mezcla con el estado de servidor.
- `apps/admin`, al ser más interactivo, es donde más aparece estado de servidor con mutaciones optimistas (ej. marcar un pedido como despachado y reflejarlo antes de la confirmación del servidor, con reversión si falla).

### Capa de acceso a la API

- Ambas apps consumen la API a través de un cliente HTTP propio y compartido (en `packages/`) que ya conoce: base URL por entorno, inyección del token de autenticación, manejo uniforme del sobre de respuesta y de errores definido en `apis.md`.
- Ningún componente arma manualmente una URL de la API o parsea el sobre `{ data, error }` por su cuenta; pasa por esa capa.
- Las llamadas que disparan trabajo en cola en el backend (ej. regenerar descripción con IA) manejan explícitamente el estado "en proceso" en la interfaz — no bloquean asumiendo respuesta inmediata.

### `packages/ui`

- Contiene componentes de interfaz sin lógica de negocio ni llamadas a la API: botones, inputs, tablas, modales, tipografía, tokens de diseño — construidos sobre el sistema de diseño (`docs/design/design-system.md`).
- Un componente entra a `packages/ui` cuando lo necesita más de una app o cuando se anticipa que lo necesitará (ej. componentes del design system desde el día uno); lógica específica de negocio de un módulo (ej. una tarjeta de "producto con badge de stock bajo") vive en la app que la usa, no en el paquete compartido.
- `packages/ui` no depende de Next.js específicamente donde sea evitable, para permitir reutilizarlo si en el futuro existe un tercer cliente (ej. una app móvil con React Native/Expo).

---

## Ejemplos

- La ficha de un producto en `apps/web` se genera con SSG y se revalida cuando el backend emite un evento de cambio de precio o stock relevante; el buscador de la tienda usa SSR porque los filtros y resultados cambian por cada búsqueda y siguen importando para SEO de términos de cola larga.
- El listado de pedidos en `apps/admin` se carga por CSR tras autenticar, con paginación y filtros gestionados en el cliente contra la API, y actualización optimista al cambiar el estado de un pedido a "despachado".
- Un componente `Badge` de "stock bajo" con el mismo estilo visual se define una vez en `packages/ui` y se usa tanto en la ficha de producto de `apps/web` como en la tabla de inventario de `apps/admin`, con la lógica de negocio de "cuándo mostrarlo" resuelta por separado en cada app.

---

## Casos límite

- **Producto que cambia de stock a mitad de una página SSG ya generada** (ej. se agota mientras la página estática sigue servida): la ficha de producto revalida stock en el cliente al cargar o usa ISR con intervalo corto para ese dato específico, para no anunciar disponibilidad falsa.
- **Panel admin usado en conexión lenta o intermitente** (contexto realista para un emprendedor operando desde el celular): las mutaciones optimistas deben poder revertirse con claridad si la petición falla, sin dejar al usuario en un estado ambiguo.
- **Página de catálogo con tráfico de campaña publicitaria repentino:** al ser SSG/ISR servida detrás de CDN (Cloudflare), el pico de tráfico no debería tocar directamente la API ni los contenedores de `apps/web` en la mayoría de las requests.

---

## Decisiones futuras

- Librería concreta de gestión de estado de servidor (React Query vs. SWR vs. capa de datos nativa de Next.js) — se decidirá como ADR al iniciar la Fase 2.
- Si `packages/ui` se publica como paquete versionado internamente (para permitir que evolucione a ritmo distinto de las apps) o se consume directamente del monorepo sin publicación.
- Estrategia de internacionalización cuando el modelo SaaS atienda mercados fuera de Colombia/LatAm hispanohablante.
- Uso de Server Actions de Next.js como alternativa a llamadas explícitas al cliente HTTP compartido, evaluado cuando exista código real que lo justifique.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — principio API-first que rige el acceso a datos desde ambas apps.
- `docs/architecture/apis.md` — contrato de la API que esta capa de acceso consume.
- `docs/design/design-system.md` — sistema de diseño que `packages/ui` implementa.
- `docs/architecture/escalabilidad.md` — rol de Cloudflare/CDN sobre las páginas estáticas de `apps/web`.

---

## Historial

- **2026-07-27** — Primera versión.
