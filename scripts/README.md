# scripts/

Automatizaciones que corren en el droplet de pruebas, fuera del ciclo de vida normal de `apps/api` (no son parte de la app Laravel, son operación del servidor). Ambos son bash, pensados para correr por SSH directamente en Linux — no en Windows.

- **`deploy.sh`** — despliegue manual de una actualización: `git pull`, reinstala dependencias, migra, reconstruye `apps/web`/`apps/admin`, reinicia PM2. Se corre a mano después de cada `git push` que se quiera reflejar en el droplet (no hay webhook todavía). Detalle completo en `docs/estado-actual.md`, sección "Cómo desplegar cambios nuevos al droplet".
- **`respaldo.sh`** — respaldo automático diario (por cron) de la base de datos y las fotos de producto, con rotación de 7 días. Detalle en `docs/estado-actual.md`, sección "Respaldos automáticos".

Cualquier script nuevo de infraestructura/operación (no de desarrollo local) va aquí, no dentro de `apps/api`.
