# Infraestructura

## Propósito

Explicar cómo se despliega Proyecto Alfa en su fase de prototipo (Fase 2): entorno local con Laragon, servidor de pruebas nativo sobre un Droplet de DigitalOcean con OpenLiteSpeed + MySQL + Redis (sin contenedores), cómo ocurre un despliegue a alto nivel, qué rol cumple Cloudflare, y qué rol cumple GitHub Actions. El detalle paso a paso del pipeline de CI/CD vive en `docs/development/ci-cd.md`; este documento explica el **porqué** de la topología de infraestructura actual, no cada comando del pipeline. La decisión de usar despliegue nativo en vez de Docker para esta fase está documentada en `docs/adr/ADR-002.md`.

---

## Objetivo

Que el entorno local de desarrollo y el servidor de pruebas compartan la misma base tecnológica (PHP, MySQL, Redis) para que "funciona en mi máquina" sea representativo de "funciona en el droplet", y que llevar un cambio de código al servidor de pruebas sea un procedimiento simple y repetible que un desarrollador solo pueda operar con confianza durante un sprint de 30 días, sin la sobrecarga operativa de una capa de contenedores que hoy no aporta valor medible.

---

## Alcance

Cubre: entorno local (Laragon), servidor de pruebas (droplet con despliegue nativo), definición de esos dos entornos y sus diferencias, flujo de despliegue a alto nivel, rol de Cloudflare, y rol de GitHub Actions como orquestador de CI/CD a alto nivel.

No cubre: el detalle exacto del pipeline (jobs, steps, secretos de GitHub Actions) — eso vive en `docs/development/ci-cd.md`; ni las palancas de escalado en sí (`escalabilidad.md`); ni la postura de seguridad de secretos e infraestructura (`seguridad.md`); ni la justificación completa de MySQL sobre PostgreSQL o de despliegue nativo sobre Docker — eso vive en `docs/adr/ADR-002.md`.

---

## Problema que resuelve

Sin una definición explícita de infraestructura, cada entorno termina configurado a mano, de forma distinta, y las diferencias entre "cómo corre en mi máquina" y "cómo corre en el servidor de pruebas" se convierten en la fuente más común de bugs que "no deberían pasar". Igualmente, un despliegue manual sin ningún procedimiento repetible es lento y propenso a error humano. Este documento fija Laragon en local, el droplet nativo como servidor de pruebas, y GitHub Actions como las piezas que reducen ambos problemas, con la complejidad justa para un prototipo operado por una sola persona — no la topología completa de múltiples entornos contenerizados que un equipo más grande necesitaría.

---

## Principios

1. **La misma base tecnológica en todos los entornos, aunque el empaquetado no sea idéntico.** Local y droplet corren PHP, MySQL y Redis de la misma versión mayor; las diferencias son de configuración (credenciales, dominio, recursos), no de motor.
2. **La infraestructura es lo más simple que el problema actual permite.** Para un prototipo de 30 días, sin equipo y sin tráfico de producción, un solo servidor nativo es suficiente — se añade complejidad (más servidores, contenedores) cuando haya una razón medible, no por adelantado (ver `docs/adr/ADR-002.md`).
3. **El droplet de pruebas cumple hoy el rol de staging y de "producción" del prototipo a la vez** (ver `docs/estado-actual.md`) — no existe todavía una separación de ambientes propia de un sistema en producción real con usuarios pagando.
4. **Cloudflare era el plan original para CDN/DNS/protección básica, pero el prototipo desplegado no lo usa todavía** — el SSL se maneja directo en el droplet (certbot/Let's Encrypt vía LiteSpeed) y el DNS está gestionado directo en el proveedor de dominio. Se retoma cuando el tráfico real lo justifique (ver "Rol de Cloudflare" más abajo); no es parte de la topología activa de la Fase 2.
5. **El despliegue es un procedimiento conocido y repetible**, aunque hoy sea manual o semi-manual — no comandos improvisados distintos cada vez.
6. **Contenerizar es una opción reservada, no descartada.** Se reevalúa en fases posteriores del roadmap (Fase 5 — SaaS) si el negocio llega a necesitar múltiples servidores o un equipo más grande que deba reproducir el mismo entorno.

---

## Reglas

### Entorno local — Laragon

- El entorno de desarrollo local corre sobre **Laragon (Windows)**, con **PHP 8.3.32, Composer, Node 22, MySQL 8.0.30 y Redis** instalados de forma nativa — no hay Docker ni Docker Compose en el flujo de trabajo local de esta fase. (PHP se actualizó desde la versión inicial 8.1.10, que se quedaba corta para el framework actual — ver `docs/estado-actual.md`.)
- Datos de prueba locales, servicios externos (transportadoras, marketplaces, proveedor de IA) en modo simulado/mock cuando sea posible, para no depender de credenciales reales ni afectar cuentas productivas.
- Un desarrollador nuevo (o el mismo desarrollador en una máquina nueva) reproduce el entorno instalando Laragon y las versiones fijadas arriba, siguiendo la guía de arranque del propio repositorio, en vez de levantar un stack de contenedores.

### Servidor de pruebas — Droplet nativo

- El servidor de pruebas es **un Droplet de DigitalOcean** configurado con **OpenLiteSpeed** como servidor web, y **PHP, MySQL y Redis instalados de forma nativa** sobre el sistema operativo del droplet — sin contenedores.
- Este droplet cumple hoy, a la vez, el rol de entorno de pruebas del prototipo y el único servidor accesible fuera de la máquina local (ver `docs/estado-actual.md`, sección "Infraestructura ya disponible") — no es un ambiente de producción con usuarios reales ni datos de clientes reales todavía.
- Las diferencias entre local y el droplet se manejan por variables de entorno y configuración (ver `seguridad.md` para manejo de secretos), nunca por una rama de código distinta.

### Flujo de despliegue (alto nivel)

- Un cambio se integra a la rama principal tras pasar el pipeline de CI (tests, linting, build) — el detalle exacto de jobs vive en `docs/development/ci-cd.md`.
- El despliegue al droplet consiste en, sobre el propio servidor: `git pull` de la rama principal, `composer install --no-dev` (backend), `npm run build` (frontend) y `php artisan migrate` para aplicar migraciones pendientes, siguiendo las reglas sin downtime de `base-de-datos.md`.
- Hoy este despliegue se ejecuta manualmente o mediante un script simple invocado a mano; GitHub Actions puede, más adelante, ejecutar ese mismo script por SSH contra el droplet al hacer merge a `main` (ver `docs/development/ci-cd.md`), sin que eso requiera introducir Docker.
- Todo despliegue al droplet queda registrado como mínimo por el propio historial de git (qué commit está desplegado) para trazabilidad básica, proporcional al tamaño del proyecto en esta fase.

### Rol de Cloudflare (planeado, no implementado en el prototipo actual)

- **Estado real (Fase 2):** el prototipo desplegado **no usa Cloudflare**. El SSL de los tres subdominios (`skincare.alegrarte.store`, `skincare-admin.alegrarte.store`, `skincare-api.alegrarte.store`) se emitió directo en el droplet con `certbot` en modo `standalone` (Let's Encrypt), y el DNS apunta directo del proveedor de dominio a la IP del droplet — sin proxy de Cloudflare de por medio. Ver `docs/estado-actual.md` para el procedimiento exacto.
- **Plan original, todavía válido como decisión futura:** cuando el tráfico real lo justifique, Cloudflare delante del droplet cumpliría DNS, CDN para contenido estático y páginas SSG/ISR, terminación TLS, y protección básica de tráfico anómalo — este rol no depende de si el backend corre en contenedores o nativo. No se implementa antes de tener una razón medible (mismo criterio que el resto de las decisiones de escalado, ver `escalabilidad.md`).

### Rol de GitHub Actions

- GitHub Actions orquesta, a alto nivel: validación de cada cambio (tests, linting, build) en cada Pull Request y en `main`; el despliegue automatizado al droplet por SSH es una extensión planeada, no obligatoria desde el día uno de esta fase.
- Detalle exacto de jobs, matrices de test, y configuración del pipeline: `docs/development/ci-cd.md`.

---

## Ejemplos

- Un desarrollador clona el repositorio, instala Laragon con PHP 8.1.10, MySQL 8.0.30, Redis y Node 22, corre `composer install`, `npm install` y `php artisan migrate`, y tiene el sistema corriendo localmente sin necesidad de ninguna herramienta de contenedores.
- Un cambio en el módulo `Envios` se integra a la rama principal; el pipeline de CI corre lint/tests/build; el desarrollador se conecta por SSH al droplet (o dispara el script de despliegue) y ejecuta `git pull`, `composer install --no-dev`, `npm run build` y `php artisan migrate` para llevar el cambio al servidor de pruebas.
- Hoy una visita a la tienda pública llega directo al droplet (sin Cloudflare de por medio, ver "Rol de Cloudflare" arriba); cuando se active, serviría contenido cacheado desde el borde antes de tocar el droplet.

---

## Casos límite

- **El droplet necesita un rollback tras un despliegue que rompe algo:** se vuelve a un commit anterior conocido-bueno y se repite el procedimiento de despliegue (`git pull` de ese commit, `composer install`, `migrate` si aplica) — ver `docs/development/devops.md` para el detalle de rollback, incluyendo el caso en que el problema es de datos y exige restaurar un backup de MySQL en vez de solo revertir código.
- **El tráfico del prototipo empieza a superar lo que un solo droplet puede sostener:** es la señal concreta para evaluar escalar a más de un servidor, y en ese punto reevaluar si contenerizar (Docker u otra herramienta) aporta valor — no antes, y no de forma preventiva (ver `docs/adr/ADR-002.md`, "Queda pendiente").
- **Caída del droplet:** hoy es un único servidor sin Cloudflare ni redundancia delante — una caída del droplet es una caída total del prototipo. El sistema debe al menos dejar claro el estado del servicio cuando vuelva (ver `vision-tecnica.md`, casos límite); riesgo aceptado explícitamente para un prototipo, no para producción.

---

## Decisiones futuras

- Si y cuándo contenerizar el despliegue (Docker u otra herramienta), evaluado en una fase posterior del roadmap (Fase 5 — SaaS) si el negocio llega a necesitar múltiples servidores o un equipo más grande — no comprometido hoy (ver `docs/adr/ADR-002.md`).
- Automatizar el despliegue nativo actual vía GitHub Actions ejecutando el script de despliegue por SSH contra el droplet, en vez de ejecutarlo a mano.
- Separar formalmente un entorno de staging del de "producción del prototipo" una vez el negocio piloto tenga tráfico real de clientes finales — hoy el droplet cumple ambos roles a la vez (ver `docs/estado-actual.md`).
- Herramienta de Infraestructura como Código (Terraform u otra) para versionar la configuración de Cloudflare y del droplet, si el crecimiento del proyecto lo justifica.
- Procedimiento formal y documentado de rollback más allá de lo descrito en `docs/development/devops.md`.

---

## Referencias

- `docs/adr/ADR-002.md` — decisión de MySQL y despliegue nativo en vez de PostgreSQL y Docker para esta fase.
- `docs/architecture/vision-tecnica.md` — principios de stack que esta infraestructura implementa.
- `docs/architecture/escalabilidad.md` — cómo escalaría el sistema si se necesitara más de un servidor.
- `docs/architecture/base-de-datos.md` — reglas de migraciones aplicadas durante el despliegue.
- `docs/development/ci-cd.md` — detalle paso a paso del pipeline de GitHub Actions.
- `docs/development/devops.md` — entornos, rollback y operación una vez desplegado.
- `docs/architecture/seguridad.md` — manejo de secretos en cada entorno.
- `docs/estado-actual.md` — infraestructura real ya disponible para el sprint de 30 días.

---

## Historial

- **2026-07-27** — Primera versión.
- **2026-07-27** — Actualizado: MySQL en vez de PostgreSQL y despliegue nativo en vez de Docker — ver ADR-002.
- **2026-07-31** — Actualizado tras el primer despliegue real: PHP local corregido a 8.3.32, y aclarado que Cloudflare **no** está implementado en el prototipo (el SSL y el DNS se manejan directo en el droplet) — quedaba documentado como si ya estuviera activo, y no lo estaba.
