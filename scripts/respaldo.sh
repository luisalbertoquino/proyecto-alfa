#!/usr/bin/env bash
# Respaldo automático de la base de datos y las fotos de producto — droplet de pruebas.
#
# Uso (ya instalado por cron — ver docs/estado-actual.md):
#   bash scripts/respaldo.sh
#
# Guarda los respaldos FUERA del repo (/var/backups/skincare), para que un
# "git pull" nunca los toque ni los borre por accidente. Con rotación
# automática: el disco del droplet ya está ajustado (~86% de uso real, ver
# "Notas técnicas del despliegue" en estado-actual.md), así que dejar crecer
# los respaldos sin límite terminaría llenándolo.
set -euo pipefail

REPO_DIR="/var/www/skincare"
BACKUP_DIR="/var/backups/skincare"
DIAS_RETENCION=7
FECHA=$(date +%Y%m%d_%H%M%S)
LOG="$BACKUP_DIR/respaldo.log"

mkdir -p "$BACKUP_DIR/db" "$BACKUP_DIR/fotos"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG"; }

log "== Iniciando respaldo =="

# Credenciales tomadas del .env real de la app — nunca hardcodeadas aquí,
# así el script sigue funcionando aunque cambie la contraseña de la BD.
cd "$REPO_DIR/apps/api"
set -a
# shellcheck disable=SC1090
source <(grep -E '^DB_(DATABASE|USERNAME|PASSWORD|HOST|PORT)=' .env)
set +a

DUMP="$BACKUP_DIR/db/${DB_DATABASE}_${FECHA}.sql.gz"
export MYSQL_PWD="${DB_PASSWORD:-}"
if mysqldump -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" "$DB_DATABASE" | gzip > "$DUMP"; then
    log "Base de datos respaldada: $DUMP ($(du -h "$DUMP" | cut -f1))"
else
    log "ERROR: falló el mysqldump, se descarta el archivo parcial"
    rm -f "$DUMP"
    unset MYSQL_PWD
    exit 1
fi
unset MYSQL_PWD

FOTOS_DIR="$REPO_DIR/apps/api/storage/app/public/productos"
if [ -d "$FOTOS_DIR" ] && [ -n "$(ls -A "$FOTOS_DIR" 2>/dev/null)" ]; then
    FOTOS_TAR="$BACKUP_DIR/fotos/productos_${FECHA}.tar.gz"
    tar -czf "$FOTOS_TAR" -C "$FOTOS_DIR" .
    log "Fotos de producto respaldadas: $FOTOS_TAR ($(du -h "$FOTOS_TAR" | cut -f1))"
else
    log "Aviso: no hay fotos de producto todavía en $FOTOS_DIR, se omite ese respaldo"
fi

# Rotación: solo se conservan los últimos $DIAS_RETENCION días de respaldos.
find "$BACKUP_DIR/db" -name '*.sql.gz' -mtime "+$DIAS_RETENCION" -delete
find "$BACKUP_DIR/fotos" -name '*.tar.gz' -mtime "+$DIAS_RETENCION" -delete

log "== Respaldo terminado, retención de $DIAS_RETENCION días aplicada =="
