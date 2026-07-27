# Estado Actual del Proyecto

> Este documento se actualiza en cada sesión de trabajo relevante. No es un historial completo — para eso está el historial de git y el `## Historial` de cada documento formal. Aquí solo vive **el presente**: en qué fase estamos y qué sigue.

**Última actualización:** 2026-07-27

---

## Fase

- **Fase 1 — Fundación documental:** completada. README, LICENSE, .gitignore y los 47 documentos de `docs/` + `templates/` + ADR-001 están escritos y commiteados (`2d52a3a`).
- **Fase 2 — Prototipo funcional del piloto:** en curso. Objetivo del sprint: un prototipo **confiable y funcional**, no un despliegue en producción. No se integra pasarela de pago real, publicidad ni servicios pagos todavía — eso se evalúa después de validar el prototipo.
- Ventana de tiempo: el usuario tiene 30 días libres a partir de 2026-07-27 dedicados a avanzar esto, con intención de avanzar más rápido de lo mínimo si es posible.

---

## El piloto activo

- **Negocio:** tienda de skincare en línea (nombre del negocio y catálogo real: pendientes de definir).
- **Equipo:** proyecto de dos socios.
  - **Angie** — socia. Se enfoca en marketing y comunicaciones; su participación empieza cuando haya un prototipo que mostrar, no durante la construcción técnica.
  - **El usuario** (dueño de este repositorio) — a cargo de producto y desarrollo, construyendo en solitario con Claude Code como implementador principal.
- Esto es exactamente el "negocio piloto" que describen `docs/business/vision-producto.md` y `docs/business/actores.md` — ahí donde esos documentos dicen "el equipo fundador cumple varios roles a la vez", ese equipo fundador es el usuario (hoy) y Angie (desde que haya algo que comunicar).

## Infraestructura ya disponible

- Un servidor (Droplet de DigitalOcean) reservado para pruebas de este prototipo.
- Un dominio ya comprado, disponible para usarse cuando el prototipo esté listo para mostrarse.
- Ninguno de los dos se usa como entorno de producción todavía — son para pruebas del prototipo del sprint de 30 días.

---

## Hecho hasta ahora

- [x] Fase 1 completa y commiteada.
- [x] Roadmap ajustado tras investigación real: Mercado Libre se integra antes que TikTok Shop (TikTok Shop no tenía onboarding de vendedor local abierto en Colombia a la fecha de la investigación — re-validar en `docs/research/tiktok-shop.md` antes de construir esa integración).
- [x] Alcance del sprint de 30 días acordado: prototipo funcional, no producción.
- [ ] Semana 1 — Cimientos: Laravel (auth + modelo de datos núcleo con `tenant_id`) + Next.js conectado a la API propia. Entorno local, sin Docker ni despliegue todavía.
- [ ] Semana 2 — Tienda: catálogo público, carrito, checkout con pago simulado (pedido queda "pendiente de confirmación", el admin lo confirma a mano — sin pasarela de pago real).
- [ ] Semana 3 — Panel: CRUD de productos, gestión de pedidos, descuento de stock al confirmar un pedido.
- [ ] Semana 4 — Confiabilidad: cargar el catálogo real de skincare, pruebas end-to-end del flujo completo, corregir lo que rompa.

## Próximo paso concreto

Definir el entorno de desarrollo local (versiones de PHP/Composer/Node instaladas, si se desarrolla en local y se despliega luego al Droplet, o se trabaja directo sobre el Droplet por SSH) y arrancar la Semana 1.

## Decisiones pendientes que no son técnicas

- Nombre de la tienda de skincare.
- Catálogo real de productos a cargar en la Semana 4.
