# Coding Standards

## Propósito

Fijar el **proceso** que garantiza un nivel mínimo de calidad de código antes de que llegue a `main`: qué revisión es obligatoria, qué cobertura de tests se exige para considerar algo "terminado", qué se automatiza con linters/formatters, y qué bloquea un Pull Request. Este documento no define convenciones de nombres, estructura de carpetas o formato exacto de código — eso vive en `docs/standards/` (en construcción por otro colaborador) y en `docs/architecture/principios-de-arquitectura.md`.

---

## Objetivo

Que la calidad de cualquier cambio que entra a `main` no dependa de la disciplina individual de quien lo escribió, sino de un proceso que la verifica automáticamente donde es posible, y por revisión humana donde la automatización no alcanza.

---

## Alcance

Cubre: revisión de código obligatoria, cobertura mínima de tests para "terminado", linters y formatters automáticos, y condiciones que bloquean un PR, aplicado a `apps/api`, `apps/web`, `apps/admin` y `packages/`.

No cubre: convenciones concretas de nombres (`docs/standards/naming.md`), estrategia detallada de pruebas por nivel (`docs/development/testing.md`), qué ejecuta el pipeline paso a paso (`docs/development/ci-cd.md`), ni principios de diseño de módulos (`docs/architecture/principios-de-arquitectura.md`).

---

## Problema que resuelve

"Revisar bien" y "no olvidar los tests" son buenas intenciones que se degradan bajo presión de tiempo, sobre todo cuando el equipo crece y ya no todos conocen todo el sistema. Sin reglas explícitas y automatizadas:

- El estilo de código diverge entre módulos y entre personas, y cada Pull Request gasta tiempo de revisión discutiendo formato en vez de lógica.
- "Terminado" significa algo distinto para cada desarrollador — algunos prueban, otros no, y nadie lo nota hasta que falla en producción.
- La revisión de código se vuelve opcional en la práctica ("es un cambio chico, lo mergeo directo") y termina fallando justo en el cambio que parecía chico.
- El costo de mantener el código crece con cada persona nueva que no conoce las reglas tácitas del equipo.

---

## Principios

1. **Automatizar antes que pedir disciplina.** Todo lo que un linter, formatter o analizador estático pueda verificar, lo verifica una herramienta en CI — no un humano releyendo el diff buscando errores de estilo.
2. **Ningún PR se mergea sin al menos una revisión humana de otra persona.** El linter valida forma; la revisión humana valida que la solución sea correcta y encaje en la arquitectura (fronteras de módulo, ver `principios-de-arquitectura.md`).
3. **"Terminado" incluye tests, no es un paso aparte.** Un PR que agrega comportamiento nuevo sin su test correspondiente no está terminado, está a medias, sin importar qué tan bien funcione en la demo.
4. **El pipeline de CI decide objetivamente si un PR puede mergearse; la persona revisora decide si debe.** Ambas condiciones son necesarias — CI verde sin revisión no es suficiente, y revisión aprobada con CI en rojo tampoco.
5. **Las reglas se aplican igual a todos los módulos y a todo el equipo, incluida la fundadora/el fundador del proyecto.** No hay atajo de "es un cambio mío, no necesito revisión".

---

## Reglas

### Automatización (linters / formatters)

- `apps/api` (Laravel/PHP): un formateador automático (ej. Laravel Pint) y un analizador estático (ej. PHPStan/Larastan) corren en cada PR. El formateador se puede ejecutar en modo autofix localmente antes de commitear; en CI corre en modo verificación (falla si hay diferencias sin aplicar).
- `apps/web`, `apps/admin` (Next.js/TypeScript): ESLint + Prettier corren en cada PR en modo verificación. TypeScript en modo estricto; `any` implícito no compila.
- Los hooks de Git (pre-commit) pueden ejecutar el formateador localmente como conveniencia, pero **nunca reemplazan la verificación en CI** — un desarrollador sin el hook instalado no puede saltarse la regla.
- La configuración de linters vive versionada en el repositorio (no en configuración local de cada editor), para que el resultado sea el mismo en cualquier máquina y en CI.

### Revisión de código obligatoria

- Todo PR requiere mínimo una aprobación antes de mergear a `main`, exigida por configuración de branch protection en GitHub, no por acuerdo verbal.
- Quien abre el PR no puede aprobar su propio PR.
- La persona que revisa verifica, además de que el código "funcione":
  - Que respeta las fronteras de módulo de `principios-de-arquitectura.md` (no importa clases internas de otro módulo, usa interfaces de servicio).
  - Que la lógica de negocio no quedó en el controlador.
  - Que si el cambio toca datos de negocio, la tabla o consulta nueva respeta `tenant_id` (ver `testing.md` y `vision-tecnica.md`).
  - Que el PR trae los tests que le corresponden según su alcance (ver más abajo).
- Un PR marcado como borrador (`draft`) puede recibir comentarios pero no se mergea hasta salir de borrador y cumplir el resto de reglas.

### Cobertura mínima para considerar "terminado"

- Ningún PR que agregue o modifique lógica de negocio se considera terminado sin al menos un test que cubra su flujo principal (regla heredada de `principios-de-arquitectura.md`).
- Un PR que solo cambia estilo, documentación, o configuración sin efecto en comportamiento no requiere test nuevo, pero tampoco puede introducir cambios de comportamiento "aprovechando" esa etiqueta.
- El detalle de qué tipo de test corresponde a qué tipo de cambio (unitario, integración, end-to-end) y las reglas específicas de multi-tenant y de flujos críticos vive en `docs/development/testing.md` — este documento solo fija que la revisión y el CI lo exigen como condición de "terminado", no la mecánica de cómo probar.
- La cobertura de código (porcentaje medido por herramienta) se reporta en cada PR como referencia, pero no es en sí misma el criterio de aceptación — un módulo puede tener 100% de cobertura y no probar el caso multi-tenant que importa. El criterio real es el de `testing.md`.

### Qué bloquea un PR

Un PR **no puede mergearse** si ocurre cualquiera de estos casos:

- El pipeline de CI falla (lint, tests, build) — ver `docs/development/ci-cd.md`.
- No tiene ninguna aprobación de revisión.
- Tiene comentarios de revisión marcados como bloqueantes sin resolver.
- Agrega lógica de negocio sin su test de flujo principal.
- Agrega o modifica una tabla o consulta de datos de negocio sin `tenant_id`, o un test que no verifique el caso de más de un tenant cuando el cambio es multi-tenant por naturaleza.
- Introduce un `import`/`use` que cruza directamente al namespace interno de otro módulo (violación de fronteras de `principios-de-arquitectura.md`), salvo que el PR sea justamente el que define la interfaz de servicio correspondiente.

---

## Ejemplos

- Un PR agrega un endpoint nuevo en `Pedidos` para cancelar una orden. El linter valida estilo automáticamente. El PR incluye un test que verifica que cancelar una orden del tenant A no afecta al tenant B. La persona revisora confirma que la regla de negocio de cancelación vive en un servicio, no en el controlador, y aprueba. CI está verde. Se mergea.
- Un PR "solo mueve archivos" de `backend/` a `apps/api/` como parte de la migración a monorepo: no requiere test nuevo porque no cambia comportamiento, pero el reviewer confirma que efectivamente no cambió comportamiento (no se coló un cambio de lógica disfrazado de mover archivos).
- Un PR pasa CI en verde pero el reviewer detecta que el nuevo código de `Inventario` accede directamente al modelo `Stock` desde el módulo `Pedidos`: se bloquea aunque CI esté verde, porque viola fronteras de módulo.

---

## Casos límite

- **Cambio urgente en producción (hotfix):** sigue exactamente el mismo proceso — CI verde y revisión obligatoria (ver `git-workflow.md`). La urgencia no es excepción a la calidad; si acaso, es razón para tener a alguien revisando de inmediato en vez de saltarse la revisión.
- **PR que toca varios módulos a la vez:** se prefiere dividirlo en PRs por módulo cuando es posible; si no lo es (ej. una interfaz compartida que varios módulos deben adoptar a la vez), requiere revisión de alguien familiar con cada módulo afectado, no solo una aprobación genérica.
- **Herramienta de linter/analizador da un falso positivo:** se documenta la excepción explícitamente en el código (comentario de supresión con motivo), nunca desactivando la regla globalmente para evitar la molestia puntual.
- **Equipo de una sola persona en un momento dado (sin nadie más para revisar):** la regla de "no autoaprobar" no se relaja informalmente; se resuelve con revisión asistida por IA como reviewer adicional obligatorio (ver `docs/development/ci-cd.md`) mientras no haya una segunda persona disponible, dejando constancia en el PR de que fue el mecanismo usado.

---

## Decisiones futuras

- Definir umbral numérico de cobertura de código por módulo (ej. 80%) una vez exista una línea base real que medir, en vez de fijar un número arbitrario hoy.
- Evaluar CODEOWNERS por módulo de dominio para dirigir automáticamente la revisión a quien conoce ese módulo, cuando el equipo crezca.
- Evaluar herramienta de linter de arquitectura (ver `principios-de-arquitectura.md`, Decisiones futuras) para automatizar la verificación de fronteras entre módulos en vez de depender solo de revisión humana.

---

## Referencias

- `docs/architecture/principios-de-arquitectura.md` — fronteras de módulo y regla de "código sin test no está terminado".
- `docs/development/testing.md` — qué tipo de test corresponde a qué cambio, regla multi-tenant, flujos críticos.
- `docs/development/ci-cd.md` — mecánica exacta de qué corre en CI y en qué orden.
- `docs/development/git-workflow.md` — cuándo se abre un PR y cómo se mergea.
- `docs/standards/` (en construcción) — convenciones de nombres y formato exacto.

---

## Historial

- **2026-07-27** — Primera versión.
