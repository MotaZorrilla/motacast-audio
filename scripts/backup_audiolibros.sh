#!/bin/bash
# ==============================================================================
# MotaCastAudio - Automated Homelab Local Backup (Idea B)
# Target: MariaDB dumps + Media Storage Sync + 14-day Retention
# Author: MotaZorrilla Homelab Architecture
# ==============================================================================

set -eo pipefail

BACKUP_ROOT="/home/motazorrilla/backups/audiolibros"
DB_BACKUP_DIR="${BACKUP_ROOT}/db"
MEDIA_BACKUP_DIR="${BACKUP_ROOT}/media"
LOG_FILE="${BACKUP_ROOT}/backup.log"
APP_DIR="/home/motazorrilla/apps/audiolibros"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=14

mkdir -p "${DB_BACKUP_DIR}" "${MEDIA_BACKUP_DIR}"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "${LOG_FILE}"
}

log "=== Starting MotaCastAudio Backup ==="

# 1. MariaDB Database Dump
DUMP_FILE="${DB_BACKUP_DIR}/audiolibros_${TIMESTAMP}.sql.gz"
log "Dumping MariaDB database from container 'audiolibros-db'..."

if docker exec audiolibros-db mariadb-dump -u audiolibros -pSecureAudioLab2026! --single-transaction --routines --triggers audiolibros | gzip > "${DUMP_FILE}"; then
    DUMP_SIZE=$(ls -lh "${DUMP_FILE}" | awk '{print $5}')
    log "Database dump created successfully: ${DUMP_FILE} (${DUMP_SIZE})"
else
    log "ERROR: Database dump failed!"
    exit 1
fi

# 2. Media Storage Synchronization (PDFs + Generated Audiobooks)
log "Syncing media files from ${APP_DIR}/storage/app/public/ to ${MEDIA_BACKUP_DIR}/..."
if rsync -av --delete "${APP_DIR}/storage/app/public/" "${MEDIA_BACKUP_DIR}/" >> "${LOG_FILE}" 2>&1; then
    MEDIA_COUNT=$(find "${MEDIA_BACKUP_DIR}" -type f | wc -l)
    MEDIA_SIZE=$(du -sh "${MEDIA_BACKUP_DIR}" | awk '{print $1}')
    log "Media synchronization complete. Total files: ${MEDIA_COUNT}, Total size: ${MEDIA_SIZE}"
else
    log "WARNING: Rsync media sync reported warnings or errors."
fi

# 3. Retention Rotation (Purge dumps older than 14 days)
log "Purging database dumps older than ${RETENTION_DAYS} days..."
DELETED_COUNT=0
while IFS= read -r old_file; do
    if [ -n "$old_file" ]; then
        rm -f "$old_file"
        log "Removed old backup: $old_file"
        DELETED_COUNT=$((DELETED_COUNT + 1))
    fi
done < <(find "${DB_BACKUP_DIR}" -type f -name "audiolibros_*.sql.gz" -mtime +${RETENTION_DAYS})
log "Retention rotation finished. Purged ${DELETED_COUNT} old files."

# 4. Storage Usage Summary
AVAILABLE_DISK=$(df -h / | awk 'NR==2 {print $4}')
log "Homelab Disk Free Space on root (/): ${AVAILABLE_DISK}"
log "=== MotaCastAudio Backup Completed Successfully ==="
