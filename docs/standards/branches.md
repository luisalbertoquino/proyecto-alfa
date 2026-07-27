# Convención de Nombres de Rama

## Propósito

Fijar cómo se nombra una rama de git en Proyecto Alfa, para que cualquier persona pueda ver la lista de ramas activas y saber, sin abrir ninguna, qué tipo de cambio contiene cada una y a qué se refiere.

---

## Objetivo

Que el nombre de una rama comunique, por sí solo, el tipo de trabajo (funcionalidad, corrección, mantenimiento, documentación) y un resumen suficiente del alcance para no confundirla con otra.

---

## Alcance

Cubre exclusivamente la convención de nombres de rama: prefijo, formato de la descripción, e idioma. El flujo general de trabajo (cuándo se crea una rama, cómo se integra a `main`, revisión de PR) vive en `docs/development/git-workflow.md`; este documento no lo repite.

---

## Problema que resuelve

Sin una convención fija, ramas como `luis-cambios`, `arreglo2`, `nueva-feature` se acumulan y nadie puede saber, sin preguntar, si una rama sigue activa, qué tipo de cambio trae o si es segura de borrar.

---

## Principios

1. **El prefijo dice el tipo de cambio; la descripción dice de qué se trata.** Ambos son necesarios, ninguno reemplaza al otro.
2. **Corta, pero no críptica.** Una rama se lee de un vistazo en una lista junto a otras diez.
3. **Una rama, un propósito.** Igual que un commit, una rama no mezcla una funcionalidad con una corrección no relacionada.

---

## Reglas

- Formato: `<prefijo>/<descripción-corta-en-kebab-case>`.
- Prefijos permitidos (inglés, alineados con los tipos de `docs/standards/commits.md`):
  - `feature/` — funcionalidad nueva.
  - `fix/` — corrección de un bug.
  - `chore/` — mantenimiento, dependencias, configuración.
  - `docs/` — cambios solo de documentación.
  - `refactor/` — reestructuración sin cambio de comportamiento.
  - `hotfix/` — corrección urgente directamente relacionada con producción.
- La descripción va en `kebab-case`, minúsculas, sin tildes ni caracteres especiales, máximo ~5 palabras.
- El idioma de la descripción sigue la misma regla que `docs/standards/naming.md`: si describe un concepto de negocio, va en español (`feature/comparador-transportadoras`); si es puramente técnico, va en inglés (`chore/upgrade-laravel-11`).
- Si la rama atiende una tarea del backlog o un issue, se puede anteponer el número: `feature/48-alerta-stock-bajo`.
- `main` es la única rama larga viva; toda rama de trabajo nace de `main` y se elimina al fusionarse (detalle del flujo completo en `docs/development/git-workflow.md`).

---

## Ejemplos

| Rama | Válida | Motivo |
|---|---|---|
| `feature/comparador-transportadoras` | Sí | Prefijo correcto, concepto de negocio en español |
| `fix/48-calculo-tiempo-coordinadora` | Sí | Prefijo correcto, referencia a issue, descripción clara |
| `chore/upgrade-laravel-11` | Sí | Cambio técnico, descripción en inglés |
| `luis-cambios` | No | Sin prefijo, no dice qué tipo de cambio ni de qué trata |
| `feature/Fix-Envios` | No | Mezcla prefijos/tipos, no usa `kebab-case` |
| `nueva-rama-para-probar-cosas` | No | Sin prefijo, descripción no dice nada concreto |

---

## Casos límite

- **Un cambio no encaja claramente en un solo prefijo** (ej. una refactorización que también corrige un bug menor encontrado en el camino): se elige el prefijo del propósito principal de la rama; si el bug es significativo, se separa en su propia rama `fix/`.
- **Rama de experimentación que probablemente no se fusiona:** se prefija igual (normalmente `chore/` o `feature/`) y se documenta en la descripción del PR que es exploratoria, en vez de usar un prefijo no estándar como `spike/` hasta que el equipo decida adoptarlo formalmente (ver Decisiones futuras).
- **Rama de release o de sincronización de infraestructura:** fuera del alcance de este documento hasta que exista un flujo de releases formal; se documentará entonces en `docs/development/git-workflow.md`.

---

## Decisiones futuras

- Si se adopta un prefijo `spike/` para exploraciones técnicas que no necesariamente terminan en un merge.
- Si se automatiza la validación del nombre de rama en GitHub Actions antes de permitir abrir un PR.

---

## Referencias

- `docs/development/git-workflow.md` — flujo completo de trabajo con git; este documento solo cubre la convención de nombres.
- `docs/standards/commits.md` — tipos de commit que inspiran los prefijos de rama.
- `docs/standards/naming.md` — regla de idioma (español para negocio, inglés para técnico) aplicada aquí.

---

## Historial

- **2026-07-27** — Primera versión.
