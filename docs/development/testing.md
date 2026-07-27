# Testing

## Propósito

Definir la estrategia de pruebas de Proyecto Alfa: qué se prueba, en qué nivel (unitario, integración, end-to-end), con qué cobertura para cada tipo de cambio, y qué flujos son lo bastante críticos como para exigir más que el mínimo. Fija también, de forma explícita y no negociable, la regla de probar siempre con más de un tenant.

---

## Objetivo

Que un cambio que pasa la suite de tests dé confianza real de que no rompe nada — en particular, que no filtra datos entre tenants ni rompe un flujo crítico del negocio (checkout, sincronización de inventario, pagos) — sin que la suite se vuelva tan lenta o tan frágil que el equipo empiece a ignorarla.

---

## Alcance

Cubre: la pirámide de pruebas por nivel, dónde vive cada tipo de test dentro del monorepo, la regla multi-tenant, y la clasificación de flujos críticos con su exigencia de cobertura.

No cubre: qué corre en cada PR ni en qué orden (`docs/development/ci-cd.md`), ni el checklist de revisión de código (`docs/development/coding-standards.md`) — aunque ambos dependen de lo que este documento define como "probado correctamente".

---

## Problema que resuelve

Un sistema multi-tenant donde el piloto opera hoy como tenant único tiene un riesgo particular: es fácil escribir código y tests que funcionan perfecto para un solo tenant y fallan — filtrando datos de un negocio a otro, o rompiendo una consulta — en cuanto existe el segundo. Ese bug no se ve en desarrollo ni en el piloto; se ve el día que se vende la plataforma a otro emprendedor, que es exactamente el peor momento para descubrirlo.

Además, sin una pirámide de pruebas explícita, los equipos tienden a uno de dos extremos: todo end-to-end (suite lentísima, se ejecuta poco, feedback tardío) o todo unitario sin nada que verifique que los módulos realmente funcionan juntos (falsa sensación de seguridad).

---

## Principios

1. **Pirámide de pruebas, no un solo nivel.** Muchos tests unitarios rápidos en la base, menos tests de integración entre módulos en el medio, y pocos tests end-to-end de los flujos que de verdad importan en la cima. Cada nivel prueba lo que le corresponde; no se duplica el mismo caso en los tres niveles.
2. **Todo test multi-tenant prueba con más de un tenant, siempre.** Ningún test ni fixture asume que solo existe un negocio en el sistema (regla heredada directamente de `principios-de-arquitectura.md` y `vision-tecnica.md`). Un test que crea datos de un solo tenant y verifica que "aparecen" no prueba aislamiento; prueba solo que el dato existe.
3. **Los flujos críticos del negocio exigen más cobertura que el resto**, no porque sean más difíciles de programar, sino porque su falla cuesta más: dinero perdido (pagos, checkout), inventario mal sincronizado (sobreventa), o pedidos que no llegan (envíos).
4. **Un test que nunca fallado no es garantía de nada; un test debe poder fallar.** Todo test nuevo se corre primero contra el código sin el fix/feature (o se verifica deliberadamente que falla) para confirmar que realmente prueba lo que dice probar, antes de confiar en él en verde.
5. **El nivel del test lo decide qué se necesita verificar, no la comodidad de escribirlo.** Verificar una regla de negocio pura (ej. cálculo de comisión) no requiere levantar HTTP ni base de datos; verificar que dos módulos se comunican correctamente sí requiere integración.

---

## Reglas

### Pirámide de pruebas

- **Unitarios (base de la pirámide, la mayoría de los tests):**
  - Viven dentro de cada módulo de dominio (`apps/api/.../Catalogo/Tests`, `.../Pedidos/Tests`, etc., siguiendo la organización por dominio de `principios-de-arquitectura.md`).
  - Prueban clases de servicio/acciones en aislamiento, sin HTTP, sin base de datos real cuando es posible (o con base de datos en memoria/transacción revertida).
  - Rápidos (la suite completa de unitarios corre en segundos, no minutos) — son los que corren primero en CI y los que un desarrollador corre en su máquina antes de hacer push.
- **Integración (nivel medio):**
  - Prueban que dos o más módulos se comunican correctamente a través de sus interfaces de servicio o eventos (ej. `Pedidos` llamando a `InventarioService::verificarDisponibilidad()`), y que un endpoint completo de la API responde correctamente pasando por controlador, servicio y base de datos real (de test).
  - Viven junto al módulo que "posee" el flujo, o en una carpeta de integración compartida cuando el flujo cruza más de dos módulos.
  - Incluyen siempre el caso multi-tenant cuando el flujo toca datos de negocio (ver regla dedicada abajo).
- **End-to-end (cima, los menos, los más caros):**
  - Simulan al usuario real de principio a fin a través de `apps/web` o `apps/admin` contra una API real (entorno de test/staging), sin mockear nada del propio sistema.
  - Reservados para los flujos críticos listados abajo — no se escribe un E2E por cada pantalla o funcionalidad menor.
  - Corren en CI en el pipeline de PR o de merge a `main` según su duración (ver `docs/development/ci-cd.md`), pero siempre antes de un despliegue a producción.

### Regla multi-tenant (no negociable)

- Todo test de integración o E2E que toque una tabla o consulta con `tenant_id` crea **al menos dos tenants** en su setup, con datos que se parecen lo suficiente entre sí como para que un bug de aislamiento (ej. un `where` sin filtrar por tenant) produzca un falso positivo si no se prueba explícitamente.
- El caso a verificar no es solo "el tenant A ve sus datos", es explícitamente "el tenant A **no** ve los datos del tenant B" — la aserción negativa es la que importa y no puede faltar.
- Ningún seeder, factory o fixture de test genera datos de un solo tenant por defecto; el helper de test estándar del proyecto crea multi-tenant desde el nombre (ej. `TenantFactory` produce al menos dos tenants a menos que el test declare explícitamente que el caso de un solo tenant es lo que se está probando, por ejemplo, "qué pasa cuando aún no hay segundo tenant").
- Un PR que agrega o modifica una tabla o consulta de negocio sin este caso de prueba no se considera terminado (ver `docs/development/coding-standards.md`).

### Flujos críticos y su exigencia de cobertura

Se consideran **críticos** — con exigencia de test de integración obligatorio y test E2E cubriendo el camino feliz y al menos un camino de falla — los siguientes flujos:

- **Checkout / creación de pedido:** desde selección de producto hasta confirmación de la orden, incluida validación de stock disponible.
- **Pagos:** integración con pasarela de pago, confirmación y reconciliación de estado de pago con el estado del pedido.
- **Sincronización de inventario (multicanal):** que un cambio de stock se propague correctamente entre canales (`Canales`) y nunca deje vender más unidades de las disponibles (sobreventa) — probado explícitamente con más de un tenant y más de un canal.
- **Autenticación y aislamiento multi-tenant:** que un usuario de un tenant nunca pueda leer o modificar datos de otro tenant por ningún endpoint, no solo por los "obvios".

Para estos flujos, un PR que los modifica no puede reducir la cobertura E2E existente; si el flujo cambia de comportamiento, el test E2E se actualiza en el mismo PR, no en uno posterior.

Para el resto de flujos (no listados como críticos), el mínimo exigido es el test unitario del flujo principal (regla general de `principios-de-arquitectura.md`) y, si el flujo cruza módulos, un test de integración.

---

## Ejemplos

- Módulo `Inventario`: un test unitario verifica que `InventarioService::verificarDisponibilidad()` devuelve `false` cuando el stock reservado supera el disponible, sin tocar base de datos. Un test de integración crea dos tenants con el mismo SKU y confirma que reservar stock del tenant A no reduce el stock del tenant B. Un test E2E de checkout confirma que, con stock en 1 unidad, dos compras simultáneas del mismo tenant no ambas tienen éxito (no hay sobreventa).
- Módulo `Canales`: al agregar TikTok Shop, el test de integración verifica que el evento `StockActualizado` de un tenant dispara la propagación solo a los canales configurados por ese tenant, no a los de otro tenant que también tiene TikTok Shop conectado.
- Un cambio "menor" en el cálculo de costo de envío (`Envios`) no es un flujo crítico en sí mismo, pero si se invoca desde el checkout, su test de integración se ejecuta como parte de la suite de checkout para confirmar que el flujo completo sigue funcionando.

---

## Casos límite

- **Un flujo crítico es difícil o lento de probar end-to-end** (ej. depende de una pasarela de pago real): se usa un doble de prueba (sandbox oficial de la pasarela, o un adaptador falso detrás de la misma interfaz que usa producción — ver `principios-de-arquitectura.md`, principio de "diseñado para reemplazar") en vez de omitir el test.
- **El piloto sigue operando como tenant único en producción:** eso no exime a los tests de crear múltiples tenants; el entorno de test siempre simula el escenario multi-tenant aunque producción hoy tenga uno solo, precisamente para no descubrir el problema el día que llegue el segundo tenant real.
- **Un test E2E falla de forma intermitente (flaky):** no se ignora ni se reintenta silenciosamente hasta que pase; se marca explícitamente como conocido-inestable con una tarea para arreglarlo, y no bloquea otros PRs mientras se investiga, pero tampoco se borra sin entender la causa.
- **Un cambio solo de infraestructura o configuración (sin lógica)** no requiere nuevo test funcional, pero si toca algo que un test E2E ya cubre (ej. variables de entorno de la pasarela de pago), ese E2E se corre igual antes de mergear.

---

## Decisiones futuras

- Definir umbral de tiempo máximo aceptable para la suite completa de E2E antes de que se requiera paralelización o selección de subconjunto por cambio.
- Evaluar herramienta de *contract testing* entre `apps/api` y `apps/web`/`apps/admin` cuando el número de endpoints y de clientes crezca, para detectar rupturas de contrato sin depender solo de E2E.
- Definir el catálogo formal de "flujos críticos" como documento vivo separado si la lista crece más allá de lo que cabe cómodamente en este archivo (candidatos: proceso de reembolso, cambio de transportadora en un pedido ya despachado).

---

## Referencias

- `docs/architecture/principios-de-arquitectura.md` — regla base de multi-tenant y de "código sin test no está terminado".
- `docs/architecture/vision-tecnica.md` — multi-tenant desde el modelo de datos, trabajo asíncrono por cola.
- `docs/development/coding-standards.md` — cómo la falta de test bloquea un PR.
- `docs/development/ci-cd.md` — en qué momento del pipeline corre cada nivel de test.

---

## Historial

- **2026-07-27** — Primera versión.
