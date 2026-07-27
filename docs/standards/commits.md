# Estándar de Commits

## Propósito

Fijar el formato que todo mensaje de commit en Proyecto Alfa debe seguir, para que el historial de git sea legible por cualquier persona (o herramienta) sin tener que abrir el diff, y para que se pueda generar un changelog automático a futuro sin reescribir mensajes retroactivamente.

---

## Objetivo

Que cualquier desarrollador pueda leer `git log --oneline` y entender qué cambió y por qué, sin abrir el commit; y que un commit se pueda clasificar automáticamente (feature, fix, breaking change) sin intervención manual.

---

## Alcance

Cubre: estructura del mensaje de commit, tipos permitidos, qué va en el cuerpo, cómo referenciar issues o tareas del `docs/business/product-backlog.md`, y cómo se marca un cambio incompatible.

No cubre: convención de nombres de rama (`docs/standards/branches.md`), ni el flujo de trabajo de git de principio a fin (`docs/development/git-workflow.md`).

---

## Problema que resuelve

Un historial de commits sin formato ("cambios", "fix", "wip", "arreglos varios") no dice nada seis meses después: no se puede saber qué módulo tocó un commit, si fue una funcionalidad nueva o una corrección, ni relacionarlo con la tarea que lo originó. Esto vuelve casi imposible depurar cuándo se introdujo un bug o generar notas de versión sin reconstruir el contexto de memoria.

---

## Principios

1. **El mensaje comunica el porqué, el diff ya comunica el qué.** El título y el cuerpo explican la razón del cambio; el código exacto que cambió ya está en el diff.
2. **Un commit, un propósito.** Un commit que mezcla una funcionalidad nueva con una corrección no relacionada se separa en dos.
3. **Formato consistente antes que expresividad libre.** Se sacrifica algo de libertad de redacción a cambio de que todo el equipo (y las herramientas) puedan parsear el historial de la misma forma.

---

## Reglas

- Se usa el formato [Conventional Commits](https://www.conventionalcommits.org/):

  ```
  <tipo>(<alcance opcional>): <descripción corta en presente>

  <cuerpo opcional: por qué, no qué>

  <footer opcional: referencias, breaking changes>
  ```

- Tipos permitidos (en inglés, por ser vocabulario técnico estándar de la industria, no de negocio):
  - `feat` — funcionalidad nueva de cara al usuario o a otro módulo.
  - `fix` — corrección de un comportamiento incorrecto.
  - `docs` — cambios solo de documentación (`docs/`, `README.md`, comentarios).
  - `refactor` — cambio de estructura interna sin cambiar comportamiento observable.
  - `test` — agregar o corregir tests sin cambiar código de producción.
  - `chore` — tareas de mantenimiento (dependencias, configuración, scripts) sin impacto en el comportamiento de la app.
  - `perf` — cambio cuyo propósito explícito es mejorar rendimiento.
  - `style` — formato, espacios, linting; sin cambio de lógica.
  - `build` / `ci` — cambios al pipeline de build o de GitHub Actions.
- El alcance opcional identifica el módulo o app afectada, en minúscula, entre paréntesis: `feat(pedidos): ...`, `fix(apps-web): ...`, `chore(infra): ...`.
- La descripción corta va en modo imperativo/presente ("agrega", "corrige", no "agregado" ni "agregando"), en español (idioma de trabajo del equipo y del resto de la documentación), sin punto final, máximo ~72 caracteres.
- El cuerpo (si el cambio no es obvio por el título) explica **por qué** se hizo el cambio, qué alternativa se descartó, o qué problema resolvía — no repite línea por línea lo que ya muestra el diff.
- Un commit que rompe compatibilidad hacia atrás (ej. cambia un contrato de API sin versión nueva, cambia una migración ya aplicada en producción) agrega un footer `BREAKING CHANGE: <explicación>`.
- Referencia a una tarea del backlog o un issue de GitHub en el footer: `Refs: #123` o `Closes: #123` si el commit la resuelve por completo.
- Nunca se commitean secretos, credenciales ni datos de tenants reales — ver `docs/standards/security.md`.

---

## Ejemplos

```
feat(inventario): agrega alerta de stock bajo por producto

Los emprendedores pedían saber cuándo un producto está por agotarse
sin tener que revisar el listado completo cada día. Se dispara un
evento StockBajo cuando el disponible cae bajo el umbral configurado
por producto.

Refs: #48
```

```
fix(envios): corrige cálculo de tiempo estimado con Coordinadora

El adaptador sumaba días hábiles como si fueran corridos.

Closes: #112
```

```
chore(ci): sube runner de GitHub Actions a ubuntu-24.04
```

```
refactor(pedidos): extrae validación de stock a InventarioService

BREAKING CHANGE: PedidoService::confirmar() ya no acepta el parámetro
$forzarSinStock; usar InventarioService::reservar() explícitamente.
```

---

## Casos límite

- **Commit que solo mueve/renombra archivos sin cambiar contenido:** tipo `refactor`, cuerpo indica que es solo movimiento (`git mv`), para que quien lo revise no busque un cambio de lógica que no existe.
- **Commit de un merge o de un revert:** se conserva el mensaje que genera git automáticamente para merges; un revert usa el prefijo `revert:` seguido del mensaje del commit revertido.
- **Cambio que toca varios módulos a la vez** (ej. una migración de estructura de carpetas): se omite el alcance entre paréntesis o se usa un alcance general (`chore(repo): ...`) en vez de forzar una lista de módulos.

---

## Decisiones futuras

- Adopción de un hook de commit (`commitlint` o equivalente) que valide el formato automáticamente antes de aceptar el commit.
- Generación automática de changelog por versión a partir del historial de commits, una vez exista un primer release etiquetado.

---

## Referencias

- `docs/standards/branches.md` — convención de nombres de rama, complementaria a este documento.
- `docs/development/git-workflow.md` — flujo completo de trabajo con git (branching, PRs, merges).
- [Conventional Commits](https://www.conventionalcommits.org/) — especificación de referencia.

---

## Historial

- **2026-07-27** — Primera versión.
