# Escalabilidad

## Propósito

Explicar cómo escala Proyecto Alfa cuando crece el número de tenants y el tráfico: escalado horizontal de contenedores stateless, cache con Redis, colas para trabajo pesado, CDN de Cloudflare para estáticos, réplicas de lectura de PostgreSQL cuando haga falta, y qué se mide para saber cuándo escalar cada capa.

---

## Objetivo

Que crecer de un tenant a cientos, y de tráfico de un negocio piloto a tráfico de campaña publicitaria exitosa, sea una decisión de **agregar recursos**, no de **reescribir código**.

---

## Alcance

Cubre: escalado horizontal de aplicación, estrategia de cache, uso de colas para trabajo pesado, rol del CDN sobre contenido estático/SSG, réplicas de lectura de base de datos, y métricas que disparan cada decisión de escalar.

No cubre: el detalle de infraestructura y despliegue (`infraestructura.md`), el esquema físico de base de datos (`base-de-datos.md`), ni el pipeline de CI/CD (`docs/development/ci-cd.md`).

---

## Problema que resuelve

Un sistema que guarda estado en el servidor de aplicación, calcula todo en el momento de cada request, y no tiene plan de qué hacer cuando la base de datos se vuelve el cuello de botella, no escala agregando servidores — escala reescribiendo bajo presión, en producción, durante el peor momento posible (un pico de tráfico real). Este documento fija por adelantado qué palanca se mueve para cada tipo de crecimiento, para que escalar sea operar infraestructura, no rediseñar arquitectura.

---

## Principios

1. **Stateless es la base de todo lo demás.** Sin estado local en los servidores de aplicación (ver `vision-tecnica.md`, principio 4), agregar instancias detrás de un balanceador es la primera y más barata palanca de escala.
2. **Cachear lo que se lee mucho y cambia poco.** Redis absorbe lectura repetida antes de que llegue a PostgreSQL.
3. **Lo pesado nunca compite con el tráfico interactivo.** El trabajo asíncrono (colas) tiene su propia capacidad de cómputo, separable de los servidores que responden requests de usuarios.
4. **Lo estático se sirve lo más cerca posible del usuario.** Cloudflare como CDN evita que tráfico de solo lectura de catálogo toque siquiera los contenedores de aplicación.
5. **Se escala con datos, no con intuición.** Cada palanca tiene una métrica asociada que indica cuándo activarla.
6. **La primera palanca es siempre la más simple.** Se agregan instancias antes de optimizar código; se cachea antes de particionar; se replica lectura antes de fragmentar la base de datos.

---

## Reglas

### Escalado horizontal de aplicación

- Los contenedores de `apps/api`, `apps/web` (SSR) y `apps/admin` son stateless: cualquier instancia puede atender cualquier request sin depender de sesión ni cache local en disco.
- El escalado horizontal (agregar o quitar instancias) es la respuesta por defecto ante más tráfico concurrente, antes de considerar aumentar recursos de una sola instancia (escalado vertical).
- Los workers de colas (Horizon) escalan de forma independiente a los servidores web: un pico de trabajo asíncrono (ej. sincronización masiva multicanal) no debe requerir agregar servidores de request HTTP, y viceversa.

### Cache con Redis

- Redis cumple tres roles separados conceptualmente aunque compartan infraestructura: cache de datos de lectura frecuente, backend de colas (Horizon), y almacenamiento de sesión.
- Se cachean en Redis: catálogos de solo lectura de alta frecuencia (listado de categorías, configuración de tenant), resultados de cálculos costosos con vida útil clara (ej. cotización de envío reciente, agregados de dashboard), y datos de sesión.
- Toda clave de cache multi-tenant incluye el `tenant_id` como parte de la clave (ej. `tenant:{id}:catalogo:categorias`), para que invalidar o purgar el cache de un tenant nunca afecte a otro.
- El cache se invalida explícitamente ante el evento de dominio correspondiente (ej. `PrecioActualizado` invalida el cache de la ficha de ese producto), no solo por expiración temporal — la expiración por tiempo es una red de seguridad, no la estrategia principal.

### Colas para trabajo pesado

- Toda operación que sincroniza canales, genera contenido con IA, o calcula reportes se ejecuta en un Job de Horizon, nunca en el ciclo de request (ver `vision-tecnica.md`, principio 5).
- Las colas se separan por criticidad y perfil de carga: una cola para trabajo sensible al tiempo (ej. propagar stock a marketplaces) y otra para trabajo tolerante a demora (ej. recalcular un reporte de tendencias), de forma que un pico en una no bloquee a la otra.
- El número de workers por cola escala de forma independiente según su propia métrica de saturación (ver más abajo).

### CDN de Cloudflare

- Todo el contenido estático y las páginas SSG/ISR de `apps/web` se sirven detrás de Cloudflare, de forma que el tráfico de catálogo de alto volumen (ej. una campaña de publicidad exitosa) llegue a la CDN y no a los contenedores de aplicación en la mayoría de los casos.
- Cloudflare también cumple rol de WAF (Web Application Firewall) y mitigación de tráfico anómalo/bots antes de que ese tráfico alcance la capa de aplicación (complementario al rate limiting de la propia API, ver `apis.md`).
- Los activos de `apps/admin`, al no tener necesidad de SEO ni de servir a visitantes anónimos masivos, no dependen de la misma estrategia de cache agresivo de CDN.

### Réplicas de lectura de PostgreSQL

- El punto de partida es una sola instancia de PostgreSQL (primaria) para todas las lecturas y escrituras — no se introduce complejidad de réplicas antes de necesitarla.
- Se introduce una réplica de lectura cuando las consultas de solo lectura de alto volumen (ej. dashboards de `Analítica`, catálogo público en los casos que no estén ya resueltos por cache/CDN) empiecen a competir con las escrituras críticas de negocio (confirmar pedidos, descontar stock) por los mismos recursos de la primaria.
- Las consultas que van a réplica de lectura son las que toleran una pequeña latencia de replicación (reportes, listados no críticos); las operaciones que requieren consistencia inmediata (confirmar un pedido, verificar stock antes de vender) siempre leen de la primaria.

### Qué se mide para saber cuándo escalar

- **Aplicación (contenedores):** CPU y memoria por instancia, latencia p95/p99 de request, tasa de error — se agregan instancias antes de que la latencia p95 se degrade de forma sostenida, no después.
- **Colas:** profundidad de cola (jobs pendientes) y tiempo de espera antes de procesarse — una cola con jobs esperando más de lo aceptable para su criticidad dispara agregar workers.
- **Cache:** tasa de aciertos (hit rate) de Redis — una tasa de aciertos baja en una clave de alto tráfico es señal de revisar la estrategia de cacheo antes de escalar la base de datos.
- **Base de datos:** utilización de CPU/IO de la primaria, tiempo de espera de locks, y proporción de lecturas vs. escrituras — una primaria saturada por lecturas es la señal concreta para introducir una réplica.

---

## Ejemplos

- **Campaña publicitaria exitosa un fin de semana:** el tráfico a la ficha de producto lo absorbe Cloudflare porque la página es SSG/ISR; el tráfico que sí llega a `apps/api` (agregar al carrito, checkout) se atiende agregando instancias de `apps/api` de forma horizontal, sin cambios de código.
- **Sincronización multicanal en horas pico:** un aumento en el volumen de cambios de stock (muchas ventas simultáneas en varios canales) aumenta la profundidad de la cola de `Canales`; Horizon escala el número de workers de esa cola específica sin afectar la cola de generación de contenido con IA.
- **Dashboard de Analítica lento por muchos tenants activos:** en vez de optimizar cada query manualmente primero, se evalúa introducir una réplica de lectura dedicada a las consultas de `Analítica`, dejando la primaria libre para el tráfico transaccional de `Pedidos` e `Inventario`.

---

## Casos límite

- **Un tenant grande genera un pico de trabajo en cola que satura a los demás tenants** (ej. una resincronización masiva de catálogo): se evalúa una cola dedicada o con prioridad por tenant para que un tenant grande no degrade la experiencia de los tenants pequeños que comparten la misma infraestructura.
- **Cache desincronizado tras un fallo de invalidación** (ej. un evento de actualización de precio no dispara la invalidación esperada): se mitiga con un tiempo de expiración máximo como red de seguridad (nunca cache "eterno"), y con monitoreo de discrepancias entre el valor cacheado y el real en flujos críticos como precios.
- **Réplica de lectura con retraso de replicación notable bajo carga:** las consultas que dependan de datos recién escritos (ej. mostrar el pedido recién creado) siguen leyendo de la primaria explícitamente, nunca asumen que la réplica ya tiene el dato.

---

## Decisiones futuras

- Umbrales numéricos exactos (CPU, latencia, profundidad de cola) que disparan cada decisión de escalar — se calibrarán con datos reales de operación del piloto, no de forma especulativa.
- Herramienta de autoescalado horizontal (basado en métricas de contenedor) una vez exista tráfico real que lo justifique; hoy el escalado puede ser manual/operativo.
- Introducción de un CDN o cache adicional específico para respuestas de API de solo lectura de alto tráfico (más allá del cache de aplicación en Redis), si se identifica esa necesidad.
- Estrategia de sharding o partición de tenants entre múltiples bases de datos si el crecimiento de tenants supera lo que réplicas de lectura pueden resolver (ver `base-de-datos.md`, particionamiento).

---

## Referencias

- `docs/architecture/vision-tecnica.md` — principios de stateless y trabajo asíncrono en cola que esta estrategia de escala aplica.
- `docs/architecture/base-de-datos.md` — particionamiento y aislamiento de tenants grandes, complementario a réplicas de lectura.
- `docs/architecture/infraestructura.md` — dónde y cómo corren los contenedores que se escalan aquí.
- `docs/architecture/apis.md` — rate limiting como mecanismo complementario de protección ante picos anómalos.

---

## Historial

- **2026-07-27** — Primera versión.
