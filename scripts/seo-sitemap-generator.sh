#!/bin/bash

# SEO and Sitemap Generation Script
set -e

APP_DIR="/home/deploy/projects/nextgenbeing"
LOG_FILE="/var/log/nextgenbeing/seo.log"

log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

cd "$APP_DIR"

log "Starting SEO optimization tasks..."

# Generate sitemap
log "Generating sitemap..."
php artisan sitemap:generate

# Generate RSS feed
log "Generating RSS feed..."
php artisan rss:generate

# Update search index
log "Updating search index..."
php artisan scout:import "App\Models\Post"

# Generate structured data
log "Generating structured data..."
php artisan structured-data:generate

# Ping search engines
log "Pinging search engines..."
php artisan seo:ping-search-engines

# Generate social media previews
log "Generating social media previews..."
php artisan social:generate-previews

# Clear and warm cache
log "Optimizing cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize images
log "Optimizing images..."
php artisan media-library:optimize

log "SEO optimization completed"
