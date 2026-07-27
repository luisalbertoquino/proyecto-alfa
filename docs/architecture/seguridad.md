# Seguridad

## Propósito

Fijar la postura de seguridad de Proyecto Alfa: cómo se autentica y autoriza a un usuario, cómo se garantiza el aislamiento estricto entre tenants, qué riesgos del OWASP Top 10 son relevantes para esta plataforma y cómo se mitigan, cómo se manejan los secretos, y qué acciones sensibles quedan registradas en auditoría. El detalle operativo (checklist exacto por PR, herramientas específicas de escaneo) puede vivir en `docs/standards/` o `docs/development/` cuando exista; este documento fija el porqué y las reglas base.

---

## Objetivo

Que ningún tenant pueda ver, modificar o inferir la existencia de datos de otro tenant bajo ninguna circunstancia, que cada acción sensible sea atribuible a quién la hizo y cuándo, y que el sistema resista las clases de ataque más comunes contra una plataforma de comercio electrónico con dinero, datos personales e integraciones externas de por medio.

---

## Alcance

Cubre: autenticación, autorización basada en roles (RBAC) con aislamiento entre tenants, mitigación de riesgos OWASP Top 10 relevantes, manejo de secretos, y registro de auditoría de acciones sensibles.

No cubre: el contrato exacto de headers de autenticación (`apis.md`), el detalle de infraestructura donde corren los secretos (`infraestructura.md`), ni el esquema de tablas de auditoría (`base-de-datos.md`).

---

## Problema que resuelve

Una plataforma multi-tenant que maneja pedidos, pagos y datos de clientes de varios negocios distintos tiene un requisito de seguridad que una aplicación single-tenant no tiene: una fuga entre tenants no es un bug, es una violación de confianza que puede destruir el negocio SaaS antes de empezar. Además, sin registro de auditoría, un cambio indebido en un pedido o un acceso administrativo indebido no se puede investigar después del hecho. Este documento fija las reglas que hacen que ambos problemas sean estructuralmente difíciles de introducir, no solo "responsabilidad de tener cuidado".

---

## Principios

1. **El aislamiento entre tenants no es responsabilidad del desarrollador en cada query, es una garantía de la arquitectura.** El scoping por `tenant_id` se aplica de forma automática y transversal, no como disciplina manual que alguien puede olvidar (ver nota de coherencia con `base-de-datos.md` y `arquitectura-backend.md`).
2. **Ningún secreto vive en el repositorio.** Ni en código, ni en `.env` versionado, ni en configuración de ejemplo con valores reales.
3. **Autorización explícita, no implícita.** Toda acción sensible se autoriza contra rol y tenant, nunca se asume permitida por defecto.
4. **Lo sensible se audita, no se infiere después de los logs generales.** Cambios de pedidos, accesos administrativos y cambios de configuración de tenant quedan registrados de forma específica y consultable.
5. **Se defiende en profundidad.** Ninguna mitigación depende de una sola capa (ej. no confiar solo en validación de frontend, ni solo en un firewall).

---

## Reglas

### Autenticación

- La autenticación de usuarios de `apps/admin` y de clientes de `apps/web` usa tokens (Laravel Sanctum) sobre HTTPS exclusivamente; nunca credenciales en texto plano en tránsito.
- Las contraseñas se almacenan con hash fuerte (bcrypt/argon2, según el estándar vigente de Laravel), nunca en texto plano ni con hash reversible.
- Los tokens tienen expiración y se pueden revocar (ej. al cerrar sesión, al detectar actividad sospechosa, al cambiar contraseña).
- Detalle exacto de mecanismo de token, headers y expiración: `docs/architecture/apis.md` y, a nivel operativo, `docs/standards/api.md`.

### Autorización basada en roles y aislamiento entre tenants

- Todo usuario pertenece a un tenant y a uno o más roles dentro de ese tenant (ej. administrador, operador de pedidos, solo-lectura); los permisos se evalúan por rol, nunca por "el usuario está autenticado" a secas.
- Toda autorización se evalúa en dos niveles simultáneos: ¿tiene el rol el permiso para esta acción? y ¿pertenece el recurso solicitado al mismo `tenant_id` del usuario? — un fallo en cualquiera de los dos niveles deniega el acceso.
- El aislamiento entre tenants se refuerza a nivel de aplicación (scoping automático de queries por `tenant_id`, ver `arquitectura-backend.md` y `base-de-datos.md`) y se valida con pruebas explícitas multi-tenant (ver `principios-de-arquitectura.md`, principio 6): ningún test asume que solo existe un tenant.
- Un identificador de recurso predecible (ej. `/api/v1/pedidos/1042`) nunca es suficiente para acceder a él — el backend valida pertenencia al tenant del usuario autenticado en cada acceso, no solo la existencia del recurso.

### Mitigación de OWASP Top 10 relevantes

- **Control de acceso roto (A01):** cubierto por el modelo de RBAC + aislamiento por tenant descrito arriba, verificado con tests que intentan cruzar tenants deliberadamente.
- **Fallas criptográficas (A02):** HTTPS obligatorio en todos los entornos expuestos (reforzado por Cloudflare), hash fuerte de contraseñas, cifrado de datos sensibles en reposo cuando aplique (ej. datos de integración con pasarelas de pago).
- **Inyección (A03):** uso exclusivo del ORM/Query Builder de Laravel con bindings parametrizados; ninguna query construida por concatenación de entrada de usuario.
- **Diseño inseguro (A04):** las reglas de este documento y de `arquitectura-backend.md`/`base-de-datos.md` se piensan como parte del diseño, no como revisión posterior.
- **Configuración de seguridad incorrecta (A05):** configuración por entorno versionada como código (ver `infraestructura.md`), sin valores por defecto inseguros expuestos en producción (modo debug desactivado, cabeceras de seguridad HTTP activas).
- **Componentes vulnerables desactualizados (A06):** dependencias de Laravel/Next.js con actualizaciones de seguridad monitoreadas y aplicadas de forma regular (mecanismo exacto en `docs/development/ci-cd.md`).
- **Fallas de identificación y autenticación (A07):** tokens con expiración y revocación, límite de intentos de login (rate limiting específico, ver `apis.md`).
- **Fallas de integridad de software y datos (A08):** el código desplegado (build de `composer install --no-dev` y `npm run build`) proviene siempre de un commit validado por el pipeline oficial (ver `infraestructura.md`), nunca de cambios ensamblados o editados a mano directamente en el servidor.
- **Fallas de registro y monitoreo (A09):** cubierto por la estrategia de auditoría descrita abajo, más monitoreo de errores e intentos de acceso anómalos.
- **Server-Side Request Forgery (A10):** relevante por las integraciones externas del sistema (transportadoras, marketplaces, proveedor de IA); toda llamada saliente a un servicio externo pasa por el adaptador de su módulo (`TransportadoraInterface`, `MarketplaceInterface`, `ProveedorIAInterface`, ver `principios-de-arquitectura.md`), nunca por una URL construida dinámicamente a partir de entrada de usuario sin validar.

### Manejo de secretos

- Ningún secreto (credenciales de base de datos, claves de API de transportadoras/marketplaces/proveedor de IA, claves de firma de tokens) se versiona en el repositorio, ni siquiera en archivos de ejemplo con valores reales.
- Los secretos se inyectan por variables de entorno gestionadas por la plataforma de infraestructura/CI-CD (ver `infraestructura.md`), con acceso restringido por entorno (los secretos de producción no son accesibles desde staging ni local).
- Rotación de secretos ante sospecha de compromiso o cambio de personal con acceso, como procedimiento definido, no reactivo únicamente.

### Auditoría de acciones sensibles

- Se registran, como mínimo: cambios de estado de un pedido (creación, confirmación, cancelación, modificación de dirección/monto), accesos y cambios de configuración administrativa (alta/baja de usuarios, cambios de rol), y accesos de soporte/administración de Proyecto Alfa a datos de un tenant específico.
- Todo registro de auditoría incluye: quién (usuario y rol), qué (acción y recurso afectado), cuándo, desde dónde (IP/origen cuando sea relevante), y el `tenant_id` correspondiente.
- Los registros de auditoría son de solo append — no se editan ni se borran por la operación normal del sistema, y su acceso de lectura está restringido y también auditado.
- El registro de auditoría es independiente del log técnico general de la aplicación: existe para responder "¿quién hizo qué" sobre datos de negocio, no para depurar errores de sistema.

---

## Ejemplos

- Un operador con rol "solo-lectura" del tenant A intenta modificar un pedido vía la API: la autorización lo rechaza en la capa de Policy antes de llegar a cualquier lógica de negocio, y el intento queda registrado.
- Un usuario autenticado del tenant B solicita `/api/v1/pedidos/1042`, que pertenece al tenant A: la API responde como si el recurso no existiera (no revela que existe pero pertenece a otro tenant), y el intento se registra para revisión si ocurre de forma repetida.
- Un administrador de Proyecto Alfa accede al panel de un tenant específico para dar soporte: ese acceso queda registrado en auditoría con su identidad y el tenant accedido, no ocurre de forma silenciosa.
- Un cambio de dirección de envío en un pedido ya confirmado queda registrado con el valor anterior y el nuevo, quién lo hizo y cuándo — relevante tanto para soporte al cliente como para disputas.

---

## Casos límite

- **Token válido robado o filtrado:** el mecanismo de revocación de tokens y la posibilidad de cerrar todas las sesiones activas de un usuario deben existir y ser accesibles rápidamente ante sospecha de compromiso.
- **Rol con permisos amplios mal asignado por error humano** (ej. dar rol de administrador a un usuario de soporte): el registro de auditoría de cambios de rol permite detectar y corregir esto después del hecho; la separación de roles limita el daño mientras tanto.
- **Integración externa comprometida** (ej. credenciales de una transportadora filtradas): al estar aisladas detrás de su interfaz y sus propios secretos por integración, revocar y rotar esa credencial específica no debe afectar a las demás integraciones.
- **Solicitud de un tenant para ver su propio registro de auditoría** (transparencia hacia el cliente del modelo SaaS): se trata como una decisión de producto a definir, no como una prohibición categórica — ver Decisiones futuras.

---

## Decisiones futuras

- Nivel de acceso que el propio tenant tiene a su registro de auditoría (autoservicio) vs. acceso restringido solo a soporte de Proyecto Alfa.
- Herramienta de escaneo de vulnerabilidades y dependencias integrada al pipeline (SAST/dependency scanning) y su nivel de bloqueo sobre el CI.
- Política formal de retención y cifrado de datos personales de clientes finales (compradores en la tienda) conforme crezca la exposición del negocio piloto y, después, del modelo SaaS — relevante para eventual cumplimiento normativo de protección de datos en Colombia/LatAm.
- Autenticación multifactor para roles administrativos, evaluada como requisito antes de abrir el modelo SaaS a terceros.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — principio API-first y multi-tenant que esta postura de seguridad protege.
- `docs/architecture/base-de-datos.md` — scoping por `tenant_id` a nivel de datos.
- `docs/architecture/arquitectura-backend.md` — Policies y capa de autorización dentro de cada módulo.
- `docs/architecture/apis.md` — autenticación por token y rate limiting como parte del contrato de API.
- `docs/architecture/infraestructura.md` — dónde y cómo se gestionan los secretos por entorno.

---

## Historial

- **2026-07-27** — Primera versión.
- **2026-07-27** — Actualizado: MySQL en vez de PostgreSQL y despliegue nativo en vez de Docker — ver ADR-002.
