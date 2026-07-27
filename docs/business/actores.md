# Actores

## Propósito

Identificar a todas las personas (y al propio sistema, cuando actúa de forma autónoma) que interactúan con Proyecto Alfa, y fijar qué puede hacer cada una a alto nivel. Este documento usa exactamente la terminología fijada en [`diccionario-del-negocio.md`](diccionario-del-negocio.md) — en particular, distingue con cuidado al **emprendedor** (dueño de un tenant) del **cliente** (comprador final) y del **administrador de la plataforma** (equipo de Proyecto Alfa).

---

## Objetivo

Que cualquier caso de uso, historia de usuario o regla de negocio del proyecto pueda referirse a un actor concreto de esta lista, sin inventar roles nuevos sobre la marcha ni mezclar responsabilidades de dos actores en uno.

## Alcance

**Incluye:** actores humanos que interactúan directa o indirectamente con la plataforma (emprendedor, cliente, administrador de la plataforma, proveedor, transportadora, equipo de soporte), y el sistema/IA como actor cuando ejecuta acciones de forma autónoma o semi-autónoma.

**No incluye:** permisos y roles técnicos exactos (matriz de permisos, RBAC) — eso se define en `docs/architecture/` o en un documento de seguridad/autenticación cuando exista. Este documento describe el rol de negocio, no la implementación de control de acceso.

---

## Problema que resuelve

Sin una lista explícita de actores, es fácil que un caso de uso mezcle a dos roles distintos bajo una misma palabra (el error más común: usar "cliente" para el emprendedor), o que aparezcan responsabilidades sin dueño claro (¿quién resuelve una disputa con una transportadora: el emprendedor o soporte de la plataforma?). Este documento fija esos límites una sola vez para que todos los demás documentos los hereden.

---

## Principios

- **Cada actor tiene un ámbito claro.** El emprendedor actúa dentro de su tenant; el administrador de la plataforma actúa a través de tenants; el cliente actúa dentro de un canal de un tenant.
- **El sistema es un actor cuando decide, no solo cuando ejecuta.** Cuando la IA sugiere un reabastecimiento o recomienda una transportadora, es un actor con iniciativa propia, aunque el emprendedor conserve la decisión final — eso se documenta explícitamente en los casos de uso donde aplique.
- **Ningún actor externo (proveedor, transportadora) tiene acceso directo al sistema en el estado actual del piloto.** Su interacción hoy es operativa (fuera del software) o vía integraciones que el emprendedor configura; un portal propio para ellos es una posibilidad a futuro, no un compromiso actual (ver `vision-producto.md` → Decisiones futuras).
- **El piloto usa un subconjunto de estos actores.** Hoy no existe "administrador de la plataforma" como rol separado (el equipo fundador cumple ambos papeles); el rol se vuelve real y necesario a partir de la Fase 5 (SaaS).

---

## Reglas

- Todo caso de uso nuevo (`casos-de-uso.md`) debe nombrar explícitamente qué actor de esta lista lo inicia y cuáles participan.
- Un actor no puede realizar, en la documentación de negocio, una acción que no esté listada en su columna "Qué puede hacer" sin que primero se actualice este documento.
- Si aparece una necesidad de negocio que no encaja en ningún actor existente (ej. un "gestor de campañas" tercerizado), se agrega como actor nuevo aquí antes de usarse en otros documentos.

### Tabla de actores

| Actor | Rol | Qué puede hacer (alto nivel) | Dónde interactúa |
|---|---|---|---|
| **Emprendedor / vendedor** | Dueño y operador de un tenant | Gestionar catálogo, inventario, precios, pedidos, envíos, proveedores, campañas de publicidad y ver el dashboard de inteligencia comercial de su propio tenant | Panel administrativo (`apps/admin`) |
| **Cliente** | Comprador final en la tienda de un tenant | Explorar catálogo, comprar, pagar, hacer seguimiento a su pedido, solicitar devolución/cancelación según la política del tenant | Tienda virtual (`apps/web`) y canales externos (TikTok Shop, Mercado Libre) |
| **Administrador de la plataforma (Platform Admin)** | Equipo de Proyecto Alfa que opera el SaaS | Dar de alta y soporte a tenants, monitorear salud e incidencias multi-tenant, gestionar facturación de la plataforma, suspender o reactivar un tenant por incumplimiento | Panel de plataforma (futuro, Fase 5) |
| **Proveedor** | Abastece mercancía o insumos a un tenant | Ofrecer catálogo y condiciones a través del directorio de proveedores; hoy la relación comercial (pedido de compra, pago) ocurre fuera del sistema | Directorio de proveedores (consulta desde el emprendedor); sin acceso directo propio en el estado actual |
| **Transportadora** | Ejecuta el transporte físico de un pedido | Recibir la solicitud de envío generada por el emprendedor, entregar el pedido al cliente, reportar estado (recogido, en tránsito, entregado, novedad) | Integración vía API del módulo de logística (comparador de transportadoras); sin panel propio en el estado actual |
| **Equipo de soporte** | Atiende incidencias operativas del piloto y, a futuro, de tenants del SaaS | Resolver dudas y problemas del emprendedor (hoy) y de emprendedores-tenant (a futuro); escalar incidencias técnicas al equipo de plataforma | Canal de soporte (hoy informal; formalizado en fases posteriores) |
| **Sistema / IA** | Actor no humano que automatiza tareas | Generar descripciones de producto, responder preguntas frecuentes, sugerir reabastecimiento, recomendar transportadora, sincronizar inventario entre canales | Todos los módulos, de forma transversal, siempre con el emprendedor como responsable final de aprobar o revertir una acción cuando el proceso lo requiera |

---

## Ejemplos

- El **emprendedor** entra al panel administrativo, ve que un SKU tiene alta rotación porque el **sistema/IA** lo marcó en el dashboard, y decide subir una orden de compra a un **proveedor** del directorio (acción hoy realizada fuera del sistema).
- Un **cliente** compra en Mercado Libre; el **sistema** registra el pedido, descuenta inventario en todos los canales, y el **emprendedor** elige la **transportadora** con mejor relación costo/tiempo desde el comparador.
- Un **emprendedor** en el modelo SaaS (Fase 5) reporta que su integración con TikTok Shop dejó de sincronizar; el **equipo de soporte** investiga y, si es un problema de plataforma, lo escala al **administrador de la plataforma**.

---

## Casos límite

- **El equipo fundador actúa hoy como emprendedor, soporte y administrador de la plataforma a la vez:** en el piloto esto es aceptable y esperado; los documentos de negocio igual distinguen los tres roles para que el sistema (y el equipo, cuando crezca) sepa separar esas responsabilidades sin rediseñar nada.
- **Un empleado del emprendedor usa el panel administrativo:** hasta que se defina un sub-rol formal (ver `diccionario-del-negocio.md` → Decisiones futuras), se documenta como el mismo actor "emprendedor", con la limitación de permisos como detalle de implementación futuro.
- **Una transportadora falla en actualizar el estado de un pedido:** el actor "transportadora" no se considera responsable dentro del sistema; el módulo de logística debe manejarlo como caso límite técnico (reintento, alerta al emprendedor), no como una acción faltante del actor.

---

## Decisiones futuras

- Definir sub-roles dentro de "emprendedor" (dueño vs. colaborador con permisos limitados) cuando el panel administrativo soporte más de un usuario por tenant.
- Definir si proveedores y transportadoras eventualmente tienen un portal propio con acceso directo al sistema (mencionado como aspiracional en `vision-producto.md`) o si su interacción sigue siendo siempre mediada por el emprendedor.
- Formalizar el rol y proceso del "equipo de soporte" cuando exista más de un tenant real (Fase 5).

---

## Referencias

- [`docs/business/diccionario-del-negocio.md`](diccionario-del-negocio.md) — definiciones de cada actor.
- [`docs/business/vision-producto.md`](vision-producto.md) — usuarios objetivo del piloto y del SaaS.
- `docs/business/casos-de-uso.md` — casos de uso que usan estos actores.

---

## Historial

- **2026-07-27** — Primera versión.
