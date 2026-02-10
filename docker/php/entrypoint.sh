#!/bin/sh
set -e

echo "🚀 Running Laravel Docker Entrypoint..."

# Ensure runtime writable directories
# mkdir -p storage/logs bootstrap/cache
# chmod -R 775 storage bootstrap/cache

# Always ensure vendor directory is properly set up
echo "🔧 Checking and fixing vendor directory..."

# Remove broken vendor directory if it exists
if [ -d "vendor" ] && [ ! -f "vendor/composer/autoload_real.php" ]; then
    echo "⚠️  Vendor directory appears broken. Removing and reinstalling..."
    rm -rf vendor
fi

# Install or reinstall composer dependencies
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ] || [ ! -f "vendor/composer/autoload_real.php" ]; then
    echo "📦 Installing composer dependencies (including dev dependencies)..."
    # Increase timeout and use optimized settings for Docker
    COMPOSER_PROCESS_TIMEOUT=600 composer install \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-suggest \
        --no-progress
    echo "✅ Composer installation complete."
else
    echo "✅ Vendor directory exists and looks good."
fi

# Verify vendor directory is working
if [ ! -f "vendor/autoload.php" ]; then
    echo "❌ Critical error: vendor/autoload.php still missing after installation"
    exit 1
fi

# Check if .env file exists before running Laravel commands
if [ -f ".env" ]; then
    echo "🔧 Configuring Laravel application..."

    # Test if Laravel can load (test artisan before running commands)
    if php artisan --version > /dev/null 2>&1; then
        echo "✅ Laravel is loading correctly."

        # Run package discovery
        echo "🔍 Running package discovery..."
        php artisan package:discover --ansi || echo "⚠️  Package discovery failed."

        echo "📦 Publishing Laravel assets..."
        php artisan vendor:publish --tag=laravel-assets --ansi --force || echo "⚠️  Asset publishing failed."

        # Generate application key if it doesn't exist
        if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d'=' -f2)" ]; then
            echo "🔑 Generating application key..."
            php artisan key:generate --no-interaction
        fi

        # Laravel setup
        php artisan config:clear
        php artisan config:cache

        # Create storage link if it doesn't exist
        if [ ! -L "public/storage" ]; then
            echo "🔗 Creating storage link..."
            php artisan storage:link || echo "⚠️  Storage link creation failed."
        fi

        # Run migrations (fail-safe)
        php artisan migrate --force || echo "⚠️  Migrations failed or already applied."

        # Restart queue
        php artisan queue:restart || echo "⚠️  Queue restart failed."

        echo "✅ Laravel configuration complete!"
    else
        echo "❌ Laravel failed to load. Check your application code and dependencies."
        # Don't exit here, let the container start so we can debug
    fi
else
    echo "⚠️  .env file not found. Skipping Laravel configuration."
fi

echo "✅ Laravel initialization complete. Starting PHP-FPM..."

exec "$@"
