# Proyecto Alfa

## ¿Qué es?

Proyecto Alfa es una plataforma de comercio electrónico inteligente que permite a emprendedores crear, administrar y optimizar sus ventas en línea desde un solo lugar. Integra gestión de tienda, inventario, pedidos, logística, proveedores, publicidad digital y analítica de negocio, con inteligencia artificial como eje transversal para automatizar procesos.

A mediano plazo, la arquitectura del sistema está pensada para operar como una plataforma **SaaS multi-tenant**, de modo que otros emprendedores puedan crear sus propias tiendas sobre la misma base.

## ¿Por qué existe?

Nace como la tienda virtual de un negocio real (el piloto de Proyecto Alfa), que se usa como caso de estudio para validar cada funcionalidad con datos y operación reales antes de generalizarla y ofrecerla a otros emprendedores.

## Problema que resuelve

Un emprendedor de comercio electrónico hoy típicamente opera con herramientas fragmentadas y desconectadas entre sí:

- Vende en varios canales (tienda propia, TikTok Shop, Mercado Libre, etc.) sin un lugar único donde ver todos sus pedidos.
- Controla el inventario a mano o por canal, con riesgo constante de sobreventa.
- Elige transportadora "a ojo", sin comparar costo, tiempo y cobertura.
- No tiene visibilidad de qué productos rotan, cuáles conviene reabastecer o qué tendencias está perdiendo.
- Gasta tiempo valioso en tareas repetitivas: redactar descripciones de producto, responder preguntas frecuentes, armar reportes.

Proyecto Alfa centraliza esas funciones en un solo sistema para que el emprendedor pase de operar disperso a operar con datos.

## Objetivos

**Objetivo general**

Desarrollar una plataforma integral para emprendedores y comercios electrónicos que centralice la gestión de ventas, inventarios, logística, publicidad y analítica de múltiples canales digitales, incorporando herramientas de inteligencia artificial para automatizar procesos, optimizar la operación y aumentar las oportunidades de venta.

**Objetivos específicos** (resumen — detalle completo en [`docs/business/vision-producto.md`](docs/business/vision-producto.md))

- Tienda virtual moderna orientada a conversión.
- Panel administrativo de productos, inventario, pedidos, clientes y ventas.
- Comparador de transportadoras (costo, tiempo, cobertura).
- Gestión logística centralizada (despacho y seguimiento).
- Directorio de proveedores confiables.
- Módulo de publicidad digital (Meta Ads, Google Ads, futuras integraciones).
- Dashboard de inteligencia comercial (ventas, clientes, tendencias).
- Integración multicanal (tienda propia, TikTok Shop, Mercado Libre, otros marketplaces) con inventario sincronizado entre canales.
- Automatización con IA de tareas repetitivas (páginas de producto, descripciones, respuestas frecuentes).
- Arquitectura preparada para operar como SaaS (licencias, suscripciones o comisión por venta).
- El negocio piloto como caso de estudio para validar y medir antes de comercializar.

## Arquitectura General

El repositorio sigue un enfoque de **monorepo**, donde varias aplicaciones relacionadas conviven y comparten estándares:

```
proyecto-alfa/
├── apps/
│   ├── api/         # Laravel — API JSON, monolito modular por dominio (incluye sus propias database/ y storage/)
│   ├── web/         # Next.js — tienda pública
│   └── admin/       # Next.js — panel administrativo
├── packages/        # código compartido entre apps (aún no creado)
├── docs/            # documentación de arquitectura, negocio, diseño, desarrollo, research, ADRs, RFCs, actas
├── resources/       # logos, mockups, íconos, capturas — hoy vacío, ver resources/README.md
├── scripts/         # despliegue y respaldo del droplet (scripts/README.md)
└── templates/       # plantillas para documentos nuevos de docs/
```

Cada carpeta principal tiene su propio `README.md` con el detalle de qué va ahí (`apps/api/README.md`, `apps/web/README.md`, `apps/admin/README.md`, `resources/README.md`, `scripts/README.md`, `templates/README.md`).

El detalle completo vive en [`docs/architecture/vision-tecnica.md`](docs/architecture/vision-tecnica.md).

## Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Backend / API | Laravel |
| Frontend | Next.js |
| Base de datos | MySQL |
| Cache / colas | Redis |
| Despliegue | Nativo (sin contenedores) — droplet con OpenLiteSpeed en esta fase de prototipo; Docker queda como opción a evaluar en fases posteriores |
| CDN / seguridad | *Sin implementar todavía* — Cloudflare estaba planeado, pero el prototipo desplegado usa SSL directo en el droplet (certbot/Let's Encrypt) y DNS gestionado directo en el proveedor de dominio. Se retoma si el tráfico real lo justifica. |
| CI/CD | GitHub Actions (pipeline aún no configurado — el despliegue hoy es manual vía `scripts/deploy.sh`) |

La justificación de cada elección se documentará en un stack tecnológico dedicado dentro de `docs/architecture/`. El cambio de PostgreSQL a MySQL y de Docker a despliegue nativo está documentado en [`docs/adr/ADR-002.md`](docs/adr/ADR-002.md).

## Estado del Proyecto

🚧 **Fase 2 — Prototipo funcional del piloto** (sprint de 30 días, no producción). Fase 1 (fundación documental) completada. Estado detallado y siempre actualizado en [`docs/estado-actual.md`](docs/estado-actual.md).

## Roadmap

1. **Fase 1 — Fundación:** identidad del repositorio, estándares, documentos base (visión de producto, visión técnica, stack, estándares, design system, modelo de datos).
2. **Fase 2 — MVP piloto:** tienda virtual, panel administrativo, gestión de pedidos e inventario para el negocio piloto.
3. **Fase 3 — Logística y proveedores:** comparador de transportadoras, directorio de proveedores.
4. **Fase 4 — Multicanal e inteligencia:** integraciones con marketplaces, dashboard de inteligencia comercial, publicidad digital.
5. **Fase 5 — SaaS:** multi-tenant, licenciamiento/suscripciones, apertura a otros emprendedores.

Detalle y fechas en [`docs/business/roadmap.md`](docs/business/roadmap.md).

## Estructura del Repositorio

| Carpeta | Contenido |
|---|---|
| `apps/api`, `apps/web`, `apps/admin` | Código de la aplicación: API Laravel, tienda Next.js y panel administrativo Next.js. Esquemas/migraciones viven dentro de `apps/api/database/`, no en una carpeta `database/` separada. |
| `docs/` | Documentación: arquitectura, negocio, diseño, desarrollo, research, actas (`docs/meetings/`), ADRs, RFCs, estándares |
| `resources/` | Logos, mockups, íconos, inspiración, capturas — vacío hoy, ver `resources/README.md` |
| `scripts/` | Despliegue y respaldo del droplet de pruebas (`deploy.sh`, `respaldo.sh`) — ver `scripts/README.md` |
| `templates/` | Plantillas para arrancar un documento nuevo de `docs/` |

## Cómo contribuir

Proyecto de equipo reducido (un desarrollador, una socia de marketing/diseño). Los estándares de nombres, commits, ramas, API y base de datos ya están definidos en [`docs/standards/`](docs/standards/) — sigue esos antes de improvisar un formato nuevo. Cualquier cambio significativo debe:

1. Documentarse primero (qué problema resuelve, qué decisión toma) siguiendo la plantilla de [`templates/nuevo-documento.md`](templates/nuevo-documento.md).
2. Pasar por una rama separada de `main`, con el prefijo de [`docs/standards/branches.md`](docs/standards/branches.md).
3. Describirse en el commit/PR con el porqué del cambio, no solo el qué — ver [`docs/standards/commits.md`](docs/standards/commits.md).

## Licencia

Aún no definida. Pendiente decidir entre comercial, open source o código privado — ver [`LICENSE`](LICENSE).

## Contacto

Luis Alberto Quino — [luisalbertoquino@gmail.com](mailto:luisalbertoquino@gmail.com)
Repositorio: [github.com/luisalbertoquino/proyecto-alfa](https://github.com/luisalbertoquino/proyecto-alfa)
