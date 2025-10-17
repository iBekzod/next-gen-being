#!/bin/bash

echo "🚀 Completing Social Sharing Enhancement Setup..."
echo "==========================================="

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Step 1: Add routes to web.php
echo -e "${BLUE}🛣️  Adding social sharing routes...${NC}"

ROUTES_CODE="
// Social Sharing Routes
Route::prefix('api/social-share')->name('social-share.')->group(function () {
    Route::post('/track', [App\Http\Controllers\SocialShareController::class, 'track'])->name('track');
    Route::get('/count/{postId}', [App\Http\Controllers\SocialShareController::class, 'getShareCount'])->name('count');
    Route::get('/breakdown/{postId}', [App\Http\Controllers\SocialShareController::class, 'getPlatformBreakdown'])->name('breakdown');
    Route::get('/top-shared', [App\Http\Controllers\SocialShareController::class, 'getTopShared'])->name('top-shared');
    Route::post('/generate-utm-url', [App\Http\Controllers\SocialShareController::class, 'generateUtmUrl'])->name('generate-utm-url');
})->middleware('throttle:60,1');
"

# Check if routes already exist
if ! grep -q "social-share.track" routes/web.php; then
    echo "$ROUTES_CODE" >> routes/web.php
    echo -e "${GREEN}✅ Routes added to web.php${NC}"
else
    echo -e "${GREEN}✅ Routes already exist in web.php${NC}"
fi

# Step 2: Run migrations
echo -e "${BLUE}🗄️  Running database migrations...${NC}"
php artisan migrate --force

# Step 3: Clear caches
echo -e "${BLUE}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "================================"
echo -e "${GREEN}✅ Social Sharing Setup Complete!${NC}"
echo "================================"
echo ""
echo "📋 NEXT STEPS:"
echo ""
echo "1. Update JavaScript share functions in resources/js/app.js"
echo "   - Add tracking calls before opening social platforms"
echo "   - Add WhatsApp and Telegram share functions"
echo "   - Add GA4 event tracking"
echo ""
echo "2. Add share count display to post pages:"
echo "   - Use @livewire('share-count', ['post' => \$post])"
echo ""
echo "3. Test the tracking endpoint:"
echo "   curl -X POST http://yoursite.test/api/social-share/track \\"
echo "     -H \"Content-Type: application/json\" \\"
echo "     -H \"X-CSRF-TOKEN: your-token\" \\"
echo "     -d '{\"platform\":\"twitter\",\"post_id\":1}'"
echo ""
echo "4. Monitor share analytics in database:"
echo "   SELECT platform, COUNT(*) as shares FROM social_shares GROUP BY platform;"
echo ""
echo "📚 Documentation:"
echo "   - SOCIAL_SHARING_IMPLEMENTATION_PLAN.md"
echo ""
echo "🎉 Happy Sharing!"
