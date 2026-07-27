# CI/CD

## Propósito

Definir el pipeline de integración y despliegue continuo con GitHub Actions: qué se ejecuta automáticamente en cada Pull Request, qué ocurre al hacer merge a `main`, cómo se despliega a staging y a producción, y cómo se manejan las migraciones de base de datos en el despliegue sin causar downtime.

---

## Objetivo

Que ningún cambio llegue a producción sin haber pasado por las mismas verificaciones automáticas, en el mismo orden, sin importar quién lo escribió ni qué tan urgente parezca — y que desplegar sea un evento rutinario y de bajo riesgo, no algo que dé miedo hacer seguido.

---

## Alcance

Cubre: los jobs de GitHub Actions que corren en PR y en merge a `main`, el flujo de despliegue a staging y producción, y la estrategia de migraciones de base de datos sin downtime.

No cubre: qué revisa un humano en el PR (`docs/development/coding-standards.md`), niveles y contenido de los tests (`docs/development/testing.md`), ni operación del sistema una vez desplegado — monitoreo, logs, rollback operativo (`docs/development/devops.md`), aunque el rollback de un despliegue fallido se menciona aquí en su aspecto de pipeline.

---

## Problema que resuelve

Sin un pipeline automatizado y obligatorio:

- "Correr los tests antes de mergear" depende de que cada persona se acuerde de hacerlo localmente, con su propia versión de dependencias, que puede no coincidir con producción.
- Desplegar se vuelve un procedimiento manual, propenso a error humano, que alguien tiene que ejecutar con cuidado en vez de un botón confiable.
- Las migraciones de base de datos aplicadas a mano, en el momento del despliegue, son la causa más común de downtime evitable en sistemas que crecen.
- Sin un entorno de staging que refleje producción, el primer lugar donde se descubre un bug de integración es producción misma.

---

## Principios

1. **Todo lo que se pueda verificar automáticamente, se verifica en el pipeline — nunca a mano antes de hacer push.** Lint, tests y build corren en GitHub Actions, no como paso opcional "en mi máquina funciona".
2. **El pipeline de PR y el pipeline de despliegue son distintos pero comparten los mismos checks base.** Lo que se verificó en el PR no se vuelve a cuestionar en el despliegue; el despliegue añade solo lo que es específico de desplegar (migraciones, build de producción, smoke tests).
3. **Staging es un reflejo real de producción**, no un entorno aparte con datos y configuración distintas — su propósito es que un problema se vea ahí antes que en producción.
4. **Las migraciones de base de datos son compatibles hacia atrás por defecto.** Un despliegue nunca asume que el código nuevo y el código viejo no van a coexistir ni por un segundo — porque, con más de una instancia corriendo (arquitectura stateless, ver `vision-tecnica.md`), sí van a coexistir durante el rollout.
5. **Un despliegue fallido se revierte automáticamente o con un solo paso manual conocido**, nunca con investigación de emergencia en caliente como primer recurso.
6. **El pipeline es el mismo monorepo, pero cada app (`apps/api`, `apps/web`, `apps/admin`) se construye y despliega de forma independiente** — un cambio solo en `apps/web` no reconstruye ni redespliega la API.

---

## Reglas

### En cada Pull Request (workflow de CI)

Se ejecuta automáticamente al abrir o actualizar un PR contra `main`, con jobs paralelos por app afectada (usando path filters para no correr innecesariamente lo que el PR no toca):

1. **Lint / formato:** Pint + Larastan para `apps/api`; ESLint + Prettier + `tsc --noEmit` para `apps/web`/`apps/admin`. Falla el job si hay diferencias o errores de tipo.
2. **Tests unitarios y de integración:** suite completa del/los módulo(s) afectado(s) como mínimo; en la práctica corre la suite completa dado el tamaño actual del proyecto (ver `docs/development/testing.md` para niveles). Corre contra una base de datos PostgreSQL y Redis efímeros levantados como servicios del propio workflow, con seeds multi-tenant (nunca un solo tenant).
3. **Build:** compilación de producción de cada app afectada (`apps/api` — validación de autoload/config cacheable; `apps/web`, `apps/admin` — `next build`). Un build que falla bloquea el PR aunque los tests hayan pasado.
4. **E2E de flujos críticos:** corre en el PR cuando el cambio toca un módulo relacionado a un flujo crítico (checkout, pagos, inventario/sincronización, autenticación multi-tenant — ver `testing.md`); en otro caso corre igual pero en un job no bloqueante informativo, y sí de forma bloqueante en el pipeline de merge a `main`.
5. **Reporte de cobertura:** se publica como comentario/check en el PR como referencia para la revisión humana (ver `coding-standards.md`), no como gate numérico duro.

Un PR con cualquiera de los pasos 1-4 en rojo no puede mergearse (branch protection lo exige como *required check*).

### Al hacer merge a `main`

1. Se repite la suite completa (lint + tests + build) sobre el estado ya mergeado de `main`, no solo sobre la rama — para atrapar el caso raro de un conflicto de merge que pasa en la rama pero rompe en `main`.
2. Corre la suite E2E completa de flujos críticos, siempre, sin importar si el PR la disparó como bloqueante o no.
3. Si todo pasa, se construyen y publican las imágenes Docker de cada app afectada, etiquetadas con el SHA del commit.
4. Se dispara automáticamente el despliegue a **staging**.
5. El despliegue a **producción** no es automático: requiere una aprobación manual explícita (un "gate" de GitHub Actions Environments) después de verificar staging — ver más abajo.

### Despliegue a staging y producción

- **Staging** se actualiza automáticamente con cada merge a `main`. Usa la misma configuración de infraestructura que producción (Docker, mismas variables salvo credenciales y dominio), con datos de prueba multi-tenant representativos, nunca una copia de datos reales de clientes.
- **Producción** se despliega manualmente disparando el mismo workflow ya validado en staging (mismo artefacto/imagen, no una reconstrucción), tras aprobación de una persona designada (usando GitHub Environments con *required reviewers*). No se reconstruye código para producción — se promueve exactamente lo que ya corrió en staging, para eliminar la variable de "compiló distinto".
- El despliegue es por contenedor (Docker): se publican imágenes nuevas y se reemplazan las instancias en ejecución sin downtime (rolling update), aprovechando que los servidores de aplicación son stateless (`vision-tecnica.md`).
- Cada despliegue a producción queda registrado con: commit SHA, quién aprobó, y hora — sin necesidad de proceso adicional porque lo genera el propio Environment de GitHub Actions.

### Migraciones de base de datos sin downtime

- Toda migración se escribe para ser **compatible con el código de la versión anterior todavía en ejecución** durante el tiempo que dure el rolling update. En la práctica esto significa:
  - Agregar una columna: siempre nullable o con default, nunca `NOT NULL` sin default en el mismo paso que la agrega.
  - Eliminar una columna o tabla: se hace en dos despliegues separados — primero un despliegue que deja de usarla en código, después (en un despliegue posterior) la migración que la elimina físicamente.
  - Renombrar una columna: se trata como "agregar + migrar datos + dejar de usar la vieja + eliminar la vieja", nunca como un `RENAME` directo que rompería al código viejo todavía corriendo.
  - Cambiar el tipo de una columna con datos existentes: se hace de forma expansiva (columna nueva, backfill, corte, columna vieja eliminada después), no con un `ALTER` bloqueante en el camino directo.
- Las migraciones se ejecutan **antes** de que el tráfico se enrute a los contenedores con el código nuevo, como paso propio del pipeline de despliegue (no dentro del arranque de cada contenedor, para evitar que varias instancias intenten migrar a la vez).
- Toda migración nueva se prueba en staging con el volumen de datos representativo antes de aprobarse para producción; una migración que tarda más de lo esperado en staging no se aprueba para producción hasta investigarse.
- Ninguna migración destructiva (`DROP TABLE`, `DROP COLUMN`, cambios de tipo no expansivos) corre automáticamente: requiere la misma aprobación manual que el despliegue a producción, explícitamente señalada en el PR.

---

## Ejemplos

- Un PR modifica solo `apps/admin`: el workflow de CI, por path filters, no reconstruye ni prueba `apps/api`; solo corre lint/tests/build de `apps/admin`. El PR se mergea, staging se actualiza solo en la app `admin`.
- Se agrega la columna `canal_origen` a la tabla de pedidos: la migración la agrega como nullable con default `null`; el código nuevo la usa, el código viejo (durante el rollout) simplemente la ignora sin romperse. Un PR posterior, ya con el rollout completo y estable, la vuelve `NOT NULL` si hace falta.
- Un despliegue a producción de un cambio en `Inventario` se aprueba después de confirmarse en staging con datos de dos tenants de prueba que la sincronización sigue funcionando sin sobreventa.

---

## Casos límite

- **Un despliegue a producción falla a mitad de rollout:** el orquestador de contenedores detiene el rollout y mantiene las instancias viejas sirviendo tráfico (rollback automático a nivel de infraestructura); el equipo investiga con el pipeline ya detenido, no con producción a medio actualizar. Detalle operativo de rollback en `docs/development/devops.md`.
- **Una migración necesaria es inherentemente lenta** (ej. backfill de millones de filas): se ejecuta en lotes, fuera del paso bloqueante de despliegue si es posible (job en cola, ver `vision-tecnica.md`), y el despliegue de código que depende del backfill espera a que termine antes de activarse (feature flag), no antes de completar la migración de esquema.
- **Se necesita desplegar a producción algo que aún no pasó por staging el tiempo suficiente para confianza** (ej. hotfix urgente): igual pasa por staging, aunque sea brevemente, porque saltarse staging es exactamente el tipo de atajo que este documento existe para evitar; "urgente" reduce el tiempo de observación en staging, no lo elimina.
- **El repositorio sigue en transición `backend/`/`frontend/` → `apps/`:** el pipeline referencia ambas ubicaciones mientras dure la migración, y un ADR documenta el corte cuando la migración de cada app se dé por completa, momento en el que el workflow deja de referenciar la ruta vieja.

---

## Decisiones futuras

- Definir la herramienta concreta de orquestación de contenedores en producción (Docker Compose sobre un solo host vs. Swarm/Kubernetes) según el volumen real de tráfico y tenants — hoy no está decidida más allá de "Docker" (ver `docs/architecture/infraestructura.md`, en construcción).
- Automatizar la promoción de staging a producción sin aprobación manual una vez exista suficiente confianza en la suite E2E y en el historial de despliegues estables.
- Definir umbral de tiempo de rollout aceptable y alertas si un despliegue se queda a medias más de X minutos.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — stateless por defecto, trabajo pesado en cola (base de por qué el despliegue puede ser rolling sin downtime).
- `docs/development/testing.md` — qué prueba cada nivel y qué flujos son críticos.
- `docs/development/coding-standards.md` — qué bloquea un PR antes de llegar a este pipeline.
- `docs/development/devops.md` — entornos, monitoreo y rollback operativo una vez desplegado.
- `docs/architecture/infraestructura.md` (en construcción) — detalle de infraestructura de despliegue.

---

## Historial

- **2026-07-27** — Primera versión.
