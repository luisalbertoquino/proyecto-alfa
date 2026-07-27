# Plantilla: Historia de Usuario

Copia este archivo para levantar una historia de usuario nueva antes de llevarla a `docs/business/product-backlog.md` o a un issue de GitHub.

---

## Historia

**Como** `{{actor: ej. dueño de tienda, comprador, administrador}}`
**quiero** `{{objetivo: qué acción quiere poder hacer}}`
**para** `{{beneficio: qué gana al lograrlo}}`

---

## Módulo al que pertenece

`{{Catalogo | Pedidos | Inventario | Envios | Proveedores | Publicidad | Analitica | Canales | IA}}`

Si no encaja claramente en uno, anótalo aquí y discútelo antes de asignarlo: `{{...}}`

---

## Criterios de aceptación

- [ ] `{{Dado ... cuando ... entonces ...}}`
- [ ] `{{...}}`
- [ ] `{{...}}`

---

## Fuera de alcance

<!-- Qué NO incluye esta historia, para evitar que crezca sin control durante la construcción. -->

- `{{...}}`

---

## Consideraciones

- **¿Afecta a más de un tenant / requiere probarse con más de uno?** `{{Sí/No — recordatorio: principios-de-arquitectura.md exige probar con más de un tenant}}`
- **¿Requiere endpoint nuevo?** `{{Sí/No}}` — si sí, usar `templates/nuevo-endpoint.md`.
- **¿Requiere integración externa nueva?** `{{Sí/No}}` — si sí, usar `templates/nueva-api.md`.
- **¿Requiere componente de UI nuevo?** `{{Sí/No}}` — si sí, usar `templates/nuevo-componente.md`.

---

## Referencias

- `docs/business/casos-de-uso.md`
- `docs/business/product-backlog.md`
