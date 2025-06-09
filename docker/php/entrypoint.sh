#!/bin/sh
set -e

echo "🚀 Running Laravel Docker Entrypoint..."

# Ensure correct permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

# Only install composer dependencies if vendor folder is missing
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
else
    echo "⚠️  Vendor directory already exists. Skipping composer install."
fi

# Laravel setup
php artisan config:clear
php artisan config:cache

# Run migrations (fail-safe)
php artisan migrate --force || echo "⚠️  Migrations failed or already applied."

# Restart queue
php artisan queue:restart || echo "⚠️  Queue restart failed."

echo "✅ Laravel initialization complete. Starting PHP-FPM..."

exec "$@"
