# Infraestructura

## Propósito

Explicar cómo se empaqueta y despliega Proyecto Alfa: uso de Docker en todos los entornos, qué entornos existen (local, staging, producción), cómo ocurre un despliegue a alto nivel, qué rol cumple Cloudflare, y qué rol cumple GitHub Actions. El detalle paso a paso del pipeline de CI/CD vive en `docs/development/ci-cd.md`; este documento explica el **porqué** de la topología de infraestructura, no cada comando del pipeline.

---

## Objetivo

Que cualquier entorno — la máquina de un desarrollador, staging o producción — corra exactamente la misma composición de servicios, para que "funciona en mi máquina" deje de ser un riesgo, y que llevar un cambio de código a producción sea un proceso reproducible y auditable, no manual.

---

## Alcance

Cubre: uso de Docker como unidad de empaquetado en todos los entornos, definición de entornos (local/staging/producción) y sus diferencias, flujo de despliegue a alto nivel, rol de Cloudflare, y rol de GitHub Actions como orquestador de CI/CD a alto nivel.

No cubre: el detalle exacto del pipeline (jobs, steps, secretos de GitHub Actions) — eso vive en `docs/development/ci-cd.md`; ni las palancas de escalado en sí (`escalabilidad.md`); ni la postura de seguridad de secretos e infraestructura (`seguridad.md`).

---

## Problema que resuelve

Sin una definición explícita de infraestructura, cada entorno termina configurado a mano, de forma distinta, y las diferencias entre "cómo corre en mi máquina" y "cómo corre en producción" se convierten en la fuente más común de bugs que "no deberían pasar". Igualmente, un despliegue manual sin pipeline reproducible es lento, propenso a error humano y no deja rastro auditable de qué cambió y cuándo. Este documento fija Docker y GitHub Actions como las piezas que eliminan ambos problemas desde el diseño.

---

## Principios

1. **Un solo empaquetado, todos los entornos.** La misma imagen Docker (o su equivalente por entorno con variables de configuración distintas) corre en local, staging y producción — las diferencias entre entornos son de configuración, no de construcción.
2. **La infraestructura es código versionado.** Definiciones de Docker, orquestación y pipeline viven en el repositorio (`infrastructure/`, `docker/`), no como pasos manuales documentados en la cabeza de alguien.
3. **Staging existe para parecerse a producción, no para ser un ambiente aparte con reglas propias.** Un cambio que pasa staging debe comportarse igual en producción.
4. **Cloudflare es la primera línea, no un añadido.** CDN y WAF se consideran parte de la topología base, no una optimización posterior.
5. **Todo despliegue pasa por el pipeline.** Nadie despliega a producción con comandos manuales ejecutados a mano fuera de GitHub Actions, salvo un procedimiento de emergencia explícitamente documentado y auditado.

---

## Reglas

### Docker en todos los entornos

- Cada aplicación desplegable (`apps/api`, `apps/web`, `apps/admin`) tiene su propia imagen Docker, definida en el repositorio, con un `Dockerfile` orientado a producción (build multi-stage: dependencias de build separadas de la imagen final que corre).
- El entorno local se levanta con Docker Compose, replicando los mismos servicios (Laravel, Next.js, PostgreSQL, Redis) que corren en staging y producción, para que el comportamiento observado localmente sea representativo.
- Las diferencias entre entornos se manejan por variables de entorno y configuración (ver `seguridad.md` para manejo de secretos), nunca por una rama de código o un Dockerfile distinto por entorno.

### Entornos

- **Local:** máquina de cada desarrollador, vía Docker Compose. Datos de prueba, servicios externos (transportadoras, marketplaces, proveedor de IA) en modo simulado/mock cuando sea posible para no depender de credenciales reales ni afectar cuentas productivas.
- **Staging:** entorno que replica producción en topología (mismos servicios, misma versión de PostgreSQL/Redis), con datos representativos pero no reales de clientes. Es donde se valida un cambio antes de producción, incluyendo migraciones de base de datos.
- **Producción:** entorno que sirve al negocio piloto (y, a futuro, a los tenants del modelo SaaS). Cambios llegan únicamente a través del pipeline de CI/CD, nunca de forma manual salvo procedimiento de emergencia documentado.

### Flujo de despliegue (alto nivel)

- Un cambio se integra a la rama principal solo tras pasar el pipeline de CI (tests, linting, build de imágenes) — el detalle exacto de jobs vive en `docs/development/ci-cd.md`.
- El despliegue a staging es automático ante cada integración a la rama principal; el despliegue a producción requiere una promoción explícita (ej. tag de versión o aprobación) del mismo artefacto ya validado en staging — no se reconstruye la imagen para producción, se promueve la misma que pasó staging.
- Las migraciones de base de datos siguen las reglas de `base-de-datos.md` (sin downtime) y se ejecutan como parte controlada del despliegue, nunca a mano contra producción.
- Todo despliegue a producción queda registrado (qué versión, cuándo, quién lo promovió) para trazabilidad.

### Rol de Cloudflare

- Cloudflare se sitúa delante de `apps/web` (y del resto de tráfico público) cumpliendo: CDN para contenido estático y páginas SSG/ISR, terminación TLS, WAF y mitigación de tráfico anómalo/DDoS, antes de que ese tráfico llegue a los contenedores de aplicación.
- La configuración de Cloudflare (reglas de cache, reglas de WAF, DNS) se documenta y versiona en la medida en que la herramienta lo permita (Terraform u otro IaC), evitando configuración manual no rastreada cuando el proyecto lo justifique.

### Rol de GitHub Actions

- GitHub Actions orquesta, a alto nivel: validación de cada cambio (tests, linting), construcción de imágenes Docker, y despliegue automatizado a staging/producción según el flujo descrito arriba.
- El pipeline es el único camino oficial hacia producción — cualquier excepción manual es un procedimiento de emergencia explícito, documentado y revisado después del hecho.
- Detalle exacto de jobs, matrices de test, y configuración del pipeline: `docs/development/ci-cd.md`.

---

## Ejemplos

- Un desarrollador levanta `docker compose up` localmente y obtiene Laravel, Next.js (web y admin), PostgreSQL y Redis corriendo con la misma topología que producción, con datos de prueba y transportadoras/marketplaces simulados.
- Un cambio en el módulo `Envios` se integra a la rama principal, el pipeline construye las imágenes, corre los tests del módulo y de integración, despliega automáticamente a staging; tras validación manual, se promueve la misma imagen a producción.
- Una campaña publicitaria genera un pico de tráfico a la ficha de producto: Cloudflare sirve la mayoría de esas requests desde cache CDN sin que lleguen a los contenedores de `apps/web`.

---

## Casos límite

- **Incidente en producción que exige un cambio inmediato fuera del pipeline normal** (hotfix): se define un procedimiento de emergencia explícito (quién puede autorizarlo, cómo se audita después) en vez de dejarlo como excepción tácita sin registro.
- **Staging diverge de producción con el tiempo** (ej. una variable de configuración que solo existe en producción): se considera una falla del proceso a corregir, no un estado aceptable — staging pierde su propósito si deja de predecir el comportamiento en producción.
- **Caída de Cloudflare o de un servicio externo de infraestructura:** el sistema debe degradar (ver `vision-tecnica.md`, casos límite) en vez de quedar completamente inaccesible; esto se planifica junto con `escalabilidad.md`.

---

## Decisiones futuras

- Orquestador de contenedores en producción (Docker Compose simple vs. Kubernetes vs. un servicio gestionado tipo ECS) — hoy no comprometido; se decidirá como ADR cuando el volumen de tráfico y equipo lo justifique.
- Herramienta de Infraestructura como Código (Terraform u otra) para versionar la configuración de Cloudflare y del entorno de producción de forma completa.
- Estrategia de despliegue progresivo (blue-green, canary) una vez el tráfico real lo amerite; hoy el despliegue es directo tras staging.
- Procedimiento formal y documentado de rollback ante un despliegue fallido en producción.

---

## Referencias

- `docs/architecture/vision-tecnica.md` — decisión de stack (Docker, Cloudflare, GitHub Actions) que esta infraestructura implementa.
- `docs/architecture/escalabilidad.md` — cómo escalan los contenedores definidos aquí.
- `docs/architecture/base-de-datos.md` — reglas de migraciones aplicadas durante el despliegue.
- `docs/development/ci-cd.md` — detalle paso a paso del pipeline de GitHub Actions.
- `docs/architecture/seguridad.md` — manejo de secretos en cada entorno.

---

## Historial

- **2026-07-27** — Primera versión.
