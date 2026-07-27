# Roadmap

## Propósito

Expandir el roadmap de cinco fases ya fijado en `README.md`, detallando por fase qué incluye, qué explícitamente no incluye todavía, y el criterio para considerar cada fase terminada. No introduce fechas de calendario — el proyecto no las ha definido — sino orden y duración relativa.

---

## Objetivo

Que en cualquier momento se pueda responder, sin ambigüedad, "¿en qué fase estamos y qué falta para pasar a la siguiente?", con un criterio de cierre verificable en vez de una sensación subjetiva de avance.

## Alcance

**Incluye:** las cinco fases (Fundación, MVP piloto, Logística y proveedores, Multicanal e inteligencia, SaaS), su contenido, sus límites explícitos y su criterio de cierre.

**No incluye:** fechas de calendario, asignación de personas, ni el detalle historia por historia — eso vive en `product-backlog.md`. Tampoco incluye el detalle técnico de cada entregable — eso vive en `docs/architecture/`.

---

## Problema que resuelve

Una lista de fases sin criterio de cierre invita a que "Fase 1" se extienda indefinidamente o que se salte a "Fase 3" sin haber cerrado realmente la "Fase 2". Este documento fija, para cada fase, una definición de "terminado" verificable, y dice explícitamente qué queda fuera de cada fase para evitar que el alcance crezca sin control (*scope creep*) dentro de una fase que ya debería haber cerrado.

---

## Principios

- **Las fases son secuenciales en su cierre, no necesariamente en su inicio.** Se puede empezar a explorar una fase futura (ej. investigar transportadoras durante el MVP), pero no se considera "en curso" oficialmente hasta cerrar la anterior.
- **El piloto manda también en el roadmap.** Ninguna fase multicanal, de inteligencia o SaaS se da por cerrada si no ha sido validada operando el negocio piloto real.
- **Cada fase deja algo funcionando, no solo documentado.** A partir de la Fase 2, el criterio de cierre siempre incluye algo operando con datos reales del piloto, no solo diseñado.
- **El roadmap es de duración y orden relativos, no de fechas**, hasta que el equipo decida fijarlas explícitamente como una decisión aparte.

---

## Reglas

- Ninguna fase se marca como cerrada en `README.md` o `documento-maestro.md` sin cumplir su criterio de cierre definido aquí.
- Todo cambio de alcance de una fase (algo que entra o sale) se registra en el historial de este documento con su motivo.
- El orden de las fases no se altera sin una decisión explícita documentada (ADR si afecta arquitectura, o nota en el historial si es puramente de negocio).

---

## Ejemplos

### Fase 1 — Fundación

**Incluye:** identidad del repositorio, documentos base de negocio (visión de producto, diccionario, actores, casos de uso, módulos, reglas de negocio, backlog inicial, este roadmap) y de arquitectura (visión técnica, principios de arquitectura), reestructuración del repositorio a monorepo, definición de estándares mínimos de contribución.

**No incluye todavía:** código de producto (ni backend ni frontend funcionales), diseño visual/UI definitivo, ni infraestructura de despliegue en producción.

**Criterio de cierre:** los documentos base de negocio y arquitectura existen, están completos según su propio formato, y no se contradicen entre sí; el monorepo tiene su estructura de carpetas definida (`apps/`, `packages/`, `docs/`, `infrastructure/`, `database/`, `resources/`, `scripts/`) aunque `apps/` todavía esté vacío o en migración.

### Fase 2 — MVP piloto

**Incluye:** tienda virtual funcional para el negocio piloto (catálogo, carrito, checkout, pago), panel administrativo con gestión de productos, inventario y pedidos de un único canal (tienda propia), modelo de datos multi-tenant desde el primer esquema aunque solo exista un tenant.

**No incluye todavía:** comparador de transportadoras (el envío se gestiona manualmente), integración con marketplaces, publicidad digital, dashboard de inteligencia comercial, automatización con IA.

**Criterio de cierre:** el negocio piloto procesa ventas reales de principio a fin (cliente compra en la tienda propia → aparece en el panel → el emprendedor lo gestiona) sin depender de herramientas externas para esas tres cosas.

### Fase 3 — Logística y proveedores

**Incluye:** comparador de transportadoras (costo, tiempo, cobertura) integrado al flujo de despacho, gestión logística con seguimiento de estado de pedido, directorio de proveedores.

**No incluye todavía:** integración con marketplaces (TikTok Shop, Mercado Libre), publicidad digital, dashboard de inteligencia comercial más allá de reportes básicos, automatización con IA.

**Criterio de cierre:** el emprendedor despacha pedidos del piloto eligiendo transportadora desde el comparador (no manualmente fuera del sistema), y hace seguimiento del pedido dentro del panel hasta la entrega; el directorio de proveedores tiene al menos un conjunto real de proveedores del piloto cargado y consultable.

### Fase 4 — Multicanal e inteligencia

**Incluye:** integración con **Mercado Libre primero** (marketplace validado y operativo en Colombia — sitio MCO), con inventario sincronizado (sistema unificado, ver Regla 1 de `reglas-de-negocio.md` para el manejo de conflictos); dashboard de inteligencia comercial (ventas, clientes, tendencias, sugerencias de reabastecimiento); módulo de publicidad digital (Meta Ads, Google Ads).

**TikTok Shop queda en espera, no cancelado.** La investigación en `docs/research/tiktok-shop.md` encontró que, a la fecha de esta versión, TikTok Shop no tiene onboarding de vendedor local abierto en Colombia. Se integra cuando esa condición cambie — hasta entonces no se planifica trabajo de desarrollo sobre ese canal. Antes de iniciar esa integración, se re-valida el estado real en `docs/research/tiktok-shop.md`, porque esta condición puede cambiar.

**No incluye todavía:** apertura de la plataforma a otros emprendedores (sigue siendo single-tenant operativo, aunque el modelo de datos ya es multi-tenant desde la Fase 1), automatización avanzada de IA más allá de sugerencias (ej. generación de contenido, si no se adelantó antes).

**Criterio de cierre:** el negocio piloto vende simultáneamente en tienda propia y Mercado Libre, con inventario que se mantiene consistente entre canales sin intervención manual; el dashboard refleja datos reales de esos canales; al menos una campaña de publicidad digital real ha sido gestionada y medida desde la plataforma.

### Fase 5 — SaaS

**Incluye:** apertura de la plataforma a otros emprendedores mediante creación de tenants nuevos, panel y rol de administrador de la plataforma, modelo de cobro (licencia, suscripción o comisión — decisión pendiente, ver `vision-producto.md` → Decisiones futuras), aislamiento operativo y de datos entre tenants verificado con más de un tenant real o simulado con carga representativa.

**No incluye todavía (a definir en su momento):** portal propio para proveedores o para clientes finales (hoy aspiracionales, ver `vision-producto.md`), expansión fuera del mercado hispanohablante inicial.

**Criterio de cierre:** al menos un tenant adicional al piloto opera de forma real (o en un piloto controlado) sobre la plataforma, de forma aislada, sin haber requerido cambios estructurales al modelo de datos ni a los módulos ya construidos.

---

## Casos límite

- **Una funcionalidad de una fase posterior resulta necesaria antes** (ej. el piloto necesita comparador de transportadoras antes de terminar el dashboard de la Fase 4 porque el volumen de envíos crece rápido): se permite adelantar funcionalidad puntual, pero la fase solo se marca "en curso" o "cerrada" oficialmente según el orden y criterio aquí definidos, no según qué se construyó primero.
- **El piloto crece mucho antes de llegar a la Fase 5:** según el principio de multi-tenant desde el modelo de datos (`vision-tecnica.md`), esto no debe forzar una reescritura; si ocurre, es una señal de que el criterio de cierre de alguna fase anterior no se cumplió realmente y debe revisarse.
- **Una fase se cierra parcialmente** (ej. Fase 4 sin publicidad digital pero con multicanal e inteligencia funcionando): se documenta como excepción explícita en el historial de este documento, no se marca como "cerrada" sin más.

---

## Decisiones futuras

- Fijar fechas de calendario por fase, una vez el equipo y los recursos disponibles lo permitan estimar con confianza.
- Definir si alguna fase se puede paralelizar con la siguiente en vez de ser estrictamente secuencial, cuando el equipo crezca más allá de su tamaño actual.
- Definir el criterio exacto de "carga representativa" para validar el aislamiento multi-tenant al cierre de la Fase 5.

---

## Referencias

- [`README.md`](../../README.md) — versión resumida de este roadmap.
- [`docs/business/product-backlog.md`](product-backlog.md) — historias de usuario detalladas por fase.
- [`docs/business/vision-producto.md`](vision-producto.md) — decisiones futuras que varias fases dejan pendientes deliberadamente.
- [`docs/business/reglas-de-negocio.md`](reglas-de-negocio.md) — reglas que algunas fases dan por implementadas al cerrar.
- [`docs/architecture/vision-tecnica.md`](../architecture/vision-tecnica.md) — decisión de multi-tenant desde el modelo de datos que sustenta el criterio de cierre de la Fase 5.

---

## Historial

- **2026-07-27** — Primera versión.
