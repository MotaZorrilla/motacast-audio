#!/bin/bash
# ==============================================================================
# MotaCastAudio - Restore Tool (Disaster Recovery)
# Usage: ./restore_audiolibros.sh [path_to_dump.sql.gz]
# ==============================================================================

set -eo pipefail

BACKUP_ROOT="/home/motazorrilla/backups/audiolibros"
DB_BACKUP_DIR="${BACKUP_ROOT}/db"
MEDIA_BACKUP_DIR="${BACKUP_ROOT}/media"
APP_DIR="/home/motazorrilla/apps/audiolibros"

DUMP_TARGET="$1"

if [ -z "$DUMP_TARGET" ]; then
    # Pick the latest dump by default
    DUMP_TARGET=$(ls -t "${DB_BACKUP_DIR}"/audiolibros_*.sql.gz 2>/dev/null | head -n 1)
    if [ -z "$DUMP_TARGET" ]; then
        echo "ERROR: No backup dump file found in ${DB_BACKUP_DIR}."
        exit 1
    fi
    echo "No file specified. Using latest backup: ${DUMP_TARGET}"
fi

if [ ! -f "$DUMP_TARGET" ]; then
    echo "ERROR: Backup file does not exist: $DUMP_TARGET"
    exit 1
fi

echo "=== Restoring MariaDB Database ==="
echo "Target file: ${DUMP_TARGET}"
gunzip -c "${DUMP_TARGET}" | docker exec -i audiolibros-db mariadb -u audiolibros -pSecureAudioLab2026! audiolibros
echo "Database restored successfully!"

echo "=== Restoring Media Files ==="
if [ -d "${MEDIA_BACKUP_DIR}" ]; then
    rsync -av "${MEDIA_BACKUP_DIR}/" "${APP_DIR}/storage/app/public/"
    echo "Media files restored to ${APP_DIR}/storage/app/public/."
else
    echo "Notice: Media backup directory not found, skipping media restore."
fi

echo "=== Restarting audiolibros-app container ==="
docker restart audiolibros-app
echo "=== Restore completed! ==="
