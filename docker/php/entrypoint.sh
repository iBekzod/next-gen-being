#!/bin/sh
set -e

echo "Running Laravel Docker Entrypoint..."

# Always ensure vendor directory is properly set up
echo "Checking vendor directory..."

# Remove broken vendor directory if it exists
if [ -d "vendor" ] && [ ! -f "vendor/composer/autoload_real.php" ]; then
    echo "Vendor directory appears broken. Removing and reinstalling..."
    rm -rf vendor
fi

# Install or reinstall composer dependencies
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ] || [ ! -f "vendor/composer/autoload_real.php" ]; then
    echo "Installing composer dependencies..."
    COMPOSER_PROCESS_TIMEOUT=600 composer install \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-suggest \
        --no-progress
    echo "Composer installation complete."
else
    echo "Vendor directory OK."
fi

# Verify vendor directory is working
if [ ! -f "vendor/autoload.php" ]; then
    echo "Critical error: vendor/autoload.php still missing after installation"
    exit 1
fi

SETUP_MARKER="/var/www/html/storage/.laravel_setup_done"

# Check if .env file exists before running Laravel commands
if [ -f ".env" ]; then
    # Full setup only on first run or when forced
    if [ ! -f "$SETUP_MARKER" ] || [ "${FORCE_SETUP:-0}" = "1" ]; then
        echo "Running full Laravel setup..."

        if php artisan --version > /dev/null 2>&1; then
            echo "Laravel loaded OK."

            echo "Running package discovery..."
            php artisan package:discover --ansi || echo "Package discovery failed."

            echo "Publishing Laravel assets..."
            php artisan vendor:publish --tag=laravel-assets --ansi --force || echo "Asset publishing failed."

            # Generate application key if it doesn't exist
            if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d'=' -f2)" ]; then
                echo "Generating application key..."
                php artisan key:generate --no-interaction
            fi

            php artisan config:clear
            php artisan config:cache

            # Create storage link if it doesn't exist
            if [ ! -L "public/storage" ]; then
                echo "Creating storage link..."
                php artisan storage:link || echo "Storage link creation failed."
            fi

            # Run migrations
            php artisan migrate --force || echo "Migrations failed or already applied."

            # Restart queue
            php artisan queue:restart || echo "Queue restart failed."

            touch "$SETUP_MARKER"
            echo "Full Laravel setup complete!"
        else
            echo "Laravel failed to load. Check your application code and dependencies."
        fi
    else
        echo "Skipping full setup (already done). Run with FORCE_SETUP=1 to re-run."

        # Only run migrations on subsequent starts (catches new migrations)
        php artisan migrate --force 2>/dev/null || echo "Migration check skipped."
    fi
else
    echo ".env file not found. Skipping Laravel configuration."
fi

echo "Starting PHP-FPM..."

exec "$@"
