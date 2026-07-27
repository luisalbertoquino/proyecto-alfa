# Plantilla: Nuevo Endpoint

Copia este archivo para documentar un endpoint nuevo antes de (o al mismo tiempo que) construirlo. Sigue las reglas de `docs/standards/api.md` — esta plantilla es el esqueleto para aplicarlas, no las repite.

---

## 1. Datos básicos

- **Módulo:** `{{Pedidos | Catalogo | Inventario | ...}}`
- **Método + ruta:** `{{GET|POST|PATCH|DELETE}} /api/v1/{{recurso}}`
- **Descripción en una frase:** `{{...}}`
- **¿Requiere autenticación?** `{{Sí (Sanctum) | No, público}}`
- **¿Requiere `Idempotency-Key`?** `{{Sí | No}}` — si es sí, justificar: `{{...}}`

---

## 2. Request

- **Parámetros de ruta:** `{{ {id}: uuid del recurso }}`
- **Query params (si aplica, ej. paginación/filtros):**

  | Parámetro | Tipo | Obligatorio | Descripción |
  |---|---|---|---|
  | `{{...}}` | `{{...}}` | `{{sí/no}}` | `{{...}}` |

- **Body (si aplica):**

  ```json
  {
    "{{campo}}": "{{tipo/ejemplo}}"
  }
  ```

- **Validaciones obligatorias** (ver `docs/standards/security.md`): `{{...}}`

---

## 3. Response

- **Éxito — código:** `{{200|201|202|204}}`

  ```json
  {
    "data": { "{{campo}}": "{{ejemplo}}" },
    "meta": { "version": "v1" }
  }
  ```

- **Paginación (si aplica):** `{{cursor | página/offset}}` — ver `docs/standards/api.md`.

---

## 4. Errores posibles

| Código | `codigo` | Cuándo ocurre |
|---|---|---|
| `{{422}}` | `{{STOCK_INSUFICIENTE}}` | `{{...}}` |
| `{{404}}` | — | Recurso no existe o no pertenece al tenant |
| `{{403}}` | `{{TENANT_SUSPENDIDO}}` | `{{...}}` |

---

## 5. Rate limiting

- **¿Este endpoint dispara trabajo costoso o externo?** `{{Sí/No}}` — si sí, límite propuesto: `{{N solicitudes / periodo / tenant}}`.

---

## 6. Checklist antes de mergear

- [ ] Ruta bajo `/api/v1/...`.
- [ ] Validación de entrada vía `FormRequest`, no validación manual en el controlador.
- [ ] Verifica pertenencia al tenant del recurso solicitado, no solo su existencia.
- [ ] Sigue la forma de respuesta estándar (`data`/`meta` o `error`) de `docs/standards/api.md`.
- [ ] Lógica de negocio vive en un servicio, no en el controlador (`principios-de-arquitectura.md`).
- [ ] Test de feature del flujo principal y de al menos un caso de error.
- [ ] Si es operación crítica, implementa `Idempotency-Key`.

---

## 7. Referencias

- `docs/architecture/apis.md` — contrato general de la API.
- `docs/standards/api.md` — reglas operativas exactas (headers, códigos, paginación).
- `docs/standards/security.md` — validación de entrada obligatoria.
