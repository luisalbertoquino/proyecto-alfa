# Principios de Arquitectura

## Propósito

Mientras `vision-tecnica.md` fija las decisiones grandes (monolito modular, multi-tenant, stateless, colas), este documento fija **cómo se escribe y organiza el código día a día** dentro de esa arquitectura, para que crecer en número de módulos, desarrolladores y páginas no degrade la capacidad de mantener el sistema.

---

## Objetivo

Que cualquier desarrollador — hoy o en cinco años, con el equipo diez veces más grande — pueda entrar a un módulo que no conoce y predecir dónde está cada cosa, sin depender de que alguien se lo explique.

## Alcance

Aplica a todo código de `apps/api`, `apps/web`, `apps/admin` y `packages/`. No cubre decisiones de infraestructura (`infraestructura.md`) ni de base de datos física (`base-de-datos.md`), aunque las referencia.

---

## Problema que resuelve

A medida que un proyecto de comercio electrónico crece, la causa más común de que el desarrollo se vuelva lento no es la falta de funcionalidades — es la acumulación de acoplamiento: un cambio en "envíos" rompe "pedidos", nadie sabe si tocar un archivo es seguro, y cada feature nueva tarda más que la anterior. Estos principios existen para que ese deterioro no ocurra, independientemente de cuántas páginas o módulos se agreguen.

---

## Principios

1. **Organización por dominio, no por tipo técnico.** El código se agrupa por capacidad de negocio (`Catalogo/`, `Pedidos/`, `Inventario/`, `Envios/`, `Proveedores/`, `Publicidad/`, `Analitica/`, `Canales/`, `IA/`), no por capa técnica (`Controllers/`, `Models/` sueltos). Cada módulo trae sus propios modelos, controladores, servicios y tests.
2. **Fronteras explícitas entre módulos.** Un módulo expone lo que otros módulos pueden usar a través de una interfaz de servicio o de eventos; nunca a través de sus modelos internos. Esto es lo que permite que un módulo crezca, se pruebe o se extraiga sin arrastrar al resto.
3. **La lógica de negocio no vive en el controlador.** Los controladores traducen HTTP a llamadas de servicio y de vuelta; las reglas de negocio viven en clases de servicio/acciones testeables sin necesidad de levantar el framework HTTP.
4. **Diseñado para reemplazar, no solo para extender.** Cada integración externa (transportadora, marketplace, proveedor de IA, pasarela de pago) se implementa detrás de una interfaz propia. Añadir una transportadora nueva o cambiar de proveedor de IA no debe tocar el código que las usa.
5. **Convenciones antes que configuración por caso.** Nombrar, estructurar y probar de la misma forma en todos los módulos (ver `docs/standards/naming.md`) para que el costo de entender un módulo nuevo sea siempre bajo.
6. **Toda funcionalidad multi-tenant se prueba con más de un tenant.** Ningún test ni dato de ejemplo asume que solo existe un negocio en el sistema.

---

## Reglas

- Un módulo nuevo se crea siguiendo `templates/nuevo-modulo.md`.
- Ningún `use` o `import` cruza directamente al namespace interno de otro módulo (`Models`, `Repositories`); solo a su capa de `Services` o `Events` expuesta.
- Toda integración externa implementa una interfaz definida en el propio módulo (ej. `TransportadoraInterface`, `MarketplaceInterface`, `ProveedorIAInterface`) antes de escribir el primer adaptador concreto.
- Todo endpoint nuevo se documenta siguiendo `templates/nuevo-endpoint.md` y las reglas de `docs/standards/api.md`.
- Código sin test de al menos el flujo principal no se considera terminado (ver `docs/development/testing.md`).

---

## Ejemplos

- El módulo `Envios` define `TransportadoraInterface` con `cotizar()`, `generarGuia()`, `rastrear()`. Cada transportadora real (Servientrega, Coordinadora, Interrapidísimo, etc.) implementa esa interfaz. El comparador de envíos solo conoce la interfaz, nunca los detalles de cada transportadora.
- El módulo `Pedidos` necesita saber si hay stock antes de confirmar una venta: llama al servicio público de `Inventario` (`InventarioService::verificarDisponibilidad()`), no a su modelo `Stock` directamente.
- Al agregar TikTok Shop como canal, se implementa `MarketplaceInterface` ya usada por Mercado Libre — el módulo `Pedidos` que consume pedidos de canales no cambia una línea.

---

## Casos límite

- **Dos módulos necesitan el mismo dato con frecuencia** (ej. `Pedidos` y `Analitica` sobre el histórico de ventas): se resuelve con una proyección de lectura propia de `Analitica`, no dándole acceso directo a las tablas de `Pedidos`.
- **Una regla de negocio no encaja claramente en un solo módulo** (ej. cálculo de comisión de venta que toca `Pedidos` y `Publicidad`): se documenta la decisión de a qué módulo pertenece como ADR, en vez de duplicarla en ambos.
- **Un módulo crece demasiado y se vuelve difícil de navegar:** se divide en submódulos dentro del mismo namespace de dominio antes de considerar extraerlo como servicio aparte.

---

## Decisiones futuras

- Herramienta de verificación automática de fronteras entre módulos (linter de arquitectura) una vez el número de módulos y desarrolladores lo justifique.
- Catálogo formal de eventos de dominio compartidos entre módulos, cuando el número de integraciones cruzadas crezca lo suficiente para necesitar un registro central.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — decisiones de arquitectura de alto nivel que estos principios desarrollan.
- `docs/architecture/arquitectura-backend.md` — aplicación concreta de estos principios en Laravel.
- `docs/standards/naming.md` — convenciones de nombres derivadas de estos principios.
- `templates/nuevo-modulo.md` — plantilla para crear un módulo respetando estas reglas.

---

## Historial

- **2026-07-27** — Primera versión, derivada de `vision-tecnica.md`.
