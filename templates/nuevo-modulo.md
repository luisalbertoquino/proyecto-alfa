# Plantilla: Nuevo Módulo de Dominio

Copia este archivo al crear un módulo de dominio nuevo en el backend (`apps/api`). Llena cada sección y bórrala solo cuando el checklist esté completo.

> Antes de empezar: confirma que el módulo no encaja en uno de los nueve ya existentes (`Catalogo`, `Pedidos`, `Inventario`, `Envios`, `Proveedores`, `Publicidad`, `Analitica`, `Canales`, `IA`). Un módulo nuevo es una decisión de arquitectura — si tienes dudas, discútelo antes de crear carpetas.

---

## 1. Datos básicos

- **Nombre del módulo:** `{{NombreModulo}}` (español, `PascalCase`, sin tildes — ver `docs/standards/naming.md`)
- **Responsable:** `{{nombre}}`
- **Qué capacidad de negocio cubre (una frase):** `{{...}}`
- **Por qué no encaja en un módulo existente:** `{{...}}`

---

## 2. Estructura de carpetas a crear

```
apps/api/app/Domain/{{NombreModulo}}/
├── Controllers/
├── Models/
├── Services/
├── Events/
├── Listeners/
├── Requests/
├── Resources/
├── Policies/
└── Interfaces/        # si el módulo integra un servicio externo

apps/api/tests/Domain/{{NombreModulo}}/
├── Feature/
└── Unit/
```

- [ ] Carpetas creadas siguiendo esta estructura (o justificar la desviación aquí: `{{...}}`)

---

## 3. Interfaz de servicio expuesta a otros módulos

Otros módulos solo pueden llamar a este módulo a través de su capa de `Services`, nunca a sus `Models` (ver `principios-de-arquitectura.md`, principio 2).

- **Clase de servicio pública:** `{{NombreModulo}}Service`
- **Métodos que expone (firma + qué hace, no cómo):**
  - `{{metodo()}}` — `{{qué hace}}`
- **Eventos de dominio que dispara** (para comunicación desacoplada con otros módulos):
  - `{{NombreEvento}}` — cuándo se dispara y qué payload lleva.

- [ ] Interfaz de servicio definida antes de escribir el primer controlador.
- [ ] Ningún otro módulo importará clases de `Models/`, `Repositories/` de este módulo directamente.

---

## 4. Multi-tenant

- [ ] Toda tabla nueva de este módulo incluye `tenant_id` indexado desde su primera migración (ver `docs/standards/database.md`).
- [ ] Toda query del módulo pasa por el *global scope* de tenant, no filtra `tenant_id` a mano.
- [ ] Si alguna tabla del módulo **no** lleva `tenant_id`, la excepción está justificada y documentada aquí: `{{...}}`

---

## 5. Integraciones externas (si aplica)

Si el módulo integra un servicio externo (transportadora, marketplace, proveedor de IA, pasarela de pago), sigue además `templates/nueva-api.md`.

- **Interfaz propia definida antes del primer adaptador:** `{{NombreInterface}}`
- [ ] Interfaz creada en `Interfaces/` antes de escribir el adaptador concreto.

---

## 6. Tests

- [ ] Test del flujo principal (feature test) del módulo.
- [ ] Al menos un test que verifique el comportamiento con **más de un tenant** (ver `principios-de-arquitectura.md`, principio 6) — ningún test asume que solo existe un negocio en el sistema.
- [ ] Tests de los casos límite conocidos del módulo.

---

## 7. Documentación a actualizar

Este módulo no se considera terminado hasta que:

- [ ] `docs/business/modulos.md` incluye el módulo nuevo con su descripción de negocio.
- [ ] `docs/architecture/arquitectura-funcional.md` incluye el módulo en el mapa funcional del sistema.
- [ ] Si el módulo introduce un endpoint, se documentó con `templates/nuevo-endpoint.md`.
- [ ] Si la creación del módulo implicó una decisión de arquitectura no trivial, se registró como ADR en `docs/adr/`.

---

## 8. Referencias

- `docs/architecture/principios-de-arquitectura.md` — reglas de organización por dominio que este módulo debe respetar.
- `docs/standards/naming.md` — convención de nombres.
- `docs/standards/database.md` — reglas de `tenant_id` y migraciones.
