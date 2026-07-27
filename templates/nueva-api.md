# Plantilla: Nueva Integración de API Externa

Copia este archivo al integrar una API externa nueva (transportadora, marketplace, proveedor de IA, pasarela de pago). El principio rector: la integración se implementa **detrás de una interfaz propia**, para poder reemplazarla sin tocar el código que la usa (`principios-de-arquitectura.md`, principio 4).

---

## 1. Datos básicos

- **Servicio externo:** `{{ej. Servientrega, Mercado Libre, OpenAI}}`
- **Módulo que la integra:** `{{Envios | Canales | IA | ...}}`
- **Qué resuelve para el negocio (una frase):** `{{...}}`
- **Documentación del proveedor:** `{{URL}}`
- **¿Existe research previo?** `{{enlace a docs/research/... si aplica, o "No — agregar antes de integrar"}}`

---

## 2. Interfaz propia

- **Nombre de la interfaz** (ver `docs/standards/naming.md`: negocio en español + sufijo `Interface`): `{{TransportadoraInterface | MarketplaceInterface | ProveedorIAInterface}}`
- **Métodos que define** (ya existentes en el módulo si aplica, o nuevos):

  | Método | Qué hace | Qué devuelve |
  |---|---|---|
  | `{{cotizar()}}` | `{{...}}` | `{{...}}` |

- [ ] La interfaz se escribió **antes** del primer adaptador concreto.
- [ ] El resto del módulo (servicios, controladores) depende solo de la interfaz, nunca de la clase concreta del proveedor.

---

## 3. Credenciales

- **Cómo se llaman las variables de entorno:** `{{PROVEEDOR_API_KEY, PROVEEDOR_API_SECRET}}`
- **Dónde viven en cada entorno** (dev/staging/producción): `{{gestor de secretos / .env local}}`
- [ ] Ningún secreto commiteado; `.env.example` solo tiene placeholders (ver `docs/standards/security.md`).
- [ ] Credenciales distintas por entorno, sin compartir entre dev y producción.

---

## 4. Manejo de fallos y reintentos

- **¿Qué pasa si el servicio externo no responde o responde con error?** `{{...}}`
- **Política de reintentos:** `{{cuántos intentos, backoff, en cola o síncrono}}`
- **¿Se ejecuta en el request o en cola?** Recordatorio: toda llamada externa que pueda tardar >500ms va en cola, no en el ciclo HTTP (`vision-tecnica.md`, regla).
- **Circuit breaker / aislamiento:** ¿cómo evita que la caída de este proveedor tumbe otras operaciones del sistema? `{{...}}`
- **¿Qué ve el usuario/tenant cuando el proveedor falla?** `{{mensaje de error, degradación, reintento automático}}`

---

## 5. Datos sensibles

- [ ] Payloads enviados/recibidos de este proveedor no incluyen datos sensibles sin sanitizar en logs (`docs/standards/security.md`).
- [ ] Si el proveedor recibe datos personales de clientes (ej. dirección para cotizar envío), se documentó qué datos exactamente y por qué es necesario.

---

## 6. Tests

- [ ] El adaptador concreto tiene test contra un mock/fake del servicio externo (no contra el servicio real en CI).
- [ ] Existe un test que verifica que el módulo funciona correctamente cuando el proveedor falla (timeout, error 500, respuesta inesperada).

---

## 7. Documentación a actualizar

- [ ] Si no existía, se creó una nota en `docs/research/` con la evaluación del proveedor (por qué este y no otro).
- [ ] `docs/business/modulos.md` o `docs/architecture/arquitectura-funcional.md` reflejan la integración nueva si cambia el alcance del módulo.
- [ ] Si la elección del proveedor es una decisión relevante (costo, dependencia estratégica), se registró como ADR.

---

## 8. Referencias

- `docs/architecture/principios-de-arquitectura.md` — principio de diseño "para reemplazar, no solo para extender".
- `docs/standards/security.md` — manejo de credenciales de terceros.
- `docs/architecture/vision-tecnica.md` — regla de trabajo pesado/externo a cola.
