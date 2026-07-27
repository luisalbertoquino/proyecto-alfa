# Documento Maestro

## Propósito

Ser el punto de entrada único a Proyecto Alfa: en cinco minutos, cualquier persona — inversionista, nuevo integrante del equipo, o el propio fundador retomando el proyecto tras una pausa — debe entender qué es, para quién, en qué fase está y dónde profundizar. Este documento no introduce información nueva; amarra y enlaza lo que ya está fijado en `README.md`, `vision-producto.md`, `roadmap.md` y `modulos.md`.

---

## Objetivo

Dar una vista ejecutiva y siempre actualizada de: la visión, el objetivo general, los módulos del ecosistema y el estado/fase actual del proyecto, con enlaces directos a cada documento de detalle — sin obligar a leer los ocho documentos de negocio para entender el panorama completo.

## Alcance

**Incluye:** resumen ejecutivo de visión y objetivo, listado de módulos con un enlace a su detalle, estado de fase actual, y mapa de navegación a los documentos de negocio y arquitectura relevantes.

**No incluye:** el detalle de cada módulo (→ `modulos.md`), el roadmap completo (→ `roadmap.md`), la terminología (→ `diccionario-del-negocio.md`), ni las reglas de negocio (→ `reglas-de-negocio.md`). Este documento resume; no repite ni contradice.

---

## Problema que resuelve

Un proyecto documentado a fondo tiene el riesgo opuesto a uno sin documentar: demasiados documentos para que alguien nuevo sepa por dónde empezar. Este documento resuelve el "¿por dónde empiezo?" con una sola lectura corta que ya contiene los enlaces a todo lo demás, en el orden en que conviene leerlo.

---

## Principios

- **Resume, no duplica.** Cada afirmación aquí debe poder rastrearse a un documento de detalle; si algo cambia en el detalle, este documento se actualiza para reflejarlo, nunca al revés.
- **Cinco minutos, no cincuenta.** Si este documento crece al punto de tardar más de cinco minutos en leerse, algo debe moverse a un documento de detalle.
- **Siempre refleja el estado real del proyecto**, no el estado deseado — la sección "Estado actual" se actualiza en cada cambio de fase.

---

## Reglas

- Toda actualización de fase en `README.md` o `roadmap.md` debe reflejarse en la sección "Estado actual" de este documento en el mismo cambio.
- Todo módulo nuevo agregado en `modulos.md` debe aparecer también en la lista de módulos de este documento.
- Los enlaces de este documento apuntan siempre a la ruta relativa dentro del repositorio, nunca a una copia del contenido.

---

## Ejemplos

*(Sección informativa — no aplica un ejemplo de "caso de uso"; a continuación, el resumen ejecutivo que este documento existe para entregar.)*

### Qué es Proyecto Alfa

Proyecto Alfa es una plataforma de comercio electrónico inteligente que centraliza, para un emprendedor, la gestión de ventas, inventario, logística, publicidad y analítica de múltiples canales digitales, con inteligencia artificial como eje transversal para automatizar tareas repetitivas. Nace como la tienda virtual de un negocio real (el piloto), que valida cada funcionalidad con operación real antes de generalizarla como producto **SaaS multi-tenant** para otros emprendedores. Mercado inicial asumido: Colombia y Latinoamérica hispanohablante (por el uso de "transportadora" y la integración con Mercado Libre) — supuesto ajustable, no decisión cerrada. Ver [`README.md`](../../README.md) y [`docs/business/vision-producto.md`](vision-producto.md).

### Objetivo general

Desarrollar una plataforma integral para emprendedores y comercios electrónicos que centralice la gestión de ventas, inventarios, logística, publicidad y analítica de múltiples canales digitales, incorporando inteligencia artificial para automatizar procesos, optimizar la operación y aumentar las oportunidades de venta. Detalle completo de objetivos específicos en [`vision-producto.md`](vision-producto.md).

### Módulos del ecosistema

| Módulo | En una frase |
|---|---|
| Tienda virtual | Canal de venta propio orientado a conversión. |
| Panel administrativo | Centro de control del emprendedor sobre catálogo, inventario, pedidos y ventas. |
| Comparador de transportadoras | Elige envío por costo, tiempo y cobertura en vez de "a ojo". |
| Gestión logística | Despacho y seguimiento de pedidos hasta la entrega. |
| Directorio de proveedores | Ayuda al emprendedor a encontrar proveedores confiables. |
| Publicidad digital | Planifica, administra y mide campañas en Meta Ads y Google Ads. |
| Dashboard de inteligencia comercial | Ventas, clientes y tendencias en tiempo real. |
| Integración multicanal | Tienda propia, TikTok Shop y Mercado Libre con inventario sincronizado. |
| Automatización con IA | Genera descripciones, responde preguntas frecuentes, sugiere reabastecimiento. |
| Arquitectura SaaS multi-tenant | Base para que otros emprendedores usen la plataforma. |

Detalle de cada módulo (qué hace, para quién, qué valor entrega) en [`modulos.md`](modulos.md).

### Estado y fase actual

**Fase 1 — Fundación.** El proyecto está en documentación de negocio y arquitectura, previo al desarrollo de código de producto. El repositorio está en proceso de reestructuración a monorepo (`apps/`, `packages/`, `docs/`, `infrastructure/`, `database/`, `resources/`, `scripts/`). Detalle de las cinco fases del roadmap, qué incluye y excluye cada una, en [`roadmap.md`](roadmap.md).

### Mapa de navegación

- **Para entender el negocio a fondo:** [`vision-producto.md`](vision-producto.md) → [`modulos.md`](modulos.md) → [`casos-de-uso.md`](casos-de-uso.md) → [`reglas-de-negocio.md`](reglas-de-negocio.md).
- **Para entender la terminología:** [`diccionario-del-negocio.md`](diccionario-del-negocio.md) → [`actores.md`](actores.md).
- **Para entender la planeación:** [`roadmap.md`](roadmap.md) → [`product-backlog.md`](product-backlog.md).
- **Para entender la arquitectura técnica:** [`../architecture/vision-tecnica.md`](../architecture/vision-tecnica.md) → [`../architecture/principios-de-arquitectura.md`](../architecture/principios-de-arquitectura.md).
- **Para el panorama general del repositorio:** [`README.md`](../../README.md).

---

## Casos límite

- **Un lector solo tiene tiempo para este documento y ninguno más:** debe salir sabiendo qué es Proyecto Alfa, cuál es su objetivo, qué módulos tiene y en qué fase está — si no logra eso, el documento no está cumpliendo su propósito y debe revisarse.
- **Un documento de detalle cambia y este documento queda desactualizado:** se considera una inconsistencia de documentación a corregir con la misma prioridad que un bug de código, no una tarea de "cuando haya tiempo".

---

## Decisiones futuras

- Evaluar si, al llegar a la Fase 3 o 4, conviene una versión aún más corta ("una página") de este documento para audiencias no técnicas (ej. inversionistas).
- Evaluar traducir este documento (y el resto de la documentación de negocio) si el proyecto empieza a operar fuera del mercado hispanohablante inicial.

---

## Referencias

- [`README.md`](../../README.md)
- [`docs/business/vision-producto.md`](vision-producto.md)
- [`docs/business/modulos.md`](modulos.md)
- [`docs/business/roadmap.md`](roadmap.md)
- [`docs/business/diccionario-del-negocio.md`](diccionario-del-negocio.md)

---

## Historial

- **2026-07-27** — Primera versión.
