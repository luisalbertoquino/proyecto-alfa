# Plantilla: Nuevo Componente (`packages/ui`)

Copia este archivo al crear un componente nuevo destinado a compartirse entre `apps/web` y `apps/admin`.

---

## 1. Datos básicos

- **Nombre del componente** (inglés técnico, `PascalCase` — ver `docs/standards/naming.md`): `{{ProductCard}}`
- **Dónde vive:** `packages/ui/src/{{ruta}}`
- **Qué resuelve (una frase):** `{{...}}`
- **¿Ya existe algo similar en `packages/ui`?** `{{Sí, reusar/extender ... | No}}`

---

## 2. Props

| Prop | Tipo | Obligatorio | Default | Descripción |
|---|---|---|---|---|
| `{{...}}` | `{{...}}` | `{{sí/no}}` | `{{...}}` | `{{...}}` |

---

## 3. Estados obligatorios

Todo componente interactivo debe contemplar y mostrar explícitamente:

- [ ] **Default** — estado normal.
- [ ] **Hover** — retroalimentación visual al pasar el cursor.
- [ ] **Focus** — visible y navegable por teclado (`:focus-visible`), no solo por mouse.
- [ ] **Disabled** — visualmente distinto, no interactivo, comunicado también a lectores de pantalla (`aria-disabled`).
- [ ] **Loading** — si el componente dispara una acción asíncrona (ej. botón que llama a la API).
- [ ] **Error** — si el componente puede recibir o producir un estado de error (ej. input de formulario, carga de datos).

Si algún estado no aplica a este componente, indícalo explícitamente: `{{estado: no aplica porque ...}}`

---

## 4. Accesibilidad

- [ ] Elementos interactivos son navegables y operables por teclado.
- [ ] Roles/atributos ARIA correctos si el HTML semántico no basta.
- [ ] Contraste de color cumple mínimo WCAG AA (ver `docs/design/design-system.md`).
- [ ] Textos alternativos en imágenes/íconos que comunican información (no decorativos).
- [ ] El texto visible está en español (contenido de negocio/UI), las props/nombres internos en inglés.

---

## 5. Dónde se documenta

- [ ] Entrada agregada al catálogo de componentes de `docs/design/design-system.md` (o Storybook, si ya existe) con los estados de la sección 3.
- [ ] Si el componente introduce un patrón visual nuevo no cubierto por `docs/design/ui-guidelines.md`, se actualiza ese documento.

---

## 6. Tests

- [ ] Test de render en cada estado obligatorio de la sección 3.
- [ ] Test de interacción (click, teclado) si el componente es interactivo.

---

## 7. Referencias

- `docs/standards/naming.md` — convención de nombres de componentes y props.
- `docs/design/design-system.md` — sistema de diseño y catálogo de componentes.
- `docs/design/ux-principles.md` — principios de experiencia que el componente debe respetar.
