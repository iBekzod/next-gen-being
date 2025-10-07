#!/bin/bash
# Server Troubleshooting Script for NextGenBeing

set -e

SERVER_IP="${1:-YOUR_SERVER_IP}"
SSH_USER="${2:-YOUR_SSH_USER}"
APP_DIR="/var/www/nextgenbeing"

echo "🔍 Troubleshooting nextgenbeing.com on $SERVER_IP"
echo "================================================"

# Function to run commands on server
run_remote() {
    ssh "$SSH_USER@$SERVER_IP" "$1"
}

echo ""
echo "1️⃣ Checking if site is in maintenance mode..."
run_remote "cd $APP_DIR && sudo -u www-data php artisan up" || echo "   ⚠️  Could not take site out of maintenance mode"

echo ""
echo "2️⃣ Checking PHP-FPM status..."
run_remote "sudo systemctl status php8.4-fpm --no-pager" || echo "   ❌ PHP-FPM is not running!"

echo ""
echo "3️⃣ Checking Nginx status..."
run_remote "sudo systemctl status nginx --no-pager" || echo "   ❌ Nginx is not running!"

echo ""
echo "4️⃣ Checking PostgreSQL connection..."
run_remote "cd $APP_DIR && sudo -u www-data php artisan tinker --execute='DB::connection()->getPdo();'" || echo "   ❌ Cannot connect to database!"

echo ""
echo "5️⃣ Checking application logs (last 20 lines)..."
run_remote "sudo tail -n 20 $APP_DIR/storage/logs/laravel.log" || echo "   ⚠️  No logs found"

echo ""
echo "6️⃣ Checking Nginx error logs (last 20 lines)..."
run_remote "sudo tail -n 20 /var/log/nginx/error.log" || echo "   ⚠️  No Nginx errors found"

echo ""
echo "7️⃣ Checking file permissions..."
run_remote "ls -la $APP_DIR/storage | head -10"

echo ""
echo "8️⃣ Verifying .env file exists..."
run_remote "ls -l $APP_DIR/.env" || echo "   ❌ .env file is missing!"

echo ""
echo "✅ Troubleshooting complete!"
echo ""
echo "📝 Common fixes:"
echo "   - Restart services: sudo systemctl restart php8.4-fpm nginx"
echo "   - Clear caches: cd $APP_DIR && php artisan cache:clear && php artisan config:clear"
echo "   - Exit maintenance: cd $APP_DIR && php artisan up"
echo "   - Check logs: tail -f $APP_DIR/storage/logs/laravel.log"
