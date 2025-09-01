#!/bin/bash

# System Monitoring Script
set -e

LOG_FILE="/var/log/nextgenbeing/system-monitor.log"
ALERT_EMAIL="admin@nextgenbeing.com"
APP_DIR="/home/deploy/projects/nextgenbeing"

log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

send_alert() {
    local subject="$1"
    local message="$2"
    echo "$message" | mail -s "NextGenBeing Alert: $subject" "$ALERT_EMAIL"
}

# Check disk space
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 85 ]; then
    send_alert "High Disk Usage" "Disk usage is at ${DISK_USAGE}%"
    log "ALERT: High disk usage - ${DISK_USAGE}%"
fi

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100.0}')
if [ "$MEMORY_USAGE" -gt 90 ]; then
    send_alert "High Memory Usage" "Memory usage is at ${MEMORY_USAGE}%"
    log "ALERT: High memory usage - ${MEMORY_USAGE}%"
fi

# Check if application is responding
if ! curl -f -s "https://nextgenbeing.com/health" > /dev/null; then
    send_alert "Application Down" "Application health check failed"
    log "ALERT: Application health check failed"
fi

# Check database connection
cd "$APP_DIR"
if ! php artisan db:show > /dev/null 2>&1; then
    send_alert "Database Connection Failed" "Cannot connect to database"
    log "ALERT: Database connection failed"
fi

# Check queue status
FAILED_JOBS=$(php artisan queue:failed --format=json | jq length)
if [ "$FAILED_JOBS" -gt 10 ]; then
    send_alert "High Failed Jobs" "Failed jobs count: $FAILED_JOBS"
    log "ALERT: High failed jobs count - $FAILED_JOBS"
fi

# Check Redis connection
if ! redis-cli ping > /dev/null 2>&1; then
    send_alert "Redis Connection Failed" "Cannot connect to Redis"
    log "ALERT: Redis connection failed"
fi

log "System monitoring completed"
