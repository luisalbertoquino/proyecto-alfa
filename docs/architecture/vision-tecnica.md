# Visión Técnica

## Propósito

Definir **cómo** se construye Proyecto Alfa para que, en cinco años, con muchos tenants, muchos usuarios y muchas páginas sirviendo tráfico, el sistema siga siendo rápido, seguro y fácil de modificar. Este documento es la referencia que toda decisión de arquitectura, infraestructura o código debe respetar. Si un documento técnico posterior (`base-de-datos.md`, `apis.md`, `infraestructura.md`, etc.) contradice algo aquí, este documento gana — o se corrige aquí primero, explícitamente, con su razón.

---

## Objetivo

Construir un **monolito modular, API-first, multi-tenant desde el diseño de datos**, capaz de:

- Escalar horizontalmente para soportar muchos negocios (tenants) y mucho tráfico por página sin reescritura.
- Aislar cada capacidad de negocio (catálogo, pedidos, inventario, envíos, proveedores, publicidad, analítica, canales, IA) en módulos con fronteras claras, para que se puedan modificar, probar y — si algún día hace falta — extraer a servicio independiente sin tocar el resto.
- Mantener una única fuente de verdad de datos consumida siempre a través de la API, nunca por acceso directo a base de datos desde el frontend.

## Alcance

Cubre: patrón de arquitectura general (monolito modular vs. microservicios), estrategia multi-tenant, comunicación entre frontend y backend, manejo de trabajo asíncrono, estrategia de escalabilidad e infraestructura a alto nivel, y postura de seguridad base.

No cubre (documentos específicos): esquema exacto de tablas (`base-de-datos.md`), contratos de endpoints (`apis.md`), justificación de cada tecnología del stack, pipeline de CI/CD (`docs/development/ci-cd.md`), ni detalle de despliegue (`infraestructura.md`).

---

## Problema que resuelve

Sin una decisión explícita tomada temprano, un proyecto que empieza "para un solo negocio" termina con:

- Datos sin `tenant_id`, que exigen una migración dolorosa el día que llega el segundo cliente.
- Lógica de negocio mezclada con controladores HTTP, imposible de reusar entre canales (web, admin, futura app móvil) o de extraer si un módulo necesita escalar aparte.
- Frontend acoplado a la forma interna de la base de datos, que rompe cada vez que el backend cambia un detalle interno.
- Trabajo pesado (sincronizar inventario multicanal, generar contenido con IA, calcular reportes) ejecutándose de forma síncrona, tumbando tiempos de respuesta bajo carga.

Este documento fija las decisiones que evitan esos cuatro problemas desde el primer commit de código.

---

## Principios

1. **API-first.** Ningún cliente (web, admin, app futura) accede a la base de datos directamente. Todo pasa por la API de Laravel. Esto permite añadir clientes nuevos (móvil, portal de proveedores, portal de clientes) sin duplicar lógica de negocio.
2. **Monolito modular, no microservicios prematuros.** Un solo backend Laravel, organizado en módulos de dominio con fronteras explícitas (ver `arquitectura-backend.md`). Se evalúa extraer un módulo a servicio independiente solo cuando haya una razón medible (carga, equipo dedicado, ciclo de despliegue distinto) — no por adelantado.
3. **Multi-tenant desde el modelo de datos, aunque hoy exista un solo tenant.** Toda tabla que almacene datos de negocio incluye `tenant_id` desde su primera migración. El piloto opera como tenant único, pero el esquema no requiere reescritura para soportar el segundo.
4. **Stateless por defecto.** Los servidores de aplicación no guardan estado en memoria ni en disco local; sesión y cache viven en Redis. Esto permite escalar horizontalmente agregando instancias detrás de un balanceador sin pegajosidad de sesión.
5. **Lo pesado va a cola, no al request.** Cualquier operación que no necesite responder en el mismo ciclo HTTP (sincronizar canales, generar contenido con IA, calcular reportes, enviar notificaciones) se procesa de forma asíncrona.
6. **Todo cambio de arquitectura relevante se documenta como ADR.** No se navega de memoria ni por convención tácita — ver `docs/adr/`.

---

## Reglas

- Toda tabla de negocio nueva incluye `tenant_id` indexado; una tabla sin `tenant_id` requiere justificación explícita (ej. tablas verdaderamente globales como catálogos de países).
- Ningún módulo de dominio importa clases internas de otro módulo directamente; la comunicación entre módulos ocurre por eventos internos o por una interfaz de servicio explícita.
- Ninguna vista de Next.js consulta la base de datos ni un servicio externo (transportadora, marketplace, IA) directamente: siempre pasa por la API propia.
- Toda operación que tarde más de ~500ms o dependa de un servicio externo (marketplace, transportadora, proveedor de IA) se despacha a una cola, no se ejecuta en el request.
- Toda decisión que cambie algo de este documento se registra primero como ADR en `docs/adr/`.

---

## Ejemplos

- **Sincronización multicanal:** cuando se actualiza el stock de un producto, el evento `StockActualizado` se publica internamente; un *listener* en cola propaga el cambio a TikTok Shop y Mercado Libre, sin bloquear la respuesta al usuario que originó el cambio.
- **Nuevo cliente móvil futuro:** al no existir acceso directo a base de datos desde ningún frontend, una futura app móvil consume exactamente los mismos endpoints que `apps/web` y `apps/admin`, sin lógica de negocio duplicada.
- **Extracción de un módulo:** si el módulo de IA (generación de descripciones) necesita GPUs o un ciclo de despliegue distinto al resto, su frontera ya definida como módulo permite convertirlo en servicio aparte sin reescribir el resto del backend.

---

## Casos límite

- **Un tenant con volumen mucho mayor al resto** (ej. el piloto crece 100x): la estrategia inicial de "misma base de datos, `tenant_id` compartido" debe poder evolucionar a aislamiento por schema o base de datos dedicada para ese tenant específico, sin migrar a todos los demás. Ver Decisiones futuras.
- **Pico de tráfico en una sola página** (ej. campaña de publicidad exitosa): al ser stateless y sin estado local, escalar horizontalmente el número de contenedores de `apps/web` y de la API debe bastar sin cambios de código.
- **Caída de un servicio externo crítico** (pasarela de pago, transportadora, marketplace): el módulo que lo integra debe fallar de forma aislada (circuit breaker / reintentos en cola) sin tumbar el resto del sistema.

---

## Decisiones futuras

- Umbral de tráfico o número de tenants a partir del cual se evalúa aislar un tenant grande en su propio schema o base de datos.
- Punto en el que un módulo de dominio se extrae a servicio independiente (candidato más probable: IA, por su perfil de cómputo distinto).
- Adopción de un bus de eventos externo (ej. para desacoplar aún más los módulos) si el monolito modular con eventos internos deja de ser suficiente.
- Estrategia de versión de API cuando exista más de un cliente externo (app móvil, integraciones de terceros en el modelo SaaS).

---

## Referencias

- [`docs/business/vision-producto.md`](../business/vision-producto.md) — el qué y el porqué de negocio que esta visión técnica sirve.
- `docs/architecture/principios-de-arquitectura.md` — principios de diseño de código a nivel de módulo.
- `docs/architecture/arquitectura-backend.md`, `arquitectura-frontend.md`, `base-de-datos.md`, `apis.md`, `escalabilidad.md`, `infraestructura.md`, `seguridad.md` — desarrollo de cada decisión tomada aquí.
- `docs/adr/ADR-001.md` — decisión de stack tecnológico.

---

## Historial

- **2026-07-27** — Primera versión. Fija monolito modular API-first, multi-tenant desde el modelo de datos, stateless, y trabajo asíncrono por cola como decisiones base del proyecto.
