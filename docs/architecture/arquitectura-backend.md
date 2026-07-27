# Arquitectura de Backend

## Propósito

Describir cómo se organiza en la práctica el backend Laravel de Proyecto Alfa: la estructura de carpetas por módulo de dominio, las capas dentro de cada módulo y cómo se comunican los módulos entre sí. Este documento es la aplicación concreta, en código Laravel real, de las decisiones fijadas en `vision-tecnica.md` (monolito modular) y de las reglas de `principios-de-arquitectura.md` (organización por dominio, fronteras explícitas).

---

## Objetivo

Que un desarrollador que abre `apps/api` por primera vez sepa, sin preguntar, dónde vive la lógica de una capacidad de negocio, qué archivo tocar para agregar un endpoint, y qué frontera no debe cruzar al llamar a otro módulo.

---

## Alcance

Cubre: estructura de carpetas de `apps/api`, capas dentro de un módulo (Controllers, Requests, Services, Repositories, Events, Listeners, Jobs, Policies), convención de comunicación entre módulos, y dónde vive el código transversal (autenticación, resolución de tenant, manejo de errores).

No cubre: contrato HTTP expuesto al exterior (`apis.md`), esquema de base de datos (`base-de-datos.md`), ni el detalle de cada módulo funcional de negocio (`arquitectura-funcional.md`).

---

## Problema que resuelve

Un backend Laravel típico, sin estructura de módulos, termina con toda la lógica de negocio repartida entre controladores gigantes y modelos Eloquent que hacen de todo ("modelos gordos"), donde nada indica qué parte del sistema pertenece a "pedidos" y qué parte a "envíos". El resultado: cualquier cambio requiere leer todo el proyecto para saber qué se rompe. Este documento fija una estructura donde el nombre de la carpeta ya responde "¿esto es de qué módulo?", y las capas dentro del módulo responden "¿esto es una regla de negocio o un detalle de transporte HTTP?".

---

## Principios

1. **Un módulo, una carpeta, un namespace.** `Catalogo`, `Pedidos`, `Inventario`, `Envios`, `Proveedores`, `Publicidad`, `Analitica`, `Canales`, `IA` viven como unidades autocontenidas, no repartidas entre `app/Http`, `app/Models`, `app/Services` genéricos.
2. **El controlador no piensa, traduce.** Recibe HTTP, valida forma con un Request, delega la decisión de negocio a un Service, traduce el resultado de vuelta a HTTP.
3. **Los Repositories aíslan Eloquent del resto del módulo.** Un Service no arma queries directamente contra el ORM salvo casos triviales; consulta a través de un Repository, lo que permite cambiar detalles de persistencia sin tocar reglas de negocio.
4. **Los efectos secundarios entre módulos viajan por eventos, no por llamadas directas encadenadas.** Cuando una acción en un módulo debe desencadenar trabajo en otro, se publica un evento; el módulo interesado escucha, no se le llama explícitamente desde el origen.
5. **Lo que tarda o depende de fuera va a un Job en cola**, nunca a un método invocado en el ciclo de request (ver `vision-tecnica.md`, principio 5).
6. **Un módulo expone una API de servicio pública y esconde el resto.** Solo las clases en `Services/` (o equivalentes explícitamente públicos) son invocables desde otro módulo.

---

## Reglas

### Estructura de carpetas

```
apps/api/
├── app/
│   ├── Modules/
│   │   ├── Catalogo/
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   └── Requests/
│   │   │   ├── Models/
│   │   │   ├── Repositories/
│   │   │   ├── Services/
│   │   │   ├── Events/
│   │   │   ├── Listeners/
│   │   │   ├── Jobs/
│   │   │   ├── Policies/
│   │   │   └── Tests/
│   │   ├── Pedidos/
│   │   ├── Inventario/
│   │   ├── Envios/
│   │   ├── Proveedores/
│   │   ├── Publicidad/
│   │   ├── Analitica/
│   │   ├── Canales/
│   │   └── IA/
│   └── Shared/            # Middleware de resolución de tenant, manejo de errores, contratos comunes
├── routes/
│   └── api_v1.php         # agrupa las rutas de cada módulo bajo /api/v1
├── config/
├── database/
│   └── migrations/        # ver base-de-datos.md para convenciones
└── tests/
```

- Cada módulo trae sus propios tests junto al código que prueba (`Modules/Pedidos/Tests/`), no en un árbol de tests separado por tipo.
- `Shared/` solo contiene lo verdaderamente transversal (middleware de tenant, manejo de excepciones, contratos base); si algo empieza a acumular lógica de negocio, es una señal de que pertenece a un módulo.

### Capas dentro de un módulo

- **Controllers** — traducen HTTP ↔ llamada de servicio. No contienen `if` de reglas de negocio.
- **Requests** (Form Requests de Laravel) — validan forma y autorización superficial de la entrada (tipos, campos requeridos), no reglas de negocio profundas (esas viven en el Service).
- **Services** — la lógica de negocio real, testeable sin levantar HTTP. Es la única capa que otros módulos pueden invocar.
- **Repositories** — encapsulan las queries Eloquent/Query Builder de ese módulo. Un Service pide datos al Repository, no arma la query él mismo.
- **Events** — hechos de dominio ya ocurridos (`PedidoConfirmado`, `StockActualizado`). Se nombran en pasado.
- **Listeners** — reaccionan a eventos, propios o de otro módulo; los que hacen trabajo no trivial se despachan como Job en cola.
- **Jobs** — trabajo asíncrono ejecutado por Horizon (sincronización de canal, generación con IA, cálculo de reporte pesado).
- **Policies** — autorización basada en rol y en tenant (ver `seguridad.md`) para cada acción sobre un recurso del módulo.

### Comunicación entre módulos

- Un módulo llama a otro exclusivamente a través de su clase de `Services/` pública, nunca a través de un `Model` o `Repository` interno de otro módulo (regla ya fijada en `principios-de-arquitectura.md`).
- Cuando la relación es "que pase algo en A debe disparar trabajo en B, pero A no necesita el resultado", se usa un evento (`Events/` + `Listeners/`) en vez de una llamada directa a servicio — esto es lo que permite que `Canales` reaccione a `StockActualizado` sin que `Inventario` sepa que `Canales` existe.
- Cuando la relación es "A necesita una respuesta de B para decidir algo ahora" (ej. `Pedidos` necesita saber si hay stock antes de confirmar), se usa una llamada directa a un método del Service público de B.
- Ningún módulo escribe directamente en las tablas de otro módulo, ni siquiera a través de Eloquent — todo dato que cruza la frontera lo hace a través del Service o del evento, nunca por acceso compartido a un mismo modelo.

---

## Ejemplos

- **Confirmar un pedido con validación de stock:** `Modules/Pedidos/Services/ConfirmarPedidoService` llama a `Modules/Inventario/Services/InventarioService::verificarDisponibilidad()`. Si hay stock, confirma el pedido y dispara el evento `PedidoConfirmado`; `Modules/Inventario` lo escucha para descontar stock, y `Modules/Canales` lo escucha para propagar el cambio a los marketplaces conectados — todo desde un Job en cola.
- **Nueva transportadora:** `Modules/Envios/Services/CotizadorEnvioService` depende de `TransportadoraInterface`; agregar Coordinadora o Interrapidísimo como transportadora nueva es implementar esa interfaz dentro de `Modules/Envios/Services/Transportadoras/`, sin tocar el controlador ni el módulo `Pedidos` que consume la cotización.
- **Reporte pesado de Analítica:** el controlador de `Modules/Analitica` no calcula el reporte en el request; despacha `Modules/Analitica/Jobs/CalcularReporteVentasJob`, responde `202 Accepted` con un identificador de tarea, y el cliente consulta el resultado cuando está listo.

---

## Casos límite

- **Un módulo necesita un dato de otro con mucha frecuencia y de forma síncrona** (ej. `Analitica` sobre histórico de `Pedidos`): se resuelve con una proyección de lectura propia de `Analitica` poblada por eventos, no con llamadas repetidas al Service de `Pedidos` en cada request (ver `principios-de-arquitectura.md`, casos límite).
- **Un Job falla a mitad de camino** (ej. se cae la API de un marketplace mientras `Canales` propaga un cambio de stock): el Job reintenta con backoff y, tras agotar reintentos, se marca como fallido y visible en Horizon para intervención manual — nunca falla en silencio.
- **Dos módulos parecen necesitar la misma entidad** (ej. "producto" en `Catalogo` y en `Publicidad` para armar un anuncio): cada módulo mantiene su propia vista de lo que necesita de esa entidad (aunque apunte al mismo `producto_id`), no se comparte el modelo Eloquent entre módulos.

---

## Decisiones futuras

- Si se introduce un paquete de linting de arquitectura (ej. Deptrac) para hacer cumplir automáticamente las fronteras entre módulos en CI, en vez de depender solo de revisión de código.
- Convención formal para versionar contratos de eventos internos cuando su forma cambie y varios módulos ya los consuman.
- Punto en el que un módulo se divide en submódulos (ej. `Canales/TikTokShop`, `Canales/MercadoLibre`) antes de considerar extracción a servicio independiente.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — decisión de monolito modular que esta estructura implementa.
- `docs/architecture/principios-de-arquitectura.md` — principios de diseño de código que rigen estas capas.
- `docs/architecture/arquitectura-funcional.md` — qué hace cada módulo y cómo se relacionan a nivel de negocio.
- `docs/architecture/apis.md` — contrato HTTP que exponen los Controllers.
- `docs/standards/naming.md` — convención de nombres de clases, eventos y jobs (en construcción).

---

## Historial

- **2026-07-27** — Primera versión.
