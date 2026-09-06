#!/bin/bash

# NextGenBeing Deployment Script
# Run this after pulling changes from git
#
# Every step is fatal. This script previously ran without `set -e`, so a
# failed `git pull` (blocked by local modifications) still ended with
# "Deployment complete!" while the old code stayed live — a silent
# non-deploy that looked like a successful one. Never remove the guards.

set -euo pipefail

fail() {
    echo ""
    echo "❌ DEPLOYMENT FAILED at: $1"
    echo "   The application was NOT updated. Fix the cause and re-run."
    exit 1
}

echo "🚀 Starting deployment..."

# Refuse to deploy on top of uncommitted work — pulling over it either
# aborts or destroys it, and both are worse discovered later.
echo "🔎 Checking working tree..."
if ! git diff --quiet || ! git diff --cached --quiet; then
    echo ""
    echo "❌ Uncommitted changes to tracked files:"
    git status --short --untracked-files=no | head -20
    echo ""
    echo "   Commit, stash, or restore them before deploying."
    echo "   If these are only file-mode changes, run: git config core.filemode false"
    exit 1
fi

BEFORE=$(git rev-parse --short HEAD)

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main || fail "git pull"

AFTER=$(git rev-parse --short HEAD)
if [ "$BEFORE" = "$AFTER" ]; then
    echo "   Already at ${AFTER} — no new commits."
else
    echo "   ${BEFORE} → ${AFTER}"
fi

# Install/update dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader || fail "composer install"

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force || fail "php artisan migrate"

# Seed/update settings
echo "⚙️  Updating site settings..."
php artisan db:seed --class=SiteSettingSeeder --force || fail "db:seed SiteSettingSeeder"

# Clear and cache configs
echo "🧹 Clearing caches..."
php artisan config:clear || fail "config:clear"
php artisan cache:clear || fail "cache:clear"
php artisan route:clear || fail "route:clear"
php artisan view:clear || fail "view:clear"

echo "💾 Optimizing application..."
php artisan config:cache || fail "config:cache"
php artisan route:cache || fail "route:cache"
php artisan view:cache || fail "view:cache"

# Set permissions (if needed)
echo "🔒 Setting permissions..."
chmod -R 775 storage bootstrap/cache || fail "chmod"

# Prove the deployed tree actually boots before declaring success.
echo "🩺 Verifying the application boots..."
php artisan --version > /dev/null || fail "application boot check"

echo "✅ Deployment complete! Now at ${AFTER}"
