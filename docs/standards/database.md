# Reglas Operativas de Base de Datos

## Propósito

Dar las reglas concretas para escribir migraciones y diseñar esquema en Proyecto Alfa: cómo se nombra una migración, cómo se agrega `tenant_id` a una tabla nueva, cómo se hace una migración sin downtime, y qué índices son obligatorios. `docs/architecture/base-de-datos.md` cubre el diseño físico y las decisiones de fondo (particionamiento, estrategia multi-tenant a nivel de motor); este documento da el checklist que se sigue al escribir cada migración.

---

## Objetivo

Que ninguna tabla de negocio nueva llegue a revisión de código sin `tenant_id`, sin los índices mínimos, o con una migración que bloquee la tabla en producción.

---

## Alcance

Cubre: convención de nombres de archivo de migración, regla de `tenant_id` en tablas nuevas, patrón para migraciones sin downtime, índices obligatorios, y convención de nombres de tabla/columna (referenciada desde `docs/standards/naming.md`).

No cubre: diseño físico completo del esquema, estrategia de particionamiento o aislamiento de tenants grandes (`docs/architecture/base-de-datos.md`), ni backups/replicación (`docs/architecture/infraestructura.md`).

---

## Problema que resuelve

`vision-tecnica.md` ya fija que toda tabla de negocio lleva `tenant_id` desde el día uno. Sin una regla operativa que diga exactamente cómo se agrega (tipo de columna, índice, si es nullable), cada desarrollador la implementa distinto, y algunas tablas terminan con `tenant_id` sin indexar — lo que en un sistema multi-tenant no es solo un problema de rendimiento, es un riesgo de que una query mal filtrada mezcle datos de dos negocios distintos.

---

## Principios

1. **Toda tabla de negocio es multi-tenant hasta que se demuestre lo contrario.** La ausencia de `tenant_id` es la excepción que se justifica, no la regla por defecto (ver `vision-tecnica.md`).
2. **Una migración nunca bloquea producción.** Se asume que, cuando exista tráfico real, cualquier migración corre contra una tabla con datos y usuarios activos.
3. **El índice se agrega en la misma migración que la columna que protege**, nunca "después, cuando haga falta" — para cuando haga falta ya es una migración correctiva bajo presión.
4. **Una migración es un paso hacia adelante, no una historia editable.** Una migración ya aplicada en cualquier entorno compartido no se edita; un cambio se hace con una migración nueva.

---

## Reglas

### Nombres de migración

- Laravel: `YYYY_MM_DD_HHMMSS_verbo_descripcion.php`, en inglés (convención de framework), verbo en infinitivo: `2026_07_27_120000_create_pedidos_table.php`, `2026_07_27_120500_add_tenant_id_to_productos_table.php`.
- Una migración hace una sola cosa (crear una tabla, agregar una columna, agregar un índice); no se mezclan varios cambios estructurales no relacionados en una sola migración.

### `tenant_id` en tabla nueva

- Toda migración `create_..._table` de una tabla de negocio incluye:
  ```php
  $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
  $table->index('tenant_id');
  ```
  o, si la tabla se consulta casi siempre combinando tenant + otro campo (ej. `tenant_id` + `estado`), un índice compuesto en su lugar: `$table->index(['tenant_id', 'estado'])`.
- `tenant_id` nunca es nullable en una tabla de negocio.
- Una tabla sin `tenant_id` (catálogo verdaderamente global, ej. países, monedas) documenta la excepción en un comentario en la propia migración y se referencia en `docs/architecture/base-de-datos.md`.
- Toda query generada por Eloquent sobre una tabla con `tenant_id` pasa por un *global scope* que lo filtra automáticamente; un desarrollador no escribe `->where('tenant_id', ...)` a mano en cada consulta (eso es fuente de errores por omisión). El scope se implementa una vez a nivel de trait/modelo base y se documenta en `docs/architecture/base-de-datos.md`.

### Migraciones sin downtime

- Agregar una columna nueva: siempre `nullable()` o con `default()`, nunca `NOT NULL` sin default sobre una tabla con filas existentes.
- Agregar una restricción `NOT NULL` a una columna existente: se hace en dos pasos/migraciones — 1) backfill de los datos existentes, 2) migración separada que agrega la restricción, desplegada después de confirmar que el backfill terminó.
- Renombrar una columna: nunca con `rename` directo en una tabla ya usada en producción sin ventana de mantenimiento coordinada; se prefiere agregar la columna nueva, migrar datos, y retirar la vieja en una migración posterior, con el código desplegado leyendo ambas durante la transición.
- Agregar un índice a una tabla grande: se evalúa el uso de creación de índice sin bloqueo (`CREATE INDEX CONCURRENTLY` en PostgreSQL) cuando el volumen de la tabla lo justifique; documentado como decisión futura hasta que exista una tabla lo bastante grande para necesitarlo.
- Eliminar una columna o tabla: solo después de confirmar que ningún código en ningún entorno desplegado la lee ya (mínimo un ciclo de despliegue completo de gracia).

### Índices obligatorios

- `tenant_id` (o `[tenant_id, *]` compuesto) en toda tabla de negocio, como se describe arriba.
- Toda llave foránea (`_id`) tiene su índice correspondiente — Laravel lo agrega automáticamente con `foreignId()->constrained()`, pero se verifica en migraciones manuales.
- Toda columna usada como filtro frecuente en un endpoint paginado (ej. `estado` en `pedidos`, `canal_id` en tablas de sincronización) se indexa, idealmente compuesta con `tenant_id`.
- Toda columna usada para búsqueda de texto (ej. nombre de producto) evalúa índice `GIN`/`trigram` en vez de escaneo completo, una vez el volumen lo justifique.

### Nombres de tabla y columna

- Regidos por `docs/standards/naming.md`: tabla en `snake_case` plural español (`pedidos`, `envios`); columnas de negocio en `snake_case` español; columnas de sistema (`id`, `tenant_id`, `created_at`, `updated_at`, `deleted_at`, llaves foráneas `_id`) siempre en inglés.

---

## Ejemplos

```php
// 2026_07_27_120000_create_envios_table.php
Schema::create('envios', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
    $table->string('estado');
    $table->string('transportadora');
    $table->timestamps();
    $table->softDeletes();

    $table->index(['tenant_id', 'estado']);
});
```

```php
// 2026_08_10_090000_add_tiempo_estimado_to_envios_table.php
// Columna nueva, nullable — no rompe filas existentes, no requiere downtime.
Schema::table('envios', function (Blueprint $table) {
    $table->unsignedInteger('tiempo_estimado_horas')->nullable();
});
```

---

## Casos límite

- **Una tabla necesita referenciar datos de más de un tenant a la vez** (ej. tabla interna de operaciones de Proyecto Alfa que agrega métricas de todos los tenants): se marca explícitamente como tabla de sistema/operación, sin `tenant_id`, y se documenta la excepción en `docs/architecture/base-de-datos.md`, no se improvisa fila por fila.
- **Un tenant necesita borrarse por completo** (baja de cliente): se define un procedimiento de purga en cascada respetando `tenant_id`, documentado en `docs/architecture/base-de-datos.md` cuando exista el primer caso real.
- **Una migración falla a mitad de camino en producción:** se diseña para ser re-ejecutable de forma segura (idempotente a nivel de migración) o se acompaña de un plan de rollback explícito en la descripción del PR.

---

## Decisiones futuras

- Automatizar la verificación de que toda migración `create_..._table` de una tabla de negocio incluye `tenant_id` (linter o test de esquema).
- Estrategia concreta de `CREATE INDEX CONCURRENTLY` y de migraciones de dos pasos una vez exista una tabla con volumen que lo requiera.
- Procedimiento formal de purga de datos de un tenant dado de baja.

---

## Referencias

- `docs/architecture/base-de-datos.md` — diseño físico y estrategia multi-tenant a nivel de motor (el porqué).
- `docs/architecture/vision-tecnica.md` — origen de la regla `tenant_id` desde el día uno.
- `docs/standards/naming.md` — convención de nombres de tabla y columna.

---

## Historial

- **2026-07-27** — Primera versión.
