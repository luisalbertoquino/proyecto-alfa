# Git Workflow

## Propósito

Definir cómo el equipo usa Git para que el historial, las ramas y los merges sean predecibles para cualquier persona que se una al proyecto, sin depender de que cada quien recuerde "cómo se hacía aquí". Este documento fija el **proceso**: estrategia de ramas, cuándo y cómo se integra código, y qué protege `main`. Los nombres exactos de ramas y el formato de mensajes de commit se definen en `docs/standards/` (en construcción por otro colaborador); este documento no los contradice y los referencia donde aplica.

---

## Objetivo

Que `main` esté siempre en estado desplegable, que integrar el trabajo de varias personas en el mismo módulo (o en módulos distintos) sea frecuente y de bajo riesgo, y que el costo de "juntar" ramas no crezca a medida que el equipo crece.

---

## Alcance

Cubre: estrategia de ramas para `apps/api`, `apps/web`, `apps/admin`, `packages/` y el resto del monorepo; cuándo se abre una rama; cómo y cuándo se integra a `main`; qué protecciones tiene `main`; manejo de releases a producción a nivel de Git (no de despliegue — eso es `docs/development/ci-cd.md`).

No cubre: convenciones de nombres de ramas/commits (`docs/standards/`), checklist de revisión de código (`docs/development/coding-standards.md`), qué se ejecuta en CI (`docs/development/ci-cd.md`).

---

## Problema que resuelve

Sin una estrategia de ramas explícita, un equipo que crece termina con alguno de estos problemas:

- Ramas de feature que viven semanas, se alejan de `main` y su merge se vuelve un evento de alto riesgo con conflictos masivos.
- Nadie sabe si `main` en un momento dado está en un estado desplegable o a medio terminar.
- Distintas personas resuelven "¿cuándo hago merge?" de forma distinta, y el historial se vuelve inconsistente.
- Un modelo de ramas pensado para un equipo grande (GitFlow completo, con `develop`, `release/*`, `hotfix/*`) impone ceremonia que un equipo de una a tres personas no necesita y que en la práctica nadie sigue con disciplina.

---

## Principios

1. **Trunk-based development con ramas cortas de feature.** Se elige sobre GitFlow por el tamaño actual del equipo (reducido, un negocio piloto) y porque el objetivo de la arquitectura es desplegar seguido y con bajo riesgo (ver `vision-tecnica.md`, principio de trabajo asíncrono y stateless para escalar). GitFlow completo (con `develop` de larga vida, `release/*`, `hotfix/*`) agrega ceremonia que solo se justifica en equipos grandes con ciclos de release calendarizados; no es el caso de Proyecto Alfa hoy, y migrar de trunk-based a algo más elaborado es más barato que lo inverso.
2. **`main` es siempre desplegable.** Ningún commit en `main` deja el sistema en un estado roto. Si algo llega a romper `main`, se revierte antes de seguir agregando trabajo encima.
3. **Las ramas de feature viven poco tiempo (idealmente menos de 2-3 días).** Cuanto más corta la rama, menor el riesgo de conflicto y más fácil la revisión. Una feature grande se descompone en varios PRs pequeños e integrables por separado, no en una rama larga.
4. **Integrar seguido, no "cuando esté perfecto".** Se prefiere mergear código incompleto pero correcto detrás de una bandera de funcionalidad (feature flag) o sin exponer en UI, antes que acumular cambios sin integrar.
5. **El merge a `main` requiere que el PR esté verde y revisado**, nunca "confío en que funciona".
6. **`main` está protegida técnicamente, no por convención.** Las reglas de esta sección se aplican con protección de rama en GitHub, no con la expectativa de que nadie haga push directo.

---

## Reglas

- Toda rama de feature nace de `main` actualizado y se nombra según `docs/standards/` (pendiente; hasta que exista, usar `tipo/descripcion-corta`, ej. `feat/inventario-alertas-stock`).
- Ninguna rama de feature vive más de una semana sin al menos abrir un PR (aunque sea en borrador) para visibilidad del equipo.
- Todo cambio a `main` entra por Pull Request. Push directo a `main` está deshabilitado a nivel de configuración del repositorio (branch protection de GitHub).
- Un PR no se mergea a `main` si:
  - No pasa el pipeline de CI (lint, tests, build — ver `docs/development/ci-cd.md`).
  - No tiene al menos una aprobación de revisión de código (ver `docs/development/coding-standards.md`).
  - Tiene conflictos sin resolver contra `main`.
- El merge a `main` se hace por **squash merge**: el historial de commits "wip", "fix typo", "otro intento" de la rama de feature no se preserva en `main`; el PR se condensa en un commit (o unos pocos commits lógicos) con mensaje claro del qué y el porqué.
- Después de mergear, la rama de feature se elimina. Ramas obsoletas no se acumulan en el remoto.
- Un `hotfix` a producción sigue el mismo camino que cualquier otro cambio: rama corta desde `main`, PR, CI verde, revisión, merge, despliegue. No existe un atajo que salte CI o revisión para "es urgente" — un fallo en producción no se arregla introduciendo un segundo fallo sin revisar.
- Cambios de arquitectura relevantes (ver `vision-tecnica.md`) se documentan como ADR en `docs/adr/` **antes** de abrir el PR que los implementa, no después.

---

## Ejemplos

- Se va a implementar sincronización de inventario con TikTok Shop (módulo `Canales`). En vez de una rama `feat/tiktok-shop-integration` que viva tres semanas, el trabajo se parte en PRs sucesivos: (1) `TransportadoraInterface`-equivalente para canales (`MarketplaceInterface`) sin implementación real, (2) adaptador concreto de TikTok Shop detrás de esa interfaz, (3) job en cola que consume el adaptador, (4) exposición en el panel admin. Cada PR es revisable en minutos y `main` nunca queda en un estado a medio construir sin controlar.
- Un desarrollador termina una feature el viernes pero el reviewer no está disponible hasta el lunes: la rama simplemente espera con el PR abierto: no se mergea sin revisión "porque ya es viernes".
- Se detecta un bug de sobreventa en `Inventario` en producción: se crea una rama corta desde `main`, se abre PR con el fix y un test que reproduce el bug, pasa CI, se revisa, se mergea con squash, se despliega igual que cualquier cambio normal (ver `ci-cd.md`).

---

## Casos límite

- **Dos features tocan el mismo módulo en paralelo** (ej. dos personas en `Pedidos`): al mantener las ramas cortas y mergear seguido, el costo de resolver conflictos se mantiene bajo porque nunca hay más de un par de días de divergencia. Si el conflicto es de diseño (no solo de texto), se resuelve conversando antes de forzar un merge.
- **Una feature grande no cabe en PRs pequeños de forma obvia** (ej. un módulo de dominio nuevo completo): se descompone igual siguiendo la estructura de `templates/nuevo-modulo.md` — interfaz primero, implementación después, exposición al final — en vez de justificar una rama larga.
- **Se necesita revertir un merge ya en `main`:** se revierte con un commit de revert (`git revert`), nunca reescribiendo el historial de `main` con `push --force` o `reset --hard`, porque eso rompe el trabajo de cualquiera que ya haya hecho pull.
- **El repositorio sigue en transición `backend/` + `frontend/` → `apps/`:** mientras dure la migración, los PRs que muevan código de una estructura a otra se mantienen separados de los PRs que cambian comportamiento, para que un `git blame` o un revert no mezcle "se movió de carpeta" con "se cambió qué hace".

---

## Decisiones futuras

- Adoptar `release/*` o tags de versión formales cuando exista más de un cliente externo consumiendo la API (app móvil, integraciones de terceros del modelo SaaS) y se necesite congelar una versión mientras se desarrolla la siguiente.
- Definir política de feature flags (herramienta y convención) cuando el volumen de features "a medio exponer" lo justifique.
- Evaluar CODEOWNERS por módulo de dominio cuando el equipo crezca lo suficiente para que la revisión ya no pueda ser "cualquiera revisa cualquier cosa".

---

## Referencias

- `docs/architecture/vision-tecnica.md` — principios de arquitectura que este flujo de trabajo sirve (desplegar seguido, bajo riesgo).
- `docs/development/coding-standards.md` — qué exige una revisión de código antes de aprobar un PR.
- `docs/development/ci-cd.md` — qué corre en cada PR y qué pasa al mergear a `main`.
- `docs/standards/` (en construcción) — convenciones exactas de nombres de rama y de commits.
- `docs/adr/` — registro de decisiones de arquitectura.

---

## Historial

- **2026-07-27** — Primera versión.
