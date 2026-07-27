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

El repositorio está migrando hacia un enfoque de **monorepo**, donde varias aplicaciones relacionadas conviven y comparten estándares:

```
proyecto-alfa/
├── apps/            # web (tienda), admin (panel), api  — en migración desde backend/ + frontend/
├── packages/        # código compartido entre apps
├── docs/            # documentación de arquitectura, negocio, diseño, desarrollo, research, ADRs, RFCs
├── infrastructure/  # scripts de despliegue, configuración del servidor (OpenLiteSpeed, nativo por ahora)
├── database/        # esquemas, migraciones, seeders
├── resources/        # logos, mockups, íconos, capturas
└── scripts/         # automatizaciones y utilidades de desarrollo
```

El detalle completo vive en [`docs/architecture/vision-tecnica.md`](docs/architecture/vision-tecnica.md) (en construcción).

## Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Backend / API | Laravel |
| Frontend | Next.js |
| Base de datos | MySQL |
| Cache / colas | Redis |
| Despliegue | Nativo (sin contenedores) — droplet con OpenLiteSpeed en esta fase de prototipo; Docker queda como opción a evaluar en fases posteriores |
| CDN / seguridad | Cloudflare |
| CI/CD | GitHub Actions |

La justificación de cada elección se documentará en un stack tecnológico dedicado dentro de `docs/architecture/`. El cambio de PostgreSQL a MySQL y de Docker a despliegue nativo está documentado en [`docs/adr/ADR-002.md`](docs/adr/ADR-002.md).

## Estado del Proyecto

🚧 **Fase 2 — Prototipo funcional del piloto** (sprint de 30 días, no producción). Fase 1 (fundación documental) completada. Estado detallado y siempre actualizado en [`docs/estado-actual.md`](docs/estado-actual.md).

## Roadmap

1. **Fase 1 — Fundación:** identidad del repositorio, estándares, documentos base (visión de producto, visión técnica, stack, estándares, design system, modelo de datos).
2. **Fase 2 — MVP piloto:** tienda virtual, panel administrativo, gestión de pedidos e inventario para el negocio piloto.
3. **Fase 3 — Logística y proveedores:** comparador de transportadoras, directorio de proveedores.
4. **Fase 4 — Multicanal e inteligencia:** integraciones con marketplaces, dashboard de inteligencia comercial, publicidad digital.
5. **Fase 5 — SaaS:** multi-tenant, licenciamiento/suscripciones, apertura a otros emprendedores.

Detalle y fechas en [`docs/business/roadmap.md`](docs/business/roadmap.md) (en construcción).

## Estructura del Repositorio

| Carpeta | Contenido |
|---|---|
| `backend/`, `frontend/` | Código actual de la aplicación (en migración a `apps/`) |
| `docs/` | Documentación: arquitectura, negocio, diseño, desarrollo, research, actas, ADRs, RFCs, estándares |
| `database/` | Esquemas y migraciones |
| `infrastructure/` | Scripts de despliegue y configuración del servidor (OpenLiteSpeed, nativo por ahora) |
| `resources/` | Logos, mockups, íconos, inspiración, capturas |
| `scripts/` | Utilidades de desarrollo |

## Cómo contribuir

El proyecto está en fase de fundación con equipo reducido. Los estándares de ramas, commits y documentación se están definiendo en `docs/standards/` (pendiente de creación). Hasta que existan, cualquier cambio debe:

1. Documentarse primero (qué problema resuelve, qué decisión toma).
2. Pasar por una rama separada de `main`.
3. Describirse en el PR con el porqué del cambio, no solo el qué.

## Licencia

Aún no definida. Pendiente decidir entre comercial, open source o código privado — ver [`LICENSE`](LICENSE).

## Contacto

Luis Alberto Quino — [luisalbertoquino@gmail.com](mailto:luisalbertoquino@gmail.com)
Repositorio: [github.com/luisalbertoquino/proyecto-alfa](https://github.com/luisalbertoquino/proyecto-alfa)
