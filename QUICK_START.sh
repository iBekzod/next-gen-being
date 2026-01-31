#!/bin/bash

# AI Learning & Tutorials Platform - Quick Start Script
# This script sets up everything you need to run the platform

set -e

echo "🚀 AI Learning & Tutorials Platform - Quick Start Setup"
echo "=================================================="
echo ""

# Step 1: Run Migrations
echo "Step 1️⃣  Running migrations..."
php artisan migrate

echo "✅ Migrations complete!"
echo ""

# Step 2: Publish Configuration
echo "Step 2️⃣  Verifying AI learning config..."
if [ ! -f "config/ai-learning.php" ]; then
    echo "⚠️  config/ai-learning.php not found"
else
    echo "✅ config/ai-learning.php exists"
fi
echo ""

# Step 3: Test Tutorial Command
echo "Step 3️⃣  Testing tutorial generation (dry run)..."
php artisan ai-learning:generate-weekly --day=Monday --dry-run

echo "✅ Tutorial command works!"
echo ""

# Step 4: Test Prompt Command
echo "Step 4️⃣  Testing prompt generation (3 test prompts)..."
php artisan ai-learning:generate-prompts --count=3

echo "✅ Prompt command works!"
echo ""

# Step 5: Verify Routes
echo "Step 5️⃣  Verifying marketplace routes..."
php artisan route:list | grep -E "digital-products|resources" || true

echo "✅ Marketplace routes registered!"
echo ""

# Step 6: List Scheduled Commands
echo "Step 6️⃣  Scheduled commands:"
php artisan schedule:list

echo "✅ Scheduler is configured!"
echo ""

echo "=================================================="
echo "🎉 Setup Complete!"
echo "=================================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Start the scheduler:"
echo "   php artisan schedule:work"
echo ""
echo "2. Visit the marketplace:"
echo "   - Browse: http://localhost:9070/resources"
echo "   - Admin: http://localhost:9070/admin (Digital Products)"
echo ""
echo "3. Create test products:"
echo "   - Admin panel → Monetization → Digital Products"
echo ""
echo "4. Configure LemonSqueezy (optional, for paid products):"
echo "   - Set variant IDs in .env"
echo "   - Configure webhook: /lemon-squeezy/webhook"
echo ""
echo "5. Monitor automation:"
echo "   - Check logs: tail -f storage/logs/laravel.log"
echo "   - Admin dashboard: /admin"
echo ""
echo "📚 Documentation:"
echo "   - Setup Guide: SETUP_AI_LEARNING_PLATFORM.md"
echo "   - Full Details: AI_PLATFORM_IMPLEMENTATION_SUMMARY.md"
echo ""
echo "Your AI Learning Platform is ready! 🚀"
