# apps/api — Backend

Laravel 13 (PHP 8.3.32), API JSON pura (`/api/v1/*`), monolito modular por dominio (`app/Modules/{Catalogo,Pedidos}`, más `app/Shared` para lo transversal — tenant, auth). Multi-tenant desde el modelo de datos: toda tabla de negocio tiene `tenant_id`, con scoping automático vía `TenantScope`/`BelongsToTenant`. Ver `docs/architecture/arquitectura-backend.md` y `docs/architecture/principios-de-arquitectura.md` antes de cambiar la estructura.

MySQL (no PostgreSQL — ver `docs/adr/ADR-002.md`), auth con Sanctum (token, no cookie/SPA), Redis vía `predis` para cache/colas.

## Correr en desarrollo

```bash
php artisan serve --port=8000
```

Necesita PHP **8.3.32** activo (no la 8.1 vieja de Laragon) y MySQL corriendo. Detalle completo, incluyendo cómo levantar Redis, en `docs/estado-actual.md`, sección "Cómo levantar el entorno en una sesión nueva".

## Tests

```bash
php artisan test
```

Corren contra MySQL real (`proyecto_alfa_test`, no sqlite — ver `phpunit.xml` y ADR-002), colocados junto a cada módulo (`app/Modules/*/Tests`, `app/Shared/Tests`), no en `tests/Feature`. Convenciones en `docs/development/testing.md` — en particular, todo test multi-tenant crea al menos 2 tenants y prueba explícitamente el caso negativo (que uno **no** vea los datos del otro).

## Despliegue

Nativo (sin Docker — ver `docs/adr/ADR-002.md`) vía OpenLiteSpeed + `lsphp` sobre el droplet de pruebas. Ver `scripts/deploy.sh` y `docs/estado-actual.md`, sección "Notas técnicas del despliegue", para los detalles reales (permisos de `storage/`, Document Root del vhost, etc.).
