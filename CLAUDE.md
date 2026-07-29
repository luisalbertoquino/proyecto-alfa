# Proyecto Alfa — orientación para Claude Code

Plataforma de comercio electrónico inteligente para emprendedores (visión a futuro: SaaS multi-tenant). El repositorio está en fundación documental completa (Fase 1 terminada); ahora en Fase 2: construir un **prototipo funcional del piloto, no producción**, en un sprint de ~30 días.

## Lee esto primero, en este orden

1. **`docs/estado-actual.md`** — qué está hecho, qué sigue, y el contexto concreto del negocio piloto activo. Es lo primero que hay que leer en cualquier sesión nueva; ahí vive el "dónde vamos" para no reiniciar de cero.
2. `docs/business/documento-maestro.md` — resumen ejecutivo de negocio (5 minutos).
3. `docs/architecture/vision-tecnica.md` y `docs/architecture/principios-de-arquitectura.md` — decisiones técnicas ya tomadas. No proponer alternativas a esto sin que el usuario lo pida explícitamente.

Para profundizar en un tema puntual, la documentación completa vive en `docs/` (`architecture/`, `business/`, `design/`, `development/`, `research/`, `standards/`, `adr/`) y las plantillas reutilizables en `templates/`.

## Reglas rápidas (ya decididas, no reabrir sin pedirlo el usuario)

- Stack: Laravel (API) + Next.js (web/admin) + MySQL + Redis. Sin microservicios, sin otra base de datos. Despliegue nativo (sin Docker) en esta fase de prototipo: entorno local en Laragon, servidor de pruebas en un droplet con OpenLiteSpeed. Ver `docs/adr/ADR-001.md` y `docs/adr/ADR-002.md`.
- Monolito modular por dominio; toda tabla de negocio nueva lleva `tenant_id` desde el día uno, aunque hoy solo exista un tenant.
- Terminología obligatoria: **tenant/negocio** ≠ **cliente** (comprador final) ≠ **emprendedor** (quien opera el tenant) — ver `docs/business/diccionario-del-negocio.md`.
- Objetivo de estos 30 días: un prototipo **confiable y funcional**, no un despliegue en producción. No se integra pasarela de pago real, publicidad ni servicios pagos todavía.

## Al terminar un bloque de trabajo significativo

Actualiza `docs/estado-actual.md` (sección "Próximo paso concreto" y "Hecho hasta ahora") sin que haga falta que el usuario lo pida — es lo que mantiene la continuidad entre sesiones.

## Git: commit y push automáticos

El usuario autorizó explícitamente (2026-07-27) hacer `commit` y `push` a `origin/main` sin pedir permiso cada vez, al cerrar cada bloque de trabajo significativo (no en cada archivo suelto). Sigue aplicando el resto del protocolo de seguridad de git (nunca `--force`, nunca `--no-verify`, nunca reescribir historia, commits nuevos en vez de `--amend`).
