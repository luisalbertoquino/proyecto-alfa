# Estándar de Documentación

## Propósito

Fijar cómo se documenta Proyecto Alfa: por qué toda especificación usa la misma plantilla de diez secciones, cuándo un cambio de código obliga a actualizar un documento, y cuándo una decisión se registra como ADR en vez de (o además de) actualizar un documento de `docs/`.

---

## Objetivo

Que la documentación nunca quede desactualizada respecto al sistema real, y que encontrar la respuesta a "¿por qué se hizo así?" tome minutos, no una arqueología de commits y conversaciones de Slack.

---

## Alcance

Cubre: la estructura de diez secciones usada en `docs/architecture/`, `docs/business/`, `docs/design/`, `docs/development/` y `docs/standards/`; el criterio para decidir si un cambio de código requiere actualizar documentación; y la diferencia de propósito entre un documento de `docs/` y un ADR de `docs/adr/`.

No cubre: el contenido específico de cada documento (eso lo define cada uno en su propio `Alcance`), ni el formato de las plantillas de `templates/`, que son deliberadamente distintas (ver más abajo por qué).

---

## Problema que resuelve

Un proyecto con muchos documentos y ningún estándar termina con cada archivo en un formato distinto, algunos actualizados y otros congelados en una versión vieja del sistema, y ninguna forma de distinguir "esto es la decisión vigente" de "esto es una nota de una reunión pasada". Cuando eso pasa, la documentación deja de ser la fuente de verdad y el equipo vuelve a depender de memoria oral — exactamente lo que `principios-de-arquitectura.md` identifica como la causa más común de que un proyecto se vuelva lento de mantener.

---

## Principios

1. **Un documento sin fecha ni historial no es confiable.** Todo documento de especificación termina en `## Historial`, con la fecha de cada cambio relevante — sin eso, nadie sabe si lo que lee sigue vigente.
2. **La misma pregunta, en el mismo lugar, siempre.** Las diez secciones (`Propósito`, `Objetivo`, `Alcance`, `Problema que resuelve`, `Principios`, `Reglas`, `Ejemplos`, `Casos límite`, `Decisiones futuras`, `Referencias`, `Historial`) existen para que buscar "¿esto aplica a mi caso?" siempre empiece en `Alcance` y "¿hay una excepción?" siempre termine en `Casos límite`, sin importar qué documento se esté leyendo.
3. **Documentar la decisión, no solo el resultado.** `Problema que resuelve` y `Alternativas consideradas` (en ADRs) importan tanto como la regla final: sin el porqué, la regla se vuelve dogma que nadie sabe cuándo romper.
4. **Un documento vigente nunca contradice a otro vigente.** Si dos documentos entran en conflicto, gana el de nivel más alto (`vision-tecnica.md` sobre cualquier otro de arquitectura, según ya lo fija ese mismo documento) y el de nivel más bajo se corrige explícitamente, con nota en su `Historial`.
5. **Código y documento cambian juntos, no en secuencia separada.** Un cambio de arquitectura o de regla operativa no se considera terminado hasta que el documento correspondiente refleja la nueva realidad.

---

## Reglas

### La plantilla de diez secciones

- Todo documento de especificación en `docs/architecture/`, `docs/business/`, `docs/design/`, `docs/development/` y `docs/standards/` sigue exactamente esta estructura, en este orden: `Propósito → Objetivo → Alcance → Problema que resuelve → Principios → Reglas → Ejemplos → Casos límite → Decisiones futuras → Referencias → Historial`.
- Ninguna sección se omite aunque el contenido sea breve; si una sección realmente no aplica, se deja con una frase explícita ("No aplica: ..."), nunca se borra el encabezado.
- Plantilla lista para copiar: `templates/nuevo-documento.md`.

### Cuándo un cambio de código exige actualizar un documento

Un pull request que hace cualquiera de estas cosas **no se aprueba** sin el documento actualizado en el mismo PR:

- Cambia una regla ya escrita en un documento de `docs/standards/` o `docs/architecture/` (ej. cambia el formato de respuesta de error de la API).
- Agrega un módulo de dominio nuevo, un endpoint nuevo, o una integración externa nueva (ver `templates/nuevo-modulo.md`, `templates/nuevo-endpoint.md`, `templates/nueva-api.md`).
- Introduce una excepción a una regla existente que no estaba prevista en `Casos límite`.
- Resuelve algo que estaba listado en `Decisiones futuras` de algún documento — esa sección se actualiza para reflejar la decisión tomada (y, si es una decisión de arquitectura, se registra además como ADR).

Un cambio que no altera ninguna regla documentada (un bugfix que no cambia comportamiento observable, un refactor interno, ajustes de estilo) no requiere tocar documentación.

### ADR vs. documentos de `docs/`

- Un **ADR** (`docs/adr/`) registra una decisión puntual, con fecha, alternativas consideradas y consecuencias — es un registro histórico, inmutable una vez aceptado (si la decisión cambia, se escribe un ADR nuevo que referencia y reemplaza al anterior, no se edita el viejo).
- Un **documento de `docs/`** describe el estado vigente de una parte del sistema — es vivo, se edita en el lugar cada vez que la realidad cambia, y su `Historial` es lo único que preserva el pasado.
- Toda decisión de arquitectura relevante (cambia `vision-tecnica.md`, `principios-de-arquitectura.md`, o introduce/reemplaza una tecnología del stack) se registra primero como ADR; el documento vigente de `docs/` se actualiza para reflejar la decisión ya tomada, y referencia el ADR correspondiente.
- Una regla operativa que no es una decisión de arquitectura (ej. "las migraciones se nombran así") vive directamente en `docs/standards/`, sin necesitar un ADR propio.

---

## Ejemplos

- Se decide cambiar el motor de cola de Redis a otro proveedor: se escribe un ADR nuevo (`docs/adr/ADR-00X.md`) con el porqué y las alternativas evaluadas, y `vision-tecnica.md` se actualiza para reflejar la decisión, con una línea en su `Historial` que referencia el ADR.
- Se agrega el módulo `Canales` con su primera integración a TikTok Shop: el PR incluye el módulo, el ADR si cambia algo de arquitectura general, y la actualización de `docs/business/modulos.md` y `docs/architecture/arquitectura-funcional.md`, tal como exige `templates/nuevo-modulo.md`.
- Se corrige un typo en un mensaje de error interno que nunca llega al usuario: no requiere actualizar documentación, porque no cambia ninguna regla documentada.

---

## Casos límite

- **Un documento de `docs/` y un ADR entran en conflicto** (el documento no se actualizó después del ADR): gana el ADR por ser el registro de la decisión real tomada; se corrige el documento de inmediato y se nota en su `Historial`.
- **Una decisión se toma verbalmente o en una reunión sin registrarse:** no se considera vigente hasta que exista un documento o ADR que la respalde — evita que "lo dijimos en una llamada" reemplace a la documentación.
- **Un documento crece demasiado y se vuelve difícil de navegar:** se divide en documentos más específicos referenciados entre sí (como ya ocurre entre `vision-tecnica.md` y sus documentos de desarrollo: `apis.md`, `base-de-datos.md`, etc.), nunca se elimina contenido sin mover la información a otro lado.

---

## Decisiones futuras

- Si se adopta una revisión periódica obligatoria (ej. trimestral) de los documentos de `docs/architecture/` para detectar desactualización silenciosa, más allá de la exigida por PRs individuales.
- Numeración y plantilla de RFCs, si el proyecto adopta ese mecanismo para decisiones que necesitan discusión previa a ser tomadas (a diferencia del ADR, que registra una decisión ya tomada).

---

## Referencias

- `templates/nuevo-documento.md` — plantilla de las diez secciones lista para copiar.
- `docs/adr/ADR-001.md` — primer ADR del proyecto, ejemplo de formato.
- `docs/architecture/vision-tecnica.md` — documento de mayor jerarquía; ningún otro documento lo contradice sin corregirlo primero ahí.

---

## Historial

- **2026-07-27** — Primera versión.
