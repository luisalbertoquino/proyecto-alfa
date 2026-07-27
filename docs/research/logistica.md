# Investigación: Logística — Cotización, Guías y Rastreo (Colombia/LatAm)

## Propósito

Documentar cómo funcionan realmente las integraciones de cotización, generación de guía y rastreo con transportadoras relevantes para Colombia, y qué agregadores multi-transportadora existen, para informar el diseño del "comparador de transportadoras" y la "gestión logística centralizada" descritos en `vision-producto.md`.

---

## Objetivo

Responder, con evidencia y no supuestos:

1. ¿Qué transportadoras dominan el mercado colombiano y cuáles exponen API propia?
2. ¿Qué operaciones cubre típicamente una API de transportadora o agregador (cotización, guía, tracking)?
3. ¿Existen agregadores multi-transportadora (tipo Skydropx, Envia.com, mipaquete.com) que resuelvan varias integraciones a la vez?
4. ¿Qué es realista integrar primero para el negocio piloto, dado el esfuerzo de integración directa vs. agregador?

---

## Alcance

**Incluye:** transportadoras nacionales de Colombia (Servientrega, Coordinadora, Interrapidísimo, Envía), agregadores multi-carrier con presencia en Colombia/LatAm (Skydropx, Envia.com, mipaquete.com), qué exponen sus APIs o webservices (cotización, guía/etiqueta, tracking, recolección/pickup), y una recomendación de qué es más realista integrar primero.

**No incluye:** logística internacional/cross-border compleja, fulfillment/bodegaje (picking-packing), ni el diseño del algoritmo interno de "recomendación de mejor transportadora por costo/tiempo/cobertura" (eso es una regla de negocio a definir en `docs/business/reglas-de-negocio.md`).

---

## Problema que resuelve

`vision-producto.md` compromete un "comparador de transportadoras" y "gestión logística centralizada (despacho y seguimiento)" como objetivos específicos #3 y #4, y pone como ejemplo que "el sistema recomienda la transportadora de mejor relación costo/tiempo para esa zona de cobertura". Nada de eso es posible sin saber qué transportadoras exponen API real en Colombia, con qué facilidad, y si conviene integrarlas una por una o a través de un agregador. Este documento responde eso antes de comprometer trabajo de ingeniería.

---

## Principios

- **Las transportadoras tradicionales colombianas no fueron diseñadas API-first.** Servientrega, la transportadora más usada del país, ofrece un webservice de cotización, pero la evidencia encontrada indica que su integración de cotización es compleja (formatos de ciudad no estandarizados — códigos DANE vs. un catálogo propio interno — y requisitos operativos como apertura de puertos específicos), y que generar guías es más simple que cotizar. Esto invalida el supuesto de que "conectar una transportadora" es trivial.
- **Coordinadora, Interrapidísimo y Envía sí ofrecen módulos de integración para e-commerce**, pero su documentación pública técnica es escasa comparada con estándares modernos (REST bien documentado, sandbox público, SDKs): la evidencia sugiere que la integración se gestiona más por relación comercial directa con la transportadora (solicitar credenciales, tablas de tarifas, IDs de cobertura) que por autoservicio developer-first.
- **Los agregadores multi-transportadora existen precisamente para resolver este problema.** Plataformas como Skydropx (con operación en Colombia desde 2021), Envia.com (multi-país, incluye Colombia, con ambientes sandbox/producción bien documentados) y mipaquete.com (agregador colombiano que conecta Servientrega, Coordinadora, Envía, TCC, Deprisa desde una sola API) existen exactamente para evitar que cada comercio tenga que integrarse transportadora por transportadora.
- **Un agregador cambia el problema de "N integraciones" a "1 integración con N proveedores detrás".** Esto es coherente con la regla ya definida en `vision-producto.md`: "ninguna integración de canal se construye a medida del piloto si eso impide añadir un segundo proveedor del mismo tipo después" — un agregador cumple ese principio de fábrica.
- **Envia.com tiene el perfil más "developer-friendly" encontrado**: JSON consistente, ambientes sandbox (`api-test.envia.com`) y producción (`api.envia.com`) separados, autenticación por bearer token, SDKs/ejemplos en múltiples lenguajes, y cobertura documentada de más de 100 transportadoras en varios países incluyendo Colombia — es el estándar contra el que medir a los demás.

---

## Reglas

- La primera integración logística de Proyecto Alfa debe evaluarse contra un agregador multi-transportadora (Envia.com, Skydropx o mipaquete.com), no contra una transportadora individual, para no repetir el patrón de "integración a medida" que la regla de `vision-producto.md` prohíbe explícitamente.
- Toda integración logística debe separar claramente tres operaciones como contratos independientes: cotización, generación de guía/etiqueta, y consulta de tracking — porque la evidencia muestra que no siempre las tres tienen la misma madurez técnica dentro de un mismo proveedor (ej. Servientrega: guía más simple que cotización).
- El comparador de transportadoras no puede prometer cobertura de las cuatro transportadoras líder de Colombia (Servientrega, Coordinadora, Interrapidísimo, Envía) como integración directa en el MVP piloto; debe documentarse como meta de mediano plazo, alcanzable más rápido vía agregador.
- Antes de firmar con cualquier transportadora o agregador, se debe validar en la práctica (ambiente sandbox si existe) que la cotización devuelta corresponde a zonas de cobertura reales del negocio piloto, dado que la cobertura declarada en marketing no siempre coincide con la cobertura operativa real por ciudad/municipio.

---

## Ejemplos

- **Flujo de cotización vía agregador (Envia.com):** Proyecto Alfa envía origen, destino, dimensiones y peso del paquete a la API de cotización → recibe una lista de tarifas de múltiples transportadoras con precio y tiempo estimado de entrega → el comparador de Proyecto Alfa aplica su propia lógica de recomendación (costo/tiempo/cobertura) sobre esa lista.
- **Generación de guía tras confirmar pedido:** una vez el cliente paga, Proyecto Alfa llama al endpoint de creación de envío del proveedor elegido → recibe un PDF/etiqueta imprimible y un número de guía → ese número se guarda en el pedido para tracking.
- **mipaquete.com como agregador colombiano nativo:** automatiza la generación de guía y orden de despacho apenas ocurre una venta en el e-commerce, conectando detrás Servientrega, Coordinadora, Envía, TCC y Deprisa desde una sola integración — el patrón más cercano al objetivo de "gestión logística centralizada" de `vision-producto.md`.
- **Webservice de cotización de Servientrega:** requiere resolver primero un catálogo de IDs de ciudad (formato DANE u otro propio) obtenido de un archivo Excel de red operativa que la transportadora entrega bajo solicitud — no es un endpoint autocontenible de autoservicio.

---

## Casos límite

- **Servientrega, la transportadora dominante en Colombia, no tiene un flujo de autoservicio developer-first**: requiere solicitar manualmente tablas de ciudades y tarifas, y la evidencia encontrada señala explícitamente que "no ofrece facilidad para realizar cotizaciones" pese a ser la más usada del país — un riesgo real de subestimar el esfuerzo de integración directa.
- **Documentación técnica pública limitada para Coordinadora e Interrapidísimo**: ambas ofrecen "módulos de integración" para e-commerce, pero gran parte de la evidencia disponible proviene de terceros (plataformas tipo DrEnvío, agregadores, foros) más que de portales de developer autoservicio con documentación abierta — cualquier estimación de esfuerzo debe validarse directamente con la transportadora antes de comprometer fechas.
- **Los agregadores no son gratis ni neutrales**: cobran comisión o markup sobre la tarifa de la transportadora, y su cobertura de transportadoras varía por país — se debe verificar el modelo comercial (¿cobro por envío, suscripción, o ambos?) de Envia.com, Skydropx y mipaquete.com antes de decidir cuál usar, dato que no quedó confirmado en esta investigación y debe verificarse directamente con cada proveedor.
- **Diferencias de formato de identificación de ciudad entre proveedores** (código DANE vs. IDs propios) son una fuente real de errores de integración si Proyecto Alfa maneja varias transportadoras/agregadores a la vez sin una tabla de normalización propia de ciudades/cobertura.
- **Requisitos de red/infraestructura no triviales**: al menos un caso documentado (Servientrega) exige abrir un puerto específico (8081) para la integración — una restricción de infraestructura que debe validarse con el equipo de DevOps antes de asumir que la integración es "solo HTTP".

---

## Decisiones futuras

- Elegir el agregador (o combinación de agregador + integración directa) con el que se construye el MVP piloto de logística — no resuelto en esta investigación, requiere cotizar comercialmente con Envia.com, Skydropx y mipaquete.com.
- Definir si el negocio piloto necesita cobertura nacional completa desde el día uno, o si basta con las ciudades donde opera realmente al inicio.
- Diseñar la tabla interna de normalización de ciudades/cobertura que traduzca entre los formatos de distintos proveedores logísticos.
- Definir el modelo de costos que Proyecto Alfa traslada (o no) al emprendedor por el uso del comparador/agregador logístico.

---

## Referencias

- [Servientrega: esto deberías saber antes de integrar](https://blog.saulmoralespa.com/servientrega-esto-deberias-saber-antes-de-integrar/)
- [Documentación oficial del servicio web de cotización — Servientrega](https://mobile.servientrega.com/ApiIngresoCLientes/Help)
- [Integra tu ecommerce con nuestros servicios - Coordinadora](https://coordinadora.com/servicios/integraciones-web-service/)
- [Logística eCommerce — Inter Rapidísimo](https://interrapidisimo.com/logistica-ecommerce/)
- [Envia Shipping API – Overview](https://docs.envia.com/docs/envia-shipping-api-introduction)
- [Envia.com API, Libraries, SDKs and Developer Tools](https://envia.com/en-US/developers)
- [Cómo conectar la API Skydropx](https://help.skydropx.com.co/articulos-cda/como-conectar-la-api-skydropx)
- [Conecta tu ecommerce API | Mi paquete](https://www.mipaquete.com/conecta-tu-tiendavirtual/api-integracion)
- [API mipaquete.com versión 2 — documentación](https://api.documentacion.mipaquete.com/)
- [Los 10 mejores operadores logísticos en Colombia (2026)](https://www.melonn.com/blog/mejores-operadores-logisticos-colombia/)

---

## Historial

- **2026-07-27** — Primera versión, basada en investigación web.
