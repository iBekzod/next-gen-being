#!/bin/bash

# NextGenBeing Deployment Script
# Run this after pulling changes from git

echo "🚀 Starting deployment..."

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# Install/update dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Seed/update settings
echo "⚙️  Updating site settings..."
php artisan db:seed --class=SiteSettingSeeder --force

# Clear and cache configs
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "💾 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions (if needed)
echo "🔒 Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "✅ Deployment complete!"
