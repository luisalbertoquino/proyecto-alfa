# apps/web — Tienda pública

Next.js 16 (App Router). Es la tienda del negocio piloto: catálogo, filtro por necesidad de piel, carrito (`localStorage`), checkout de invitado y páginas institucionales (Quiénes somos, Contáctanos, Rutinas sugeridas). Sin login — el login es de `apps/admin`.

Consume la API (`apps/api`) vía `NEXT_PUBLIC_API_URL` (ver `.env.example`). Las páginas que muestran catálogo/stock usan `export const dynamic = "force-dynamic"` a propósito, para no congelar precios/stock en el build — ver `docs/estado-actual.md`.

**Antes de escribir código Next.js nuevo acá**, lee `AGENTS.md` en esta misma carpeta: esta versión de Next.js tiene cambios que rompen con versiones anteriores.

## Correr en desarrollo

```bash
npm run dev -- --port 3000
```

Requiere `apps/api` corriendo (ver `docs/estado-actual.md`, "Cómo levantar el entorno en una sesión nueva").

## Despliegue

No es Vercel — corre nativo con PM2 sobre el droplet de pruebas (OpenLiteSpeed como reverse proxy), igual que el resto del stack. Ver `docs/adr/ADR-002.md` y `scripts/deploy.sh`.
