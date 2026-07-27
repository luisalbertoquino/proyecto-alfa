# CI/CD

## Propósito

Definir el pipeline de integración y despliegue continuo con GitHub Actions: qué se ejecuta automáticamente en cada Pull Request, qué ocurre al hacer merge a `main`, cómo se despliega de forma nativa al droplet de pruebas (que hoy cumple el rol de staging y de "producción" del prototipo — ver `docs/estado-actual.md`), y cómo se manejan las migraciones de base de datos en el despliegue sin causar downtime.

---

## Objetivo

Que ningún cambio llegue a producción sin haber pasado por las mismas verificaciones automáticas, en el mismo orden, sin importar quién lo escribió ni qué tan urgente parezca — y que desplegar sea un evento rutinario y de bajo riesgo, no algo que dé miedo hacer seguido.

---

## Alcance

Cubre: los jobs de GitHub Actions que corren en PR y en merge a `main`, el flujo de despliegue nativo al droplet de pruebas, y la estrategia de migraciones de base de datos sin downtime.

No cubre: qué revisa un humano en el PR (`docs/development/coding-standards.md`), niveles y contenido de los tests (`docs/development/testing.md`), ni operación del sistema una vez desplegado — monitoreo, logs, rollback operativo (`docs/development/devops.md`), aunque el rollback de un despliegue fallido se menciona aquí en su aspecto de pipeline.

---

## Problema que resuelve

Sin un pipeline automatizado y obligatorio:

- "Correr los tests antes de mergear" depende de que cada persona se acuerde de hacerlo localmente, con su propia versión de dependencias, que puede no coincidir con producción.
- Desplegar se vuelve un procedimiento manual, propenso a error humano, que alguien tiene que ejecutar con cuidado en vez de un procedimiento repetible.
- Las migraciones de base de datos aplicadas a mano, en el momento del despliegue, son la causa más común de downtime evitable en sistemas que crecen.
- Sin verificaciones automáticas antes de tocar el droplet, el primer lugar donde se descubre un bug de integración es el propio servidor de pruebas que además sirve el prototipo al piloto.

---

## Principios

1. **Todo lo que se pueda verificar automáticamente, se verifica en el pipeline — nunca a mano antes de hacer push.** Lint, tests y build corren en GitHub Actions, no como paso opcional "en mi máquina funciona".
2. **El pipeline de PR y el pipeline de despliegue son distintos pero comparten los mismos checks base.** Lo que se verificó en el PR no se vuelve a cuestionar en el despliegue; el despliegue añade solo lo que es específico de desplegar (migraciones, build de producción).
3. **El droplet de pruebas se trata con la misma seriedad que un ambiente de producción**, aunque hoy cumpla ambos roles a la vez (ver `docs/estado-actual.md`): ningún cambio llega ahí sin pasar primero por el pipeline de verificación.
4. **Las migraciones de base de datos son compatibles hacia atrás por defecto.** Un despliegue nunca asume que el código nuevo y el código viejo no van a coexistir ni por un segundo — porque, con más de una instancia corriendo (arquitectura stateless, ver `vision-tecnica.md`), podrían coexistir durante un rollout, y porque un despliegue nativo puede quedar a mitad de camino si algo falla.
5. **Un despliegue fallido se revierte con un solo paso manual conocido** (volver al commit anterior y redesplegar), nunca con investigación de emergencia en caliente como primer recurso.
6. **El pipeline es el mismo monorepo, pero cada app (`apps/api`, `apps/web`, `apps/admin`) se construye y despliega de forma independiente cuando es posible** — un cambio solo en `apps/web` no debería requerir reconstruir la API.

---

## Reglas

### En cada Pull Request (workflow de CI)

Se ejecuta automáticamente al abrir o actualizar un PR contra `main`, con jobs paralelos por app afectada (usando path filters para no correr innecesariamente lo que el PR no toca):

1. **Lint / formato:** Pint + Larastan para `apps/api`; ESLint + Prettier + `tsc --noEmit` para `apps/web`/`apps/admin`. Falla el job si hay diferencias o errores de tipo.
2. **Tests unitarios y de integración:** suite completa del/los módulo(s) afectado(s) como mínimo; en la práctica corre la suite completa dado el tamaño actual del proyecto (ver `docs/development/testing.md` para niveles). Corre contra una base de datos MySQL y Redis efímeros levantados como servicios del propio workflow, con seeds multi-tenant (nunca un solo tenant).
3. **Build:** compilación de producción de cada app afectada (`apps/api` — validación de autoload/config cacheable; `apps/web`, `apps/admin` — `next build`). Un build que falla bloquea el PR aunque los tests hayan pasado.
4. **E2E de flujos críticos:** corre en el PR cuando el cambio toca un módulo relacionado a un flujo crítico (checkout, pagos, inventario/sincronización, autenticación multi-tenant — ver `testing.md`); en otro caso corre igual pero en un job no bloqueante informativo, y sí de forma bloqueante en el pipeline de merge a `main` (que es, hoy, el paso previo directo al despliegue al droplet).
5. **Reporte de cobertura:** se publica como comentario/check en el PR como referencia para la revisión humana (ver `coding-standards.md`), no como gate numérico duro.

Un PR con cualquiera de los pasos 1-4 en rojo no puede mergearse (branch protection lo exige como *required check*).

### Al hacer merge a `main`

1. Se repite la suite completa (lint + tests + build) sobre el estado ya mergeado de `main`, no solo sobre la rama — para atrapar el caso raro de un conflicto de merge que pasa en la rama pero rompe en `main`.
2. Corre la suite E2E completa de flujos críticos, siempre, sin importar si el PR la disparó como bloqueante o no.
3. Si todo pasa, un job final de despliegue se conecta por SSH al droplet de pruebas y ejecuta el despliegue nativo: `git pull` de `main`, `composer install --no-dev`, `npm run build` y `php artisan migrate`.
4. Este despliegue automático al droplet requiere, mientras el proyecto esté en esta fase de prototipo con un solo desarrollador, una aprobación manual explícita antes de ejecutarse (un "gate" de GitHub Actions Environments) — dado que el droplet cumple hoy también el rol de "producción" del prototipo (ver `docs/estado-actual.md`), no hay un ambiente de staging separado donde validar primero sin ese cuidado.
5. Toda migración destructiva sigue la misma regla de aprobación explícita descrita más abajo, sin excepción por tratarse de un despliegue automatizado.

### Despliegue nativo al droplet

- El despliegue no publica ni promueve ninguna imagen: el propio job de GitHub Actions ejecuta, por SSH, la secuencia `git pull` → `composer install --no-dev` → `npm run build` → `php artisan migrate` directamente sobre el droplet — el mismo procedimiento que, mientras no esté automatizado, un desarrollador ejecuta a mano (ver `docs/architecture/infraestructura.md`).
- No existe hoy un entorno de staging separado del droplet de pruebas: ese único servidor recibe el cambio ya validado por el pipeline de PR y de `main`, que es la verificación equivalente a lo que un staging separado ofrecería en una topología con más de un servidor.
- El despliegue reinicia los procesos de PHP-FPM/OpenLiteSpeed necesarios para que el código nuevo entre en efecto; al no haber múltiples instancias detrás de un balanceador en esta fase, no hay rolling update — hay una breve ventana de despliegue, aceptable para un prototipo sin SLA de disponibilidad.
- Cada despliegue al droplet queda registrado con: commit SHA y hora, a través del propio log del job de GitHub Actions.

### Migraciones de base de datos sin downtime

- Toda migración se escribe para ser **compatible con el código de la versión anterior**, tanto por la breve ventana del propio despliegue como por la posibilidad de necesitar un rollback (ver `docs/development/devops.md`). En la práctica esto significa:
  - Agregar una columna: siempre nullable o con default, nunca `NOT NULL` sin default en el mismo paso que la agrega.
  - Eliminar una columna o tabla: se hace en dos despliegues separados — primero un despliegue que deja de usarla en código, después (en un despliegue posterior) la migración que la elimina físicamente.
  - Renombrar una columna: se trata como "agregar + migrar datos + dejar de usar la vieja + eliminar la vieja", nunca como un `RENAME` directo que rompería al código viejo todavía corriendo.
  - Cambiar el tipo de una columna con datos existentes: se hace de forma expansiva (columna nueva, backfill, corte, columna vieja eliminada después), no con un `ALTER` bloqueante en el camino directo.
- Las migraciones (`php artisan migrate`) se ejecutan como parte del propio script/job de despliegue, después de `composer install`/`npm run build` y antes de dar por terminado el despliegue, para que el código nuevo y el esquema nuevo lleguen juntos.
- Toda migración nueva se prueba primero localmente (Laragon) con datos representativos antes de aprobarse para el droplet; una migración que tarda más de lo esperado localmente no se aprueba para el droplet hasta investigarse.
- Ninguna migración destructiva (`DROP TABLE`, `DROP COLUMN`, cambios de tipo no expansivos) corre sin la misma aprobación manual explícita que el resto del despliegue al droplet, señalada en el PR.

---

## Ejemplos

- Un PR modifica solo `apps/admin`: el workflow de CI, por path filters, no reconstruye ni prueba `apps/api`; solo corre lint/tests/build de `apps/admin`. El PR se mergea, el despliegue al droplet solo actualiza lo necesario para la app `admin`.
- Se agrega la columna `canal_origen` a la tabla de pedidos: la migración la agrega como nullable con default `null`; el código nuevo la usa, y si hiciera falta un rollback, el código anterior simplemente la ignora sin romperse. Un PR posterior, ya con el cambio estable, la vuelve `NOT NULL` si hace falta.
- Un cambio en `Inventario` se aprueba para desplegarse al droplet después de confirmarse localmente con datos de dos tenants de prueba que la sincronización sigue funcionando sin sobreventa.

---

## Casos límite

- **Un despliegue al droplet falla a mitad de camino** (ej. `composer install` o `npm run build` falla, o una migración destructiva sale mal): el script de despliegue se detiene sin completar el `git pull`/build de forma parcial en el servidor; el equipo investiga con el pipeline ya detenido y, si hace falta, aplica el procedimiento de rollback de `docs/development/devops.md` (volver a un commit anterior y redesplegar).
- **Una migración necesaria es inherentemente lenta** (ej. backfill de millones de filas): se ejecuta en lotes, fuera del paso bloqueante de despliegue si es posible (job en cola, ver `vision-tecnica.md`), y el despliegue de código que depende del backfill espera a que termine antes de activarse (feature flag), no antes de completar la migración de esquema.
- **Se necesita desplegar al droplet algo urgente sin la observación habitual** (ej. hotfix): igual pasa por el pipeline de lint/tests/build, aunque sea brevemente, porque saltarse esa verificación es exactamente el tipo de atajo que este documento existe para evitar, más aún cuando el droplet cumple también el rol de "producción" del prototipo.
- **El repositorio sigue en transición `backend/`/`frontend/` → `apps/`:** el pipeline referencia ambas ubicaciones mientras dure la migración, y un ADR documenta el corte cuando la migración de cada app se dé por completa, momento en el que el workflow deja de referenciar la ruta vieja.

---

## Decisiones futuras

- Automatizar por completo el job de despliegue por SSH al droplet (hoy puede seguir siendo manual mientras se termina de configurar), y quitar la aprobación manual una vez exista suficiente confianza en la suite E2E y en el historial de despliegues estables.
- Separar un entorno de staging del "producción del prototipo" cuando el negocio piloto tenga tráfico real de clientes finales que justifique no arriesgar el único servidor disponible (ver `docs/estado-actual.md`).
- Evaluar si contenerizar el despliegue aporta valor cuando exista más de un servidor (ver `docs/adr/ADR-002.md`) — hoy no está en el camino crítico de este pipeline.
- Definir umbral de tiempo de despliegue aceptable y alertas si un despliegue se queda a medias más de X minutos.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — stateless por defecto, trabajo pesado en cola.
- `docs/development/testing.md` — qué prueba cada nivel y qué flujos son críticos.
- `docs/development/coding-standards.md` — qué bloquea un PR antes de llegar a este pipeline.
- `docs/development/devops.md` — entornos, monitoreo y rollback operativo una vez desplegado.
- `docs/architecture/infraestructura.md` — detalle de infraestructura de despliegue nativo al droplet.
- `docs/adr/ADR-002.md` — decisión de despliegue nativo en vez de Docker para esta fase.

---

## Historial

- **2026-07-27** — Primera versión.
- **2026-07-27** — Actualizado: MySQL en vez de PostgreSQL y despliegue nativo en vez de Docker — ver ADR-002.
