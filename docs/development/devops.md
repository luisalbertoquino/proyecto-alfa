# DevOps

## Propósito

Definir cómo opera Proyecto Alfa una vez desplegado: qué entornos existen y cómo se relacionan entre sí, qué se debe poder observar en producción (errores, latencia, colas atascadas), cómo se manejan los logs, y qué se hace cuando un despliegue falla y hay que revertirlo.

---

## Objetivo

Que un problema en producción se detecte por una alerta, no por un cliente quejándose; que cualquier persona del equipo pueda diagnosticar un incidente con la información disponible sin depender de quien escribió el código; y que revertir un despliegue malo sea un procedimiento conocido de antemano, no algo que se improvisa bajo presión.

---

## Alcance

Cubre: definición de entornos (local, staging, producción) con Docker, qué se monitorea y qué genera alerta, manejo de logs, y estrategia de rollback ante un despliegue fallido.

No cubre: qué corre en el pipeline de CI/CD ni cómo se despliega paso a paso (`docs/development/ci-cd.md`) — este documento asume que el despliegue ya ocurrió (o falló) y se enfoca en operar el sistema después; tampoco cubre el detalle de infraestructura física/cloud (`docs/architecture/infraestructura.md`, en construcción).

---

## Problema que resuelve

Un sistema que centraliza pedidos, inventario y sincronización multicanal de varios negocios (multi-tenant) falla de forma costosa y silenciosa si nadie lo está observando: una cola de sincronización de inventario atascada no tumba el sitio — simplemente deja de reflejar stock real hasta que alguien nota la sobreventa días después. Sin entornos claramente separados, un cambio se prueba "en producción sin querer". Sin logs estructurados, diagnosticar un incidente multi-tenant ("¿a cuántos tenants afectó esto?") es adivinar. Sin un plan de rollback conocido, cada despliegue fallido se convierte en una crisis improvisada.

---

## Principios

1. **Todo entorno corre en Docker con la misma forma**, para que "funciona en mi máquina" y "funciona en staging" signifiquen lo mismo que "funciona en producción" salvo por escala y credenciales.
2. **Si algo puede fallar en silencio, se instrumenta para que no pueda.** Trabajo asíncrono en colas (ver `vision-tecnica.md`), sincronización multicanal, y jobs de IA son exactamente el tipo de trabajo que falla sin que nadie lo note si no hay monitoreo activo.
3. **Los logs y métricas se piensan multi-tenant desde el diseño**, igual que los datos: se puede filtrar y alertar por tenant, no solo de forma agregada, porque un incidente que afecta a un tenant específico no debe esconderse en un promedio saludable.
4. **Toda alerta tiene un dueño y una acción esperada.** Una alerta que nadie sabe qué hacer con ella, o que nadie mira, es peor que no tener alerta — genera fatiga y se empieza a ignorar el canal completo.
5. **El rollback es un procedimiento conocido de antemano, ensayado, no una decisión de diseño tomada durante el incidente.**

---

## Reglas

### Entornos

- **Local:** cada desarrollador levanta el stack completo (`apps/api`, `apps/web`, `apps/admin`, PostgreSQL, Redis) con Docker Compose, con datos de ejemplo multi-tenant sembrados por seeders (nunca un solo tenant, igual que en tests — ver `docs/development/testing.md`), para que el comportamiento multi-tenant sea visible desde el día a día, no solo en CI.
- **Staging:** refleja producción en configuración e infraestructura (mismas imágenes Docker, misma topología), con datos de prueba representativos multi-tenant. Se actualiza automáticamente con cada merge a `main` (ver `docs/development/ci-cd.md`). Es el único lugar, aparte de producción, donde se valida el comportamiento con datos "reales" antes de exponerlo a clientes.
- **Producción:** sirve tráfico real de tenants reales. Solo se actualiza mediante el pipeline de despliegue aprobado (`docs/development/ci-cd.md`), nunca con cambios manuales directos en el servidor o contenedor.
- Las credenciales y secretos de cada entorno son distintos y no se comparten; un secreto de producción nunca vive en `.env` de un desarrollador ni en staging.
- Cloudflare se configura igual en staging y producción en lo que a reglas y cacheo respecta (salvo dominio), para que un problema de CDN/cache se detecte en staging antes que en producción.

### Monitoreo y alertas — qué se debe poder observar

- **Errores de aplicación:** toda excepción no controlada en `apps/api`, `apps/web` y `apps/admin` se captura y reporta a una herramienta de tracking de errores (ej. Sentry o equivalente), con el `tenant_id` afectado como campo obligatorio del contexto cuando aplica. Un error que se repite por encima de un umbral en una ventana de tiempo genera alerta.
- **Latencia:** se mide la latencia de la API por endpoint (p50/p95/p99). Un endpoint que supera su umbral esperado de forma sostenida (no un pico aislado) genera alerta — en particular los endpoints de checkout y de disponibilidad de inventario, por ser flujos críticos (ver `testing.md`).
- **Colas atascadas:** el tamaño de cada cola (sincronización de canales, generación de contenido IA, notificaciones, reportes) y la edad del job más antiguo pendiente se exponen como métrica. Una cola que crece sostenidamente, o un job que lleva más del tiempo esperado sin procesarse, genera alerta — este es el caso explícito de falla silenciosa que el principio 2 busca prevenir (ej. sincronización de inventario atascada = riesgo de sobreventa sin que nadie lo note).
- **Disponibilidad:** health checks de cada app y de sus dependencias (PostgreSQL, Redis) con alerta inmediata ante caída.
- **Uso por tenant (a nivel agregado, no solo global):** al menos para los flujos críticos, se puede desglosar la métrica por tenant, para distinguir "todo el sistema tiene un problema" de "un tenant específico tiene un problema" (ej. un tenant con un catálogo mal formado que genera errores solo para él).
- Toda alerta se dirige a un canal con dueño claro (rotación de guardia si el equipo lo justifica; mientras el equipo sea pequeño, a la persona responsable de operaciones) y documenta la acción esperada mínima (a quién escalar, qué runbook seguir) — no se crea una alerta sin definir qué se espera que alguien haga al recibirla.

### Manejo de logs

- Los logs de `apps/api` se estructuran (JSON), no texto libre, para poder filtrarse por campos: `tenant_id`, módulo de dominio, request ID, nivel de severidad.
- Todo log de una operación de negocio relevante (creación de pedido, cambio de stock, fallo de sincronización con un canal o transportadora) incluye el `tenant_id` correspondiente — nunca un log de negocio sin saber a qué tenant pertenece.
- Los logs se centralizan (no viven solo dentro de cada contenedor) porque los contenedores son efímeros y stateless (`vision-tecnica.md`); se agregan en una herramienta central con retención definida.
- Información sensible (contraseñas, tokens, datos de pago) nunca se escribe en logs, ni siquiera parcialmente ofuscada por accidente — se trata como regla de seguridad, verificable en revisión de código (`docs/development/coding-standards.md`).
- Cada request a la API lleva un identificador único (request ID) propagado a través de logs, jobs en cola que ese request haya disparado, y respuesta de error al cliente, para poder reconstruir el flujo completo de un incidente.

### Rollback

- El rollback de código es siempre a nivel de imagen Docker: se vuelve a desplegar la imagen etiquetada del despliegue anterior conocido-bueno, no se intenta "arreglar hacia adelante" bajo presión durante un incidente activo — arreglar hacia adelante es una decisión posterior, deliberada, no el default.
- El rollback de código nunca revierte una migración de base de datos ya aplicada de forma destructiva; por eso las migraciones se diseñan expansivas y compatibles hacia atrás (ver `docs/development/ci-cd.md`) — el código viejo debe poder seguir funcionando contra el esquema nuevo durante el tiempo que tome el rollback.
- Todo incidente que termina en rollback se documenta después (qué pasó, por qué, qué cambia para que no se repita) — no como culpa individual, sino como insumo para mejorar el pipeline o el monitoreo.
- El tiempo objetivo de detección (por alerta, no por reporte de cliente) y de rollback para un despliegue que rompe un flujo crítico es minutos, no horas — si en la práctica toma más, es una señal de que el monitoreo o el procedimiento de rollback necesitan revisión.

---

## Ejemplos

- La cola de sincronización con TikTok Shop empieza a acumular jobs sin procesar porque la API del canal está devolviendo error 500: la métrica de tamaño de cola dispara una alerta antes de que ningún tenant note stock desactualizado; el equipo investiga con el request ID y el `tenant_id` de los jobs fallidos en los logs centralizados.
- Un despliegue a producción introduce una regresión que hace fallar el checkout: la alerta de latencia/error en el endpoint de checkout dispara en minutos, se decide rollback a la imagen anterior, y como la migración asociada era expansiva (columna nueva nullable), el código anterior sigue funcionando sin problema contra el esquema ya migrado.
- Un tenant específico reporta que sus pedidos no se están creando mientras el resto del sistema funciona normal: gracias a que las métricas y logs se pueden filtrar por `tenant_id`, se aísla el problema (ej. un dato mal configurado de ese tenant) sin necesidad de revisar todo el sistema.

---

## Casos límite

- **Un incidente afecta a un solo tenant de forma aislada** (no un problema sistémico): las métricas agregadas pueden verse saludables; por eso el desglose por tenant en flujos críticos (principio 3) es obligatorio, no opcional, aunque el volumen actual del piloto sea de un tenant.
- **El rollback de código no es suficiente porque el problema es de datos** (ej. una migración expansiva corrió bien, pero un job de backfill dejó datos inconsistentes): se trata como incidente de datos aparte, con su propio plan de corrección, nunca intentando resolverlo revirtiendo código que no es la causa.
- **Fatiga de alertas:** si una alerta dispara repetidamente sin acción real requerida, se ajusta su umbral o se elimina — no se deja "silenciada" indefinidamente de forma tácita, porque eso es indistinguible de no tener la alerta.
- **Repositorio en transición `backend/`/`frontend/` → `apps/`:** mientras dure la migración, el monitoreo y los logs se configuran para ambas rutas de despliegue si coexisten temporalmente, documentando explícitamente cuándo se puede retirar la ruta vieja.

---

## Decisiones futuras

- Elegir herramienta concreta de tracking de errores, métricas y logs centralizados (hoy solo se fija el requisito, no el proveedor) — ver `docs/architecture/infraestructura.md` (en construcción).
- Definir SLA/SLO formales por flujo crítico una vez exista tráfico real suficiente para fijar umbrales con base en datos, no en estimación.
- Definir política de retención de logs y de datos de monitoreo, alineada con cualquier requisito legal aplicable al modelo SaaS (dato de terceros/tenants).
- Evaluar rotación de guardia (on-call) formal cuando el equipo crezca más allá de lo que sostiene una sola persona de operaciones.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — stateless por defecto, trabajo pesado en cola (por qué colas atascadas son un riesgo real a monitorear).
- `docs/development/ci-cd.md` — cómo se despliega y cómo se manejan migraciones sin downtime (condición para que el rollback aquí descrito funcione).
- `docs/development/testing.md` — definición de flujos críticos que reciben más atención de monitoreo.
- `docs/architecture/infraestructura.md` (en construcción) — detalle de infraestructura física/cloud y herramientas concretas.

---

## Historial

- **2026-07-27** — Primera versión.
