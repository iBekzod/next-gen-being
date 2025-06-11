#!/bin/sh
set -e

echo "🚀 Running Laravel Docker Entrypoint..."

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

npm install -D tailwindcss postcss autoprefixer || echo "⚠️ Node installation failed."

npx tailwindcss init -p || echo "⚠️ Npx installation failed."
# Restart queue
php artisan queue:restart || echo "⚠️  Queue restart failed."

echo "✅ Laravel initialization complete. Starting PHP-FPM..."

exec "$@"
