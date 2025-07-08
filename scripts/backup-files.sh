#!/bin/bash

# File Backup Script
set -e

APP_DIR="/home/deploy/projects/nextgenbeing"
BACKUP_DIR="/home/deploy/backups/files"
S3_BUCKET="nextgenbeing-backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_FILE="/var/log/nextgenbeing/file-backup.log"

mkdir -p "$BACKUP_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

log "Starting file backup..."

# Backup storage directory
STORAGE_BACKUP="storage_backup_${TIMESTAMP}.tar.gz"
if tar -czf "$BACKUP_DIR/$STORAGE_BACKUP" -C "$APP_DIR" storage --exclude="storage/logs/*" --exclude="storage/framework/cache/*"; then
    log "Storage backup created: $STORAGE_BACKUP"

    # Upload to S3
    if command -v aws &> /dev/null; then
        aws s3 cp "$BACKUP_DIR/$STORAGE_BACKUP" "s3://$S3_BUCKET/files/"
        log "Storage backup uploaded to S3"
    fi
else
    log "Storage backup failed"
    exit 1
fi

# Backup .env and important configs
CONFIG_BACKUP="config_backup_${TIMESTAMP}.tar.gz"
tar -czf "$BACKUP_DIR/$CONFIG_BACKUP" -C "$APP_DIR" .env composer.json package.json

if command -v aws &> /dev/null; then
    aws s3 cp "$BACKUP_DIR/$CONFIG_BACKUP" "s3://$S3_BUCKET/configs/"
    log "Config backup uploaded to S3"
fi

# Clean up old local backups (keep 7 days)
find "$BACKUP_DIR" -name "*_backup_*.tar.gz" -mtime +7 -delete

log "File backup completed"
