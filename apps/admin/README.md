# apps/admin — Panel administrativo

Next.js 16 (App Router, 100% Client Components — ver por qué en `docs/architecture/arquitectura-frontend.md`). Panel del emprendedor: login (Sanctum), CRUD de productos (con galería e imágenes reales), gestión de pedidos (confirmar/cancelar, con baja de stock real), rutinas sugeridas, y configuración del contenido institucional de la tienda.

Consume la API (`apps/api`) vía `NEXT_PUBLIC_API_URL`, con el token de sesión en `localStorage` — por eso todo es Client Component: un Server Component no tiene acceso a `localStorage`.

**Antes de escribir código Next.js nuevo acá**, lee `AGENTS.md` en esta misma carpeta: esta versión de Next.js tiene cambios que rompen con versiones anteriores.

## Correr en desarrollo

```bash
npm run dev -- --port 3001
```

Login de prueba: `admin@skincarepiloto.test` / `password`. Requiere `apps/api` corriendo (ver `docs/estado-actual.md`, "Cómo levantar el entorno en una sesión nueva").

## Despliegue

No es Vercel — corre nativo con PM2 sobre el droplet de pruebas (OpenLiteSpeed como reverse proxy), igual que el resto del stack. Ver `docs/adr/ADR-002.md` y `scripts/deploy.sh`.
