#!/bin/bash
set -e

# Fix common issues in NextGenBeing Laravel project

echo "🔧 Fixing NextGenBeing Project Issues..."

# 1. Fix PHP version references
echo "📝 Updating PHP version references to 8.4..."
find . -type f -name "*.sh" -o -name "*.yml" -o -name "*.yaml" -o -name "*.conf" | \
    xargs sed -i 's/php8\.3/php8.4/g' 2>/dev/null || true

# 2. Create missing directories
echo "📁 Creating required directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/{css,js,build}
mkdir -p database/seeders
mkdir -p docker/db
mkdir -p certbot/{conf,www}
mkdir -p scripts

# 3. Fix permissions
echo "🔐 Setting correct permissions..."
chmod -R 775 storage bootstrap/cache
chmod +x scripts/*.sh 2>/dev/null || true
chmod +x docker/php/entrypoint.sh 2>/dev/null || true

# 4. Create missing configuration files
echo "📄 Creating missing configuration files..."

# Create PostgreSQL production config if missing
if [ ! -f "docker/db/postgresql.prod.conf" ]; then
cat > docker/db/postgresql.prod.conf << 'EOF'
# PostgreSQL Production Configuration
listen_addresses = '*'
max_connections = 200
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
work_mem = 2MB
min_wal_size = 1GB
max_wal_size = 4GB
EOF
fi

# 5. Update composer.json scripts
echo "📦 Updating composer.json scripts..."
if [ -f "composer.json" ]; then
    # Backup original
    cp composer.json composer.json.backup

    # Add deployment scripts using PHP
    php -r '
    $json = json_decode(file_get_contents("composer.json"), true);
    if (!isset($json["scripts"]["deploy"])) {
        $json["scripts"]["deploy"] = [
            "@php artisan migrate --force",
            "@php artisan config:cache",
            "@php artisan route:cache",
            "@php artisan view:cache",
            "@php artisan queue:restart"
        ];
    }
    file_put_contents("composer.json", json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    '
fi

# 6. Create production seeder if missing
if [ ! -f "database/seeders/ProductionSeeder.php" ]; then
echo "🌱 Creating ProductionSeeder..."
cat > database/seeders/ProductionSeeder.php << 'EOF'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed essential production data
        $this->call([
            // Add production-safe seeders here
            // For example: RolesSeeder::class,
        ]);
    }
}
EOF
fi

# 7. Fix .gitignore
echo "📝 Updating .gitignore..."
cat >> .gitignore << 'EOF'

# Additional ignores
.env.production
docker/nginx/ssl/*
certbot/
*.log
supervisord.pid
ngb-db-data/
ngb-redis-data/
ngb-search-data/
EOF

# 8. Create helper script for common tasks
echo "🛠️ Creating helper script..."
cat > manage.sh << 'EOF'
#!/bin/bash
# NextGenBeing Management Script

case "$1" in
    start)
        docker-compose up -d
        ;;
    stop)
        docker-compose down
        ;;
    restart)
        docker-compose restart
        ;;
    logs)
        docker-compose logs -f ${2:-ngb-app}
        ;;
    artisan)
        docker-compose exec ngb-app php artisan "${@:2}"
        ;;
    composer)
        docker-compose exec ngb-app composer "${@:2}"
        ;;
    npm)
        docker-compose exec ngb-app npm "${@:2}"
        ;;
    shell)
        docker-compose exec ngb-app sh
        ;;
    db)
        docker-compose exec ngb-database psql -U ${DB_USERNAME:-laravel} -d ${DB_DATABASE:-nextgenbeing}
        ;;
    redis)
        docker-compose exec ngb-redis redis-cli
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|logs|artisan|composer|npm|shell|db|redis}"
        exit 1
        ;;
esac
EOF
chmod +x manage.sh

# 9. Validate environment files
echo "✅ Validating environment files..."
if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
    echo "Created .env from .env.example"
fi

# 10. Fix Redis configuration
echo "🔧 Fixing Redis configuration..."
if [ -f "docker/redis/redis.conf" ]; then
    # Remove the hardcoded password line
    sed -i '/requirepass mysecurepassword/d' docker/redis/redis.conf
    # Update to use environment variable
    echo "# Password will be set via command line parameter" >> docker/redis/redis.conf
fi

# 11. Update docker-compose.yml for Redis password
echo "🐳 Updating docker-compose files..."
if [ -f "docker-compose.yml" ]; then
    # Update Redis service to use password from env
    sed -i 's|redis-server /etc/redis/redis.conf|redis-server /etc/redis/redis.conf --requirepass ${REDIS_PASSWORD:-secret}|g' docker-compose.yml
fi

# 12. Final checks
echo "🔍 Running final checks..."

# Check if required PHP version is available
if ! php -v | grep -q "PHP 8.4"; then
    echo "⚠️  Warning: PHP 8.4 not found. Please install PHP 8.4"
fi

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "⚠️  Warning: Composer not found. Please install Composer"
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "⚠️  Warning: npm not found. Please install Node.js and npm"
fi

echo "✅ Project fixes completed!"
echo ""
echo "Next steps:"
echo "1. Review and update .env file with your configuration"
echo "2. Run 'composer install' to install PHP dependencies"
echo "3. Run 'npm install' to install Node.js dependencies"
echo "4. Run 'docker-compose up -d' to start development environment"
echo "5. Run 'php artisan key:generate' to generate application key"
echo "6. Run 'php artisan migrate' to create database tables"
echo ""
echo "For production deployment, see DEPLOYMENT.md"
