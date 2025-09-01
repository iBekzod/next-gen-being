#!/bin/bash

# Database Restore Script
set -e

if [ $# -eq 0 ]; then
    echo "Usage: $0 <backup_file.sql.gz>"
    echo "Available backups:"
    ls -la /home/deploy/backups/database/
    exit 1
fi

BACKUP_FILE="$1"
DB_NAME="nextgenbeing"
DB_USER="nextgen_user"
DB_HOST="localhost"

echo "⚠️  WARNING: This will REPLACE the current database!"
echo "Backup file: $BACKUP_FILE"
echo "Database: $DB_NAME"
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Restore cancelled."
    exit 0
fi

echo "Starting database restore..."

# Drop existing connections
sudo -u postgres psql -c "SELECT pg_terminate_backend(pg_stat_activity.pid) FROM pg_stat_activity WHERE pg_stat_activity.datname = '$DB_NAME' AND pid <> pg_backend_pid();"

# Restore database
if [ "${BACKUP_FILE##*.}" = "gz" ]; then
    gunzip -c "$BACKUP_FILE" | PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME"
else
    PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" < "$BACKUP_FILE"
fi

echo "Database restored successfully!"
echo "Running post-restore commands..."

# Navigate to app directory and run Laravel commands
cd /home/deploy/projects/nextgenbeing
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Restore completed successfully!"
