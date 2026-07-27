# Base de Datos

## Propósito

Fijar la estrategia de datos de Proyecto Alfa: por qué MySQL, cómo se aplica el multi-tenant a nivel de esquema (`tenant_id` en toda tabla de negocio, indexado desde el día uno), cómo se hacen migraciones sin downtime, cómo se respaldan y recuperan los datos, y cuándo se evalúa particionar o aislar un tenant. Este documento fija el **porqué** de estas decisiones; convenciones exactas de nombres de columnas, tipos y estilo de migración viven en `docs/standards/` cuando exista un archivo dedicado a base de datos.

---

## Objetivo

Que el modelo de datos soporte, sin reescritura, el crecimiento de un tenant único (el piloto) a muchos tenants con volúmenes de tráfico y de datos muy distintos entre sí, y que ningún cambio de esquema en producción requiera apagar el sistema.

---

## Alcance

Cubre: elección de MySQL, estrategia multi-tenant en el modelo de datos, indexación, convención de migraciones seguras, backups y recuperación, y particionamiento/aislamiento futuro de tenants grandes.

No cubre: contrato de la API que expone estos datos (`apis.md`), estructura de módulos Laravel que los consume (`arquitectura-backend.md`), ni el detalle exacto de cada tabla (vivirá en `database/` y en la documentación de esquema que se genere junto al código).

---

## Problema que resuelve

Un modelo de datos pensado "para un solo negocio" sin `tenant_id` desde el inicio obliga, el día que aparece el segundo tenant, a una migración que toca todas las tablas de negocio, reescribe cada query, y arriesga fugar datos de un tenant a otro durante la transición. De igual forma, migraciones que bloquean tablas grandes tumban el sistema en producción, y la ausencia de una estrategia de backups probada convierte cualquier incidente en una posible pérdida irreversible de pedidos, inventario o pagos. Este documento fija las reglas que evitan los tres problemas desde la primera migración.

---

## Principios

1. **Multi-tenant desde la primera migración, aunque hoy exista un solo tenant.** Toda tabla de negocio nace con `tenant_id` (ver `vision-tecnica.md`, principio 3).
2. **MySQL como única fuente de verdad transaccional.** Redis es cache y cola, no almacenamiento de verdad (ver `escalabilidad.md`).
3. **Ninguna migración bloquea producción.** Un cambio de esquema en una tabla grande (pedidos, movimientos de inventario) se diseña para aplicarse sin lock prolongado ni downtime.
4. **Todo dato de negocio es recuperable.** Backups automáticos, probados periódicamente con restauración real, no solo "existir".
5. **El aislamiento de un tenant grande es una opción reservada, no el diseño por defecto.** Se empieza con esquema compartido y `tenant_id`; se aísla un tenant específico solo cuando haya una razón medible.

---

## Reglas

### Motor y convenciones generales

- MySQL 8 es el motor relacional único del sistema. Se eligió porque ya está disponible de forma nativa en el entorno real del desarrollador (Laragon en local, instalación nativa en el droplet de pruebas — ver `docs/adr/ADR-002.md`), y porque soporta bien lo que el prototipo necesita hoy: columnas `JSON` nativas para atributos variables de producto, particionamiento por rango, y robustez transaccional (InnoDB) para operaciones de negocio crítico (pedidos, inventario, pagos).
- Toda tabla que almacene datos de negocio de un tenant incluye una columna `tenant_id` (UUID o bigint según convención elegida), no nula, indexada.
- Una tabla sin `tenant_id` requiere justificación explícita en su migración (ej. catálogos verdaderamente globales como países, monedas, o tablas internas de Proyecto Alfa como planes SaaS).

### Índices

- Todo índice que soporte una consulta filtrada por tenant (la inmensa mayoría de las consultas del sistema) es un índice **compuesto** que empieza por `tenant_id`, no un índice aislado sobre la otra columna. Ejemplo: `CREATE INDEX ON pedidos (tenant_id, estado, creado_en);` para el listado de pedidos por estado de un tenant, en vez de un índice separado sobre `estado`.
- Toda clave foránea a una tabla también multi-tenant se acompaña, cuando la consulta lo amerita, de un índice que incluya `tenant_id` para evitar escaneos que crucen tenants innecesariamente.
- Se revisan periódicamente índices no usados (`sys.schema_unused_indexes` o `performance_schema` en MySQL 8) para no pagar el costo de escritura de índices que ninguna consulta real utiliza.

### Migraciones sin downtime

- Un cambio de esquema que pueda bloquear una tabla grande (agregar columna `NOT NULL` sin default, agregar índice de forma bloqueante, cambiar tipo de columna) se descompone en pasos compatibles hacia atrás:
  1. Agregar la columna nueva como nullable (o con default calculado sin bloqueo).
  2. Desplegar el código que la usa de forma opcional.
  3. Backfill de datos existentes en lotes, fuera del ciclo de request (job en cola).
  4. Migración posterior que agrega la restricción `NOT NULL` una vez todos los registros están poblados.
- Los índices nuevos sobre tablas con volumen significativo se crean aprovechando el DDL en línea de MySQL 8 (`ALGORITHM=INPLACE, LOCK=NONE`, el equivalente a `CREATE INDEX CONCURRENTLY` de PostgreSQL) para no bloquear escrituras.
- Ninguna migración de datos (backfill) se ejecuta como parte del deploy síncrono; se despacha como Job cuando el volumen es significativo (consistente con el principio de `vision-tecnica.md` de que el trabajo pesado va a cola).
- Toda migración es reversible o, si no puede serlo (ej. borrado de columna), se aplica en una fase separada solo después de confirmar que el código que la usaba ya no está desplegado.

### Backups y recuperación

- Backups automáticos completos con frecuencia diaria como mínimo, más binary log (binlog) continuo para permitir recuperación a un punto en el tiempo (PITR) entre backups completos, dado que un pedido o un pago perdido no es aceptable.
- Los backups se prueban periódicamente restaurándolos en un entorno aislado, no solo se asume que "existen y funcionan".
- Retención de backups suficiente para cubrir el peor caso razonable de detección tardía de un problema de datos (ej. 30 días), con backups más antiguos archivados a menor costo.
- Los backups y su acceso están sujetos a la misma postura de manejo de secretos que el resto del sistema (ver `seguridad.md`): credenciales de acceso a backups no viven en el repositorio.

### Particionamiento y aislamiento de tenants

- El diseño por defecto es esquema compartido: todas las tablas de negocio viven en la misma base de datos, separadas lógicamente por `tenant_id`.
- Se evalúa particionar una tabla (por ejemplo, particionamiento nativo de MySQL por rango de fecha en `pedidos` o `movimientos_inventario`) cuando su volumen empiece a degradar el rendimiento de índices o mantenimiento (optimize table, reindex), no de forma preventiva.
- Se evalúa aislar a un tenant específico en su propio schema o base de datos dedicada cuando ese tenant, por volumen de tráfico o de datos, empiece a afectar la operación de los demás tenants que comparten la base — esto es una decisión operativa reservada, no el punto de partida (ver `vision-tecnica.md`, casos límite).

---

## Ejemplos

- La tabla `pedidos` nace con columnas `id`, `tenant_id`, `cliente_id`, `estado`, `total`, `creado_en`, con índice compuesto `(tenant_id, estado, creado_en)` para soportar el listado de pedidos pendientes de un tenant ordenado por fecha, que es la consulta más frecuente del panel administrativo.
- Para agregar una columna `canal_origen` (de dónde vino el pedido: tienda propia, TikTok Shop, Mercado Libre) a una tabla `pedidos` ya con millones de filas: se agrega nullable, se despliega el código que la escribe en pedidos nuevos, se rellenan los históricos por Job en lotes, y solo entonces se agrega la restricción `NOT NULL`.
- Un incidente que corrompe datos de inventario de un tenant se recupera restaurando desde el backup con recuperación a un punto en el tiempo vía binlog al minuto anterior al incidente, en un entorno de prueba, antes de decidir si se aplica sobre producción.

---

## Casos límite

- **Un tenant crece 100x más que el resto** (ver `vision-tecnica.md`): sus consultas empiezan a competir por los mismos recursos que tenants pequeños en la misma base compartida. Se evalúa moverlo a un schema o base de datos dedicada sin afectar a los demás tenants, usando el mismo `tenant_id` como clave de particionamiento lógico ya existente.
- **Migración de backfill sobre una tabla con tráfico de escritura constante** (ej. `movimientos_inventario` durante horario pico): el backfill se ejecuta en lotes pequeños con pausas entre ellos, monitoreando el impacto en latencia de escritura antes de continuar.
- **Query que accidentalmente omite el filtro de `tenant_id`**: se trata como un incidente de seguridad, no solo de rendimiento (ver `seguridad.md`) — se previene con scoping automático a nivel de aplicación (ver `arquitectura-backend.md`), no solo con disciplina manual en cada query.
- **Restauración de backup necesaria para un solo tenant**, sin afectar a los demás que comparten base de datos: dado el esquema compartido, esto exige restaurar en un entorno aislado y migrar selectivamente los datos de ese `tenant_id` — más costoso que si el tenant ya estuviera aislado, lo que es en sí mismo una señal para evaluar aislarlo.

---

## Decisiones futuras

- Umbral concreto (número de filas, tamaño de tabla, latencia de query) que dispara la evaluación de particionar una tabla específica.
- Umbral concreto (tráfico, volumen de datos, ruido en métricas compartidas) que dispara la evaluación de aislar a un tenant grande en su propio schema o base de datos.
- Herramienta y convención exacta de migraciones (Laravel migrations estándar vs. herramienta adicional para grandes volúmenes) para las fases de backfill.
- Política formal de retención de datos por tenant al darse de baja del sistema (relevante una vez exista el modelo SaaS con múltiples tenants reales).

---

## Referencias

- `docs/architecture/vision-tecnica.md` — decisión de multi-tenant desde el modelo de datos.
- `docs/architecture/arquitectura-backend.md` — cómo la capa de Repositories aplica el scoping por `tenant_id` automáticamente.
- `docs/architecture/escalabilidad.md` — réplicas de lectura y cache sobre esta base de datos.
- `docs/architecture/seguridad.md` — aislamiento entre tenants como postura de seguridad, no solo de datos.

---

## Historial

- **2026-07-27** — Primera versión.
- **2026-07-27** — Actualizado: MySQL en vez de PostgreSQL y despliegue nativo en vez de Docker — ver ADR-002.
