# Checklist Operativo de Seguridad

## Propósito

Dar a cualquier desarrollador de Proyecto Alfa las reglas concretas y accionables de seguridad que debe seguir todos los días: qué nunca se loguea, qué nunca se commitea, cómo se manejan credenciales de terceros, y qué validación es obligatoria en todo endpoint. `docs/architecture/seguridad.md` explica el porqué y el diseño general de la postura de seguridad; este documento da el checklist operativo que se sigue sin tener que releer la justificación cada vez.

---

## Objetivo

Que ningún incidente de seguridad en Proyecto Alfa ocurra por desconocer una regla básica ya escrita — que la brecha, si ocurre, sea por un caso nuevo no cubierto todavía, no por repetir un error ya prevenido aquí.

---

## Alcance

Cubre: manejo de datos sensibles en logs, manejo de secretos y credenciales (propias y de terceros: transportadoras, marketplaces, proveedores de IA, pasarela de pago), validación de entrada obligatoria, y prácticas mínimas de autenticación/autorización a nivel de código.

No cubre: la arquitectura de seguridad completa (modelo de amenazas, postura de red, cifrado en tránsito/reposo a nivel de infraestructura) — eso vive en `docs/architecture/seguridad.md`. Este documento no repite el porqué, solo el qué hacer.

---

## Problema que resuelve

La mayoría de incidentes de seguridad en aplicaciones como Proyecto Alfa no vienen de ataques sofisticados: vienen de una contraseña en un commit, un log que imprime el número de tarjeta completo, un endpoint que confía en un `tenant_id` enviado por el cliente, o una dependencia desactualizada con una vulnerabilidad conocida. En un sistema multi-tenant, el costo de estos errores es mayor: una fuga no afecta a un solo negocio, puede exponer datos de todos los tenants a la vez.

---

## Principios

1. **Nunca confiar en el cliente.** Todo dato que llega en un request (incluido el `tenant_id`, un ID de recurso, o un rol) se revalida en el servidor; el frontend puede ocultar un botón, nunca es la última línea de defensa.
2. **Los secretos no viven en el código ni en el historial de git.** Si un secreto llegó a un commit, se considera comprometido aunque se borre después — hay que rotarlo.
3. **El log es para depurar, no para auditar datos personales.** Un log nunca debe convertirse en una copia no cifrada de información sensible.
4. **Fallar cerrado, no abierto.** Ante una duda de autorización o un servicio externo caído, el sistema deniega o reintenta de forma segura; nunca "deja pasar por si acaso".
5. **Toda integración externa es un riesgo hasta que se aísla.** Un proveedor de IA, una transportadora o un marketplace comprometidos no deben poder afectar a un tenant que no los usa.

---

## Reglas

### Datos sensibles en logs

- Nunca se loguean: contraseñas (ni siquiera hasheadas), tokens de autenticación completos, números de tarjeta, CVV, datos completos de documento de identidad, o el cuerpo completo de un request/response que contenga esos campos.
- Un log de error que incluye el payload de un request debe pasar por un filtro de campos sensibles (`password`, `token`, `card_number`, etc.) antes de escribirse, no confiar en que "no va a pasar nada".
- Los logs de un tenant nunca se mezclan sin `tenant_id` explícito en el registro — un log sin tenant identificable es tan inútil como uno sin timestamp.

### Secretos y credenciales

- Ningún secreto (API key, contraseña de base de datos, token de terceros) se commitea al repositorio, ni siquiera en un archivo de ejemplo con valor real. `.env` está en `.gitignore`; solo se versiona `.env.example` con placeholders.
- Las credenciales de servicios de terceros (transportadoras, marketplaces, proveedores de IA, pasarela de pago) se guardan como variables de entorno o en un gestor de secretos (nunca en base de datos en texto plano, nunca en código); el detalle de dónde vive cada una se define en `docs/architecture/infraestructura.md` y se referencia desde `templates/nueva-api.md` al integrar cada servicio nuevo.
- Si un secreto se filtra (commit, log, canal de chat), se rota de inmediato — borrar el commit del historial no es suficiente, el secreto ya se considera expuesto.
- Las credenciales son por entorno (desarrollo, staging, producción) y nunca se comparten entre ellos; un desarrollador no necesita ni debe tener acceso a las credenciales de producción para trabajar localmente.

### Validación de entrada

- Todo endpoint de la API valida su entrada con una clase de validación explícita (`FormRequest` en Laravel) antes de que el controlador toque cualquier dato — nunca se confía en que el frontend ya validó.
- La validación cubre tipo, formato, rango y pertenencia al tenant correcto (ej. que un `producto_id` recibido realmente pertenezca al tenant autenticado, no solo que exista en la tabla).
- Un endpoint que recibe un archivo (ej. imagen de producto) valida tipo MIME real (no solo extensión) y tamaño máximo antes de aceptarlo.
- Toda salida que se renderiza como HTML (o se reinyecta en `apps/web`/`apps/admin`) se escapa correctamente para prevenir XSS; nunca se interpola texto de usuario sin escapar.

### Autenticación y autorización

- Toda ruta de la API que no sea explícitamente pública requiere autenticación (Sanctum) antes de resolverse; el default es "requiere autenticación", una ruta pública es la excepción documentada, no al revés.
- Toda operación que modifica o lee datos de un recurso específico verifica que ese recurso pertenece al tenant del usuario autenticado, además de verificar el permiso sobre la acción — dos chequeos, no uno.
- No se comparten cuentas de usuario ni tokens entre desarrolladores, ni en desarrollo ni en producción.

### Dependencias e infraestructura

- Las dependencias de Composer y npm se actualizan con regularidad; una alerta de seguridad de Dependabot/`composer audit`/`npm audit` sobre una vulnerabilidad crítica se atiende antes que trabajo de funcionalidad nueva.
- Ningún dato real de un tenant (piloto o futuro) se usa como dato de prueba en un entorno de desarrollo compartido o en un test versionado; se usan datos sintéticos (ver `docs/development/testing.md`).

---

## Ejemplos

- Al integrar una transportadora nueva, su API key se guarda como `SERVIENTREGA_API_KEY` en el gestor de secretos del entorno correspondiente, se referencia desde `config/services.php`, y nunca aparece en el adaptador que implementa `TransportadoraInterface`.
- Un endpoint `PATCH /api/v1/pedidos/{id}` recibe el `{id}` en la URL, pero el servicio que lo atiende igual verifica `pedido.tenant_id === request.tenant_id` antes de tocar el registro, aunque el `id` exista en la base de datos.
- Un error de la pasarela de pago se loguea con el código de error y el `pedido_id`, pero nunca con el número de tarjeta ni el CVV recibidos.

---

## Casos límite

- **Un desarrollador necesita datos reales para depurar un bug reportado por el tenant piloto:** se accede a través de un mecanismo de soporte auditado (con registro de quién accedió y cuándo), nunca copiando datos de producción a un entorno local.
- **Una integración externa exige loguear el payload completo para su propio soporte técnico:** se sanitizan los campos sensibles antes de enviarlo, incluso al proveedor externo.
- **Un secreto se necesita en un pipeline de CI (GitHub Actions):** se guarda como secret de GitHub Actions, nunca como variable de entorno en el archivo de workflow versionado.

---

## Decisiones futuras

- Herramienta de escaneo automático de secretos en cada PR (ej. gitleaks) integrada a GitHub Actions.
- Política formal de rotación periódica de credenciales de terceros, más allá de la rotación reactiva ante una filtración.
- Mecanismo concreto de acceso auditado a datos de producción para soporte/depuración, referenciado en `docs/architecture/seguridad.md`.

---

## Referencias

- `docs/architecture/seguridad.md` — postura y diseño general de seguridad (el porqué).
- `docs/architecture/apis.md` y `docs/standards/api.md` — resolución de tenant y autenticación a nivel de contrato de API.
- `templates/nueva-api.md` — checklist para integrar una API externa nueva, incluida la ubicación de sus credenciales.
- `docs/development/testing.md` — manejo de datos sintéticos en tests.

---

## Historial

- **2026-07-27** — Primera versión.
