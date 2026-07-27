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

- Un servidor (Droplet de DigitalOcean) reservado para pruebas de este prototipo, que se configura con **OpenLiteSpeed + PHP + MySQL + Redis de forma nativa, sin Docker** — decisión registrada en `docs/adr/ADR-002.md` tras verificar que el entorno local ya usa MySQL (Laragon) y que un desarrollador solo, en 30 días, no gana nada agregando Docker ni PostgreSQL a lo que ya tiene funcionando.
- Un dominio ya comprado, disponible para usarse cuando el prototipo esté listo para mostrarse.
- Ninguno de los dos se usa como entorno de producción real todavía — el droplet cumple hoy, a la vez, el rol de staging y de "producción" del prototipo del sprint de 30 días (ver `docs/development/devops.md`).

---

## Hecho hasta ahora

- [x] Fase 1 completa y commiteada.
- [x] Roadmap ajustado tras investigación real: Mercado Libre se integra antes que TikTok Shop (TikTok Shop no tenía onboarding de vendedor local abierto en Colombia a la fecha de la investigación — re-validar en `docs/research/tiktok-shop.md` antes de construir esa integración).
- [x] Alcance del sprint de 30 días acordado: prototipo funcional, no producción.
- [x] Decisión de infraestructura tomada: MySQL en vez de PostgreSQL, despliegue nativo (sin Docker) en vez de contenedores, para todos los entornos de este prototipo — ver `docs/adr/ADR-002.md`.
- [x] Entorno local listo: PHP subido de 8.1.10 a **8.3.32** (instalado y configurado a mano dentro de Laragon, con las mismas extensiones que ya usaba la 8.1: pdo_mysql, mysqli, gd, mbstring, curl, openssl, zip, bcmath, intl, exif, fileinfo). MySQL 8.0.30 corriendo, Composer y Node confirmados. **Decisión operativa:** no usamos Apache/httpd de Laragon (dio conflicto de DLLs al cambiar de versión de PHP, típico de Apache+PHP en Windows, y de todas formas no lo necesitamos) — Laragon queda solo para MySQL; Laravel corre con su propio servidor (`php artisan serve`) y Next.js con el suyo (`npm run dev`).
- [x] `apps/api` creado (Laravel 13, PHP 8.3.32), conectado a MySQL (`proyecto_alfa`, migrado) y Redis (vía `predis`, porque la extensión nativa de Redis no está disponible en Windows). Corre con `php artisan serve` en `http://127.0.0.1:8000` — sin Apache.
- [x] `apps/web` creado (Next.js 16, TypeScript, Tailwind, App Router). Corre con `npm run dev` en `http://localhost:3000`.
- [x] `backend/` y `frontend/` (legacy, vacíos) eliminados — la migración a `apps/` que estaba pendiente en el README ya se hizo directamente, sin paso intermedio.
- [ ] Semana 1 — Cimientos (en curso): falta el modelo de datos núcleo con `tenant_id` (tenant, producto, categoría, pedido, cliente) y autenticación; y que `apps/web` consuma la API en vez de la página default de Next.js.
- [ ] Semana 2 — Tienda: catálogo público, carrito, checkout con pago simulado (pedido queda "pendiente de confirmación", el admin lo confirma a mano — sin pasarela de pago real).
- [ ] Semana 3 — Panel: CRUD de productos, gestión de pedidos, descuento de stock al confirmar un pedido.
- [ ] Semana 4 — Confiabilidad: cargar el catálogo real de skincare, pruebas end-to-end del flujo completo, corregir lo que rompa.

## Próximo paso concreto

`apps/api` (Laravel) y `apps/web` (Next.js) ya corren localmente y conectan a MySQL/Redis. Sigue: diseñar y migrar el modelo de datos núcleo con `tenant_id` (tenant, producto, categoría, pedido, cliente) siguiendo `docs/architecture/base-de-datos.md` y `docs/standards/database.md`, y montar autenticación básica.

### Cómo levantar el entorno en una sesión nueva

1. Laragon: solo necesitas que **MySQL** esté iniciado (no Apache).
2. Redis: `C:\laragon\bin\redis\redis-x64-5.0.14.1\redis-server.exe` (no lo inicia Laragon solo; hay que arrancarlo aparte).
3. API: `cd apps/api && php artisan serve --port=8000`
4. Web: `cd apps/web && npm run dev -- --port 3000`
5. La versión activa de PHP debe ser la 8.3.32 (`C:\laragon\bin\php\php-8.3.32-Win32-vs16-x64`), no la 8.1.10 vieja.

## Decisiones pendientes que no son técnicas

- Nombre de la tienda de skincare.
- Catálogo real de productos a cargar en la Semana 4.
