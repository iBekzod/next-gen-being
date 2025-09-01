#!/bin/bash

# Database Backup Script for PostgreSQL

set -e

# Configuration
DB_NAME="nextgenbeing"
DB_USER="nextgen_user"
DB_HOST="localhost"
DB_PORT="5432"
BACKUP_DIR="/home/deploy/backups/database"
S3_BUCKET="nextgenbeing-backups"
RETENTION_DAYS=30
LOG_FILE="/var/log/nextgenbeing/backup.log"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# Function to log messages
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Function to send notification
send_notification() {
    local status="$1"
    local message="$2"

    # Send to Slack webhook (optional)
    if [ -n "$SLACK_WEBHOOK_URL" ]; then
        curl -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"🗄️ Database Backup [$status]: $message\"}" \
            "$SLACK_WEBHOOK_URL"
    fi

    # Send email notification
    echo "$message" | mail -s "NextGenBeing Database Backup [$status]" admin@nextgenbeing.com
}

# Create timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="nextgenbeing_backup_${TIMESTAMP}.sql.gz"
BACKUP_PATH="$BACKUP_DIR/$BACKUP_FILE"

log "Starting database backup..."

# Create PostgreSQL backup
if PGPASSWORD="$DB_PASSWORD" pg_dump \
    -h "$DB_HOST" \
    -p "$DB_PORT" \
    -U "$DB_USER" \
    -d "$DB_NAME" \
    --verbose \
    --clean \
    --no-owner \
    --no-privileges \
    | gzip > "$BACKUP_PATH"; then

    log "Database backup created successfully: $BACKUP_FILE"

    # Get backup file size
    BACKUP_SIZE=$(du -h "$BACKUP_PATH" | cut -f1)
    log "Backup size: $BACKUP_SIZE"

    # Upload to S3
    if command -v aws &> /dev/null; then
        if aws s3 cp "$BACKUP_PATH" "s3://$S3_BUCKET/database/" --storage-class STANDARD_IA; then
            log "Backup uploaded to S3 successfully"
        else
            log "Failed to upload backup to S3"
            send_notification "WARNING" "Backup created but S3 upload failed"
        fi
    else
        log "AWS CLI not found, skipping S3 upload"
    fi

    # Clean up old local backups
    find "$BACKUP_DIR" -name "nextgenbeing_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete
    log "Cleaned up backups older than $RETENTION_DAYS days"

    # Clean up old S3 backups
    if command -v aws &> /dev/null; then
        aws s3 ls "s3://$S3_BUCKET/database/" | \
        awk '$1 < "'$(date -d "$RETENTION_DAYS days ago" '+%Y-%m-%d')'" {print $4}' | \
        while read -r file; do
            if [ -n "$file" ]; then
                aws s3 rm "s3://$S3_BUCKET/database/$file"
                log "Deleted old S3 backup: $file"
            fi
        done
    fi

    send_notification "SUCCESS" "Database backup completed successfully. Size: $BACKUP_SIZE"

else
    log "Database backup failed!"
    send_notification "ERROR" "Database backup failed! Check logs immediately."
    exit 1
fi

log "Backup process completed"
