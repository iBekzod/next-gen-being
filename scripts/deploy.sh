#!/bin/bash
set -e

echo "🚀 Starting NextGenBeing deployment..."

if [ ! -f "docker-compose.prod.yml" ]; then
    echo "❌ docker-compose.prod.yml not found!"
    exit 1
fi

echo "🔄 Stopping existing containers..."
docker-compose -f docker-compose.prod.yml down

echo "📥 Pulling latest code..."
git fetch origin main
git reset --hard origin/main

echo "🏗️ Building and starting containers..."
docker-compose -f docker-compose.prod.yml build --no-cache
docker-compose -f docker-compose.prod.yml up -d

echo "⏳ Waiting for containers..."
sleep 30

echo "🔧 Running post-deployment tasks..."
docker-compose -f docker-compose.prod.yml exec -T app composer dump-autoload --optimize
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan event:cache

echo "🗄️ Optimizing database..."
docker-compose -f docker-compose.prod.yml exec -T database psql -U nextgen_user -d nextgenbeing -c "VACUUM ANALYZE;"

echo "💾 Clearing Redis cache..."
docker-compose -f docker-compose.prod.yml exec -T redis redis-cli FLUSHDB

echo "🔍 Rebuilding search index..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan scout:flush "App\Models\Post"
docker-compose -f docker-compose.prod.yml exec -T app php artisan scout:import "App\Models\Post"

echo "🗺️ Generating sitemap..."
docker-compose -f docker-compose.prod.yml exec -T app php artisan sitemap:generate

echo "✅ Performance optimization completed!"
