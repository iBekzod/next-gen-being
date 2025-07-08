#!/bin/bash
set -e

# NextGenBeing Production Deployment Script
# Supports both Docker and native PHP deployments

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/home/deploy/projects/nextgenbeing"
BACKUP_DIR="/home/deploy/backups"
ENV_FILE=".env.production"
DEPLOYMENT_TYPE="${1:-docker}" # docker or native

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Pre-deployment checks
pre_deployment_checks() {
    log_info "Running pre-deployment checks..."

    # Check if running as deploy user
    if [ "$USER" != "deploy" ]; then
        log_error "This script must be run as the deploy user"
        exit 1
    fi

    # Check if project directory exists
    if [ ! -d "$PROJECT_DIR" ]; then
        log_error "Project directory not found: $PROJECT_DIR"
        exit 1
    fi

    # Check if production env file exists
    if [ ! -f "$PROJECT_DIR/$ENV_FILE" ]; then
        log_error "Production environment file not found: $ENV_FILE"
        exit 1
    fi

    # Check deployment type
    if [ "$DEPLOYMENT_TYPE" != "docker" ] && [ "$DEPLOYMENT_TYPE" != "native" ]; then
        log_error "Invalid deployment type. Use 'docker' or 'native'"
        exit 1
    fi

    log_info "Pre-deployment checks passed"
}

# Create backup
create_backup() {
    log_info "Creating backup..."

    mkdir -p "$BACKUP_DIR"
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_FILE="$BACKUP_DIR/backup_${TIMESTAMP}.tar.gz"

    cd "$PROJECT_DIR"
    tar -czf "$BACKUP_FILE" \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        --exclude='.git' \
        .

    log_info "Backup created: $BACKUP_FILE"

    # Keep only last 10 backups
    cd "$BACKUP_DIR"
    ls -t backup_*.tar.gz | tail -n +11 | xargs -r rm
}

# Pull latest code
pull_latest_code() {
    log_info "Pulling latest code from repository..."

    cd "$PROJECT_DIR"
    git fetch origin main
    git reset --hard origin/main

    log_info "Code updated to latest version"
}

# Docker deployment
deploy_docker() {
    log_info "Starting Docker deployment..."

    cd "$PROJECT_DIR"

    # Copy production env
    cp "$ENV_FILE" .env

    # Build and start containers
    log_info "Building Docker images..."
    docker-compose -f docker-compose.prod.yml build --no-cache

    log_info "Starting containers..."
    docker-compose -f docker-compose.prod.yml down
    docker-compose -f docker-compose.prod.yml up -d

    # Wait for containers to be ready
    log_info "Waiting for containers to be ready..."
    sleep 15

    # Run Laravel commands
    log_info "Running Laravel setup commands..."
    docker-compose -f docker-compose.prod.yml exec -T ngb-app composer install --no-dev --optimize-autoloader
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan migrate --force
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan db:seed --force --class=ProductionSeeder || true
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan storage:link || true

    # Clear and optimize
    log_info "Optimizing application..."
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan optimize:clear
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan config:cache
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan route:cache
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan view:cache
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan event:cache
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan icons:cache || true

    # Queue restart
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan queue:restart

    # Update search index
    log_info "Updating search index..."
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan scout:sync-index-settings || true
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan scout:import "App\Models\Post" || true

    # Generate sitemap
    docker-compose -f docker-compose.prod.yml exec -T ngb-app php artisan sitemap:generate || true
}

# Native PHP deployment
deploy_native() {
    log_info "Starting native PHP deployment..."

    cd "$PROJECT_DIR"

    # Copy production env
    cp "$ENV_FILE" .env

    # Install dependencies
    log_info "Installing composer dependencies..."
    composer install --no-dev --optimize-autoloader

    # Install npm dependencies and build assets
    log_info "Building frontend assets..."
    npm ci
    npm run build

    # Run Laravel commands
    log_info "Running Laravel setup commands..."
    php artisan migrate --force
    php artisan db:seed --force --class=ProductionSeeder || true
    php artisan storage:link || true

    # Clear and optimize
    log_info "Optimizing application..."
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan icons:cache || true

    # Set permissions
    log_info "Setting permissions..."
    sudo chown -R www-data:www-data storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache

    # Restart services
    log_info "Restarting services..."
    sudo systemctl reload php8.4-fpm
    sudo systemctl reload nginx
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl restart all

    # Queue restart
    php artisan queue:restart

    # Update search index
    log_info "Updating search index..."
    php artisan scout:sync-index-settings || true
    php artisan scout:import "App\Models\Post" || true

    # Generate sitemap
    php artisan sitemap:generate || true
}

# Health check
run_health_check() {
    log_info "Running health check..."

    sleep 5

    HEALTH_URL="http://localhost/health"
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_URL" || echo "000")

    if [ "$RESPONSE" = "200" ]; then
        log_info "Health check passed!"
        curl -s "$HEALTH_URL" | jq '.' || true
    else
        log_error "Health check failed! HTTP Status: $RESPONSE"

        # Show container logs if Docker deployment
        if [ "$DEPLOYMENT_TYPE" = "docker" ]; then
            log_warn "Showing recent container logs..."
            docker-compose -f docker-compose.prod.yml logs --tail=50
        fi

        return 1
    fi
}

# Post-deployment tasks
post_deployment_tasks() {
    log_info "Running post-deployment tasks..."

    # Clear CDN cache if configured
    if [ ! -z "$CLOUDFLARE_ZONE_ID" ] && [ ! -z "$CLOUDFLARE_API_TOKEN" ]; then
        log_info "Purging Cloudflare cache..."
        curl -X POST "https://api.cloudflare.com/client/v4/zones/$CLOUDFLARE_ZONE_ID/purge_cache" \
            -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
            -H "Content-Type: application/json" \
            --data '{"purge_everything":true}'
    fi

    # Send deployment notification
    log_info "Deployment completed successfully!"
}

# Rollback function
rollback() {
    log_error "Deployment failed! Starting rollback..."

    # Find latest backup
    LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/backup_*.tar.gz 2>/dev/null | head -1)

    if [ -z "$LATEST_BACKUP" ]; then
        log_error "No backup found for rollback!"
        exit 1
    fi

    log_info "Restoring from backup: $LATEST_BACKUP"

    cd "$PROJECT_DIR"
    tar -xzf "$LATEST_BACKUP" -C .

    # Restart services based on deployment type
    if [ "$DEPLOYMENT_TYPE" = "docker" ]; then
        docker-compose -f docker-compose.prod.yml restart
    else
        sudo systemctl reload php8.4-fpm
        sudo systemctl reload nginx
        sudo supervisorctl restart all
    fi

    log_warn "Rollback completed. Please investigate the issue."
}

# Main deployment flow
main() {
    log_info "Starting NextGenBeing deployment ($DEPLOYMENT_TYPE)..."

    # Run pre-deployment checks
    pre_deployment_checks

    # Create backup
    create_backup

    # Pull latest code
    pull_latest_code

    # Deploy based on type
    if [ "$DEPLOYMENT_TYPE" = "docker" ]; then
        deploy_docker
    else
        deploy_native
    fi

    # Run health check
    if run_health_check; then
        post_deployment_tasks
    else
        rollback
        exit 1
    fi
}

# Trap errors and run rollback
trap 'rollback' ERR

# Run main deployment
main

log_info "Deployment process completed!"
