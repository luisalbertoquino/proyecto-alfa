# templates/

Plantillas reutilizables para arrancar un documento nuevo con la estructura ya esperada por `docs/standards/documentation.md`, en vez de improvisar el formato cada vez.

- **`nuevo-documento.md`** — plantilla general de 10 secciones (Propósito/Objetivo/Alcance/Problema/Principios/Reglas/Ejemplos/Casos límite/Decisiones futuras/Referencias/Historial) que usa todo documento formal de `docs/`.
- **`nuevo-modulo.md`** — para documentar un módulo de dominio nuevo del backend (`app/Modules/*`).
- **`nuevo-endpoint.md`** / **`nueva-api.md`** — para documentar un endpoint o superficie de API nueva, en línea con `docs/standards/api.md`.
- **`nuevo-componente.md`** — para documentar un componente de UI nuevo (pensado para cuando exista `packages/ui`).
- **`nueva-historia.md`** — para redactar una historia de usuario nueva siguiendo el formato de `docs/business/casos-de-uso.md`.

Se copian, no se editan in-place — cada documento nuevo parte de una copia de la plantilla correspondiente.
