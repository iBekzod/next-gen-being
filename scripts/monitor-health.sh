#!/bin/bash
set -e

LOG_FILE="/tmp/nextgenbeing-health.log"
ALERT_EMAIL="admin@nextgenbeing.com"

log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

send_alert() {
    echo "$2" | mail -s "NextGenBeing Alert: $1" "$ALERT_EMAIL" 2>/dev/null || true
}

# Check web server
if ! curl -f -s "http://localhost/health" > /dev/null; then
    send_alert "Website Down" "NextGenBeing health check failed"
    log "ALERT: Website health check failed"
fi

# Check containers
CONTAINERS=("nextgenbeing-app-prod" "nextgenbeing-web-prod" "nextgenbeing-db-prod" "nextgenbeing-redis-prod")
for container in "${CONTAINERS[@]}"; do
    if ! docker ps --format "table {{.Names}}" | grep -q "$container"; then
        send_alert "Container Down" "$container is not running"
        log "ALERT: $container is not running"
    fi
done

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

log "Health monitoring completed"
