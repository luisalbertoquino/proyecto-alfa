#!/usr/bin/env bash
# Script de despliegue para el droplet de pruebas — ver docs/estado-actual.md.
#
# Uso: conectarse por SSH al droplet y correr:
#   cd /var/www/skincare && bash scripts/deploy.sh
#
# Es manual a propósito (no hay webhook todavía): se corre después de cada
# `git push` a main cuando se quiere que el servidor de pruebas refleje los
# cambios. No corre el seeder (eso borraría/duplicaría datos reales del panel).
set -euo pipefail

REPO_DIR="/var/www/skincare"
PM2_WEB="skincare-web"
PM2_ADMIN="skincare-admin"

echo "== 1/5: git pull =="
cd "$REPO_DIR"
git pull

echo "== 2/5: backend (apps/api) =="
cd "$REPO_DIR/apps/api"
composer install --no-dev --optimize-autoloader
php artisan migrate --force
# Laravel corre como www-data via LiteSpeed; storage/ y bootstrap/cache/
# necesitan ser escribibles para ese usuario (ver estado-actual.md).
chown -R root:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan config:clear
php artisan config:cache

echo "== 3/5: tienda (apps/web) =="
cd "$REPO_DIR/apps/web"
npm install
NODE_OPTIONS="--max-old-space-size=512" npm run build
pm2 restart "$PM2_WEB"

echo "== 4/5: panel (apps/admin) =="
cd "$REPO_DIR/apps/admin"
npm install
NODE_OPTIONS="--max-old-space-size=512" npm run build
pm2 restart "$PM2_ADMIN"

echo "== 5/5: guardar estado de PM2 =="
pm2 save

echo ""
echo "Listo. Verifica:"
echo "  https://skincare-api.alegrarte.store/up"
echo "  https://skincare.alegrarte.store/"
echo "  https://skincare-admin.alegrarte.store/"
