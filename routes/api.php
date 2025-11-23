<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController as ApiPostController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\WritingAssistantController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\TutorialGenerationController;

Route::prefix('v1')->group(function () {
    // Public API routes
    Route::get('/posts', [ApiPostController::class, 'index']);
    Route::get('/posts/{post:slug}', [ApiPostController::class, 'show']);
    Route::get('/posts/featured', [ApiPostController::class, 'featured']);
    Route::get('/posts/popular', [ApiPostController::class, 'popular']);

    Route::get('/categories', [ApiCategoryController::class, 'index']);
    Route::get('/categories/{category:slug}', [ApiCategoryController::class, 'show']);

    // Protected API routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/posts/{post}/like', [ApiPostController::class, 'like']);
        Route::post('/posts/{post}/bookmark', [ApiPostController::class, 'bookmark']);
        Route::post('/posts/{post}/comments', [ApiPostController::class, 'storeComment']);

        Route::get('/user/profile', [ApiUserController::class, 'profile']);
        Route::put('/user/profile', [ApiUserController::class, 'updateProfile']);
        Route::get('/user/posts', [ApiUserController::class, 'posts']);
        Route::get('/user/bookmarks', [ApiUserController::class, 'bookmarks']);

        // Writing Assistant API routes
        Route::prefix('writing')->name('writing.')->group(function () {
            Route::post('/improve-text', [WritingAssistantController::class, 'improveText'])->name('improve-text');
            Route::post('/check-grammar', [WritingAssistantController::class, 'checkGrammar'])->name('check-grammar');
            Route::post('/style-suggestions', [WritingAssistantController::class, 'getStyleSuggestions'])->name('style-suggestions');
            Route::post('/readability', [WritingAssistantController::class, 'analyzeReadability'])->name('readability');
            Route::post('/analyze-tone', [WritingAssistantController::class, 'analyzeTone'])->name('analyze-tone');
            Route::post('/content-suggestions', [WritingAssistantController::class, 'generateContentSuggestions'])->name('content-suggestions');
            Route::post('/headline-suggestions', [WritingAssistantController::class, 'getHeadlineSuggestions'])->name('headline-suggestions');
            Route::post('/introduction-templates', [WritingAssistantController::class, 'getIntroductionTemplates'])->name('introduction-templates');
            Route::post('/content-outlines', [WritingAssistantController::class, 'getContentOutlines'])->name('content-outlines');
            Route::post('/extract-keywords', [WritingAssistantController::class, 'extractKeywords'])->name('extract-keywords');
            Route::post('/generate-content', [WritingAssistantController::class, 'generateContent'])->name('generate-content');
        });

        // Webhook API routes
        Route::prefix('webhooks')->name('webhooks.')->group(function () {
            Route::get('/', [WebhookController::class, 'index'])->name('index');
            Route::post('/', [WebhookController::class, 'store'])->name('store');
            Route::get('/events/available', [WebhookController::class, 'getAvailableEvents'])->name('available-events');
            Route::get('/{webhook}', [WebhookController::class, 'show'])->name('show');
            Route::put('/{webhook}', [WebhookController::class, 'update'])->name('update');
            Route::delete('/{webhook}', [WebhookController::class, 'destroy'])->name('destroy');
            Route::post('/{webhook}/test', [WebhookController::class, 'test'])->name('test');
            Route::get('/{webhook}/logs', [WebhookController::class, 'logs'])->name('logs');
            Route::get('/{webhook}/statistics', [WebhookController::class, 'statistics'])->name('statistics');
        });

        // Notification API routes
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::post('/{notification}/mark-read', [WebhookController::class, 'markAsRead'])->name('mark-read');
            Route::post('/{notification}/mark-unread', [WebhookController::class, 'markAsUnread'])->name('mark-unread');
            Route::delete('/{notification}', [WebhookController::class, 'delete'])->name('delete');
        });

        // Invoice API routes
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/payout/{payout}', [InvoiceController::class, 'downloadPayoutInvoice'])->name('payout');
            Route::get('/payout/{payout}/preview', [InvoiceController::class, 'getPayoutInvoicePreview'])->name('payout-preview');
            Route::get('/earnings', [InvoiceController::class, 'downloadEarningsInvoice'])->name('earnings');
            Route::get('/earnings/summary', [InvoiceController::class, 'getEarningsSummary'])->name('earnings-summary');
            Route::get('/tax-form', [InvoiceController::class, 'downloadTaxForm'])->name('tax-form');
            Route::get('/payouts', [InvoiceController::class, 'getUserPayouts'])->name('payouts');
            Route::get('/statistics', [InvoiceController::class, 'getStatistics'])->name('statistics');
        });

        // Tutorial Generation API routes (Admin only)
        Route::prefix('tutorials')->name('tutorials.')->group(function () {
            Route::get('/status', [TutorialGenerationController::class, 'status'])->name('status');
            Route::post('/trigger', [TutorialGenerationController::class, 'trigger'])->name('trigger');
            Route::get('/history', [TutorialGenerationController::class, 'history'])->name('history');
            Route::post('/publish', [TutorialGenerationController::class, 'publishSeries'])->name('publish');
            Route::get('/config', [TutorialGenerationController::class, 'configuration'])->name('config');
        });

        // Engagement & Monetization Features Routes
        Route::prefix('features')->name('features.')->group(function () {
            // Tips
            Route::post('/tips/initiate', [\App\Http\Controllers\TipController::class, 'initiate'])->name('tips.initiate');
            Route::get('/tips/stats/{userId}', [\App\Http\Controllers\TipController::class, 'stats'])->name('tips.stats');
            Route::get('/tips/leaderboard', [\App\Http\Controllers\TipController::class, 'leaderboard'])->name('tips.leaderboard');
            Route::get('/tips/my-sent', [\App\Http\Controllers\TipController::class, 'myTipsSent'])->name('tips.sent');
            Route::get('/tips/my-received', [\App\Http\Controllers\TipController::class, 'myTipsReceived'])->name('tips.received');

            // Streaks
            Route::get('/streaks/{userId}', [\App\Http\Controllers\StreakController::class, 'show'])->name('streaks.show');
            Route::get('/streaks/leaderboard/reading', [\App\Http\Controllers\StreakController::class, 'readingLeaderboard'])->name('streaks.reading');
            Route::get('/streaks/leaderboard/writing', [\App\Http\Controllers\StreakController::class, 'writingLeaderboard'])->name('streaks.writing');
            Route::get('/streaks/my', [\App\Http\Controllers\StreakController::class, 'myStreaks'])->name('streaks.my');
            Route::post('/streaks/record/reading', [\App\Http\Controllers\StreakController::class, 'recordReading'])->name('streaks.record.reading');
            Route::post('/streaks/record/writing', [\App\Http\Controllers\StreakController::class, 'recordWriting'])->name('streaks.record.writing');

            // Leaderboards
            Route::get('/leaderboards/creators', [\App\Http\Controllers\LeaderboardController::class, 'creators'])->name('leaderboards.creators');
            Route::get('/leaderboards/readers', [\App\Http\Controllers\LeaderboardController::class, 'readers'])->name('leaderboards.readers');
            Route::get('/leaderboards/engagers', [\App\Http\Controllers\LeaderboardController::class, 'engagers'])->name('leaderboards.engagers');
            Route::get('/leaderboards/trending', [\App\Http\Controllers\LeaderboardController::class, 'trending'])->name('leaderboards.trending');
            Route::get('/leaderboards/user-rank', [\App\Http\Controllers\LeaderboardController::class, 'userRank'])->name('leaderboards.rank');

            // Challenges
            Route::get('/challenges', [\App\Http\Controllers\ChallengeController::class, 'index'])->name('challenges.index');
            Route::get('/challenges/{challenge}', [\App\Http\Controllers\ChallengeController::class, 'show'])->name('challenges.show');
            Route::post('/challenges/{challenge}/join', [\App\Http\Controllers\ChallengeController::class, 'join'])->name('challenges.join');
            Route::post('/challenges/{challenge}/progress', [\App\Http\Controllers\ChallengeController::class, 'updateProgress'])->name('challenges.progress');
            Route::post('/challenges/{challenge}/claim-reward', [\App\Http\Controllers\ChallengeController::class, 'claimReward'])->name('challenges.reward');
            Route::get('/challenges/{challenge}/leaderboard', [\App\Http\Controllers\ChallengeController::class, 'leaderboard'])->name('challenges.leaderboard');
            Route::get('/challenges/{challenge}/stats', [\App\Http\Controllers\ChallengeController::class, 'stats'])->name('challenges.stats');
            Route::get('/challenges/my', [\App\Http\Controllers\ChallengeController::class, 'myChallenges'])->name('challenges.my');

            // Collections
            Route::get('/collections', [\App\Http\Controllers\CollectionController::class, 'index'])->name('collections.index');
            Route::post('/collections', [\App\Http\Controllers\CollectionController::class, 'store'])->name('collections.store');
            Route::get('/collections/public', [\App\Http\Controllers\CollectionController::class, 'publicCollections'])->name('collections.public');
            Route::get('/collections/trending', [\App\Http\Controllers\CollectionController::class, 'trending'])->name('collections.trending');
            Route::get('/collections/search', [\App\Http\Controllers\CollectionController::class, 'search'])->name('collections.search');
            Route::get('/collections/my-saved', [\App\Http\Controllers\CollectionController::class, 'mySaved'])->name('collections.saved');
            Route::get('/collections/{collection}', [\App\Http\Controllers\CollectionController::class, 'show'])->name('collections.show');
            Route::put('/collections/{collection}', [\App\Http\Controllers\CollectionController::class, 'update'])->name('collections.update');
            Route::delete('/collections/{collection}', [\App\Http\Controllers\CollectionController::class, 'destroy'])->name('collections.destroy');
            Route::post('/collections/{collection}/posts', [\App\Http\Controllers\CollectionController::class, 'addPost'])->name('collections.add-post');
            Route::delete('/collections/{collection}/posts/{post}', [\App\Http\Controllers\CollectionController::class, 'removePost'])->name('collections.remove-post');
            Route::post('/collections/{collection}/save', [\App\Http\Controllers\CollectionController::class, 'toggleSave'])->name('collections.toggle-save');

            // Content Calendar
            Route::post('/schedule/posts', [\App\Http\Controllers\ContentCalendarController::class, 'schedule'])->name('schedule.store');
            Route::post('/schedule/drafts', [\App\Http\Controllers\ContentCalendarController::class, 'saveDraft'])->name('schedule.draft');
            Route::get('/schedule/calendar', [\App\Http\Controllers\ContentCalendarController::class, 'calendar'])->name('schedule.calendar');
            Route::get('/schedule/upcoming', [\App\Http\Controllers\ContentCalendarController::class, 'upcoming'])->name('schedule.upcoming');
            Route::get('/schedule/drafts', [\App\Http\Controllers\ContentCalendarController::class, 'drafts'])->name('schedule.drafts');
            Route::get('/schedule/history', [\App\Http\Controllers\ContentCalendarController::class, 'history'])->name('schedule.history');
            Route::get('/schedule/stats', [\App\Http\Controllers\ContentCalendarController::class, 'stats'])->name('schedule.stats');
            Route::get('/schedule/suggestions', [\App\Http\Controllers\ContentCalendarController::class, 'suggestions'])->name('schedule.suggestions');
            Route::put('/schedule/{scheduled}', [\App\Http\Controllers\ContentCalendarController::class, 'update'])->name('schedule.update');
            Route::delete('/schedule/{scheduled}', [\App\Http\Controllers\ContentCalendarController::class, 'delete'])->name('schedule.delete');

            // Analytics
            Route::get('/analytics/dashboard', [\App\Http\Controllers\AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
            Route::get('/analytics/performance', [\App\Http\Controllers\AnalyticsController::class, 'performance'])->name('analytics.performance');
            Route::get('/analytics/audience', [\App\Http\Controllers\AnalyticsController::class, 'audience'])->name('analytics.audience');
            Route::get('/analytics/revenue', [\App\Http\Controllers\AnalyticsController::class, 'revenue'])->name('analytics.revenue');
            Route::get('/analytics/daily', [\App\Http\Controllers\AnalyticsController::class, 'dailyAnalytics'])->name('analytics.daily');

            // Affiliates
            Route::post('/affiliate/links', [\App\Http\Controllers\AffiliateController::class, 'createLink'])->name('affiliate.create');
            Route::get('/affiliate/links', [\App\Http\Controllers\AffiliateController::class, 'listLinks'])->name('affiliate.list');
            Route::get('/affiliate/links/{link}/stats', [\App\Http\Controllers\AffiliateController::class, 'stats'])->name('affiliate.stats');
            Route::get('/affiliate/earnings', [\App\Http\Controllers\AffiliateController::class, 'earnings'])->name('affiliate.earnings');
            Route::get('/affiliate/leaderboard', [\App\Http\Controllers\AffiliateController::class, 'leaderboard'])->name('affiliate.leaderboard');

            // Reader Preferences
            Route::get('/preferences', [\App\Http\Controllers\PreferenceController::class, 'show'])->name('preferences.show');
            Route::put('/preferences', [\App\Http\Controllers\PreferenceController::class, 'update'])->name('preferences.update');
            Route::post('/preferences/reset', [\App\Http\Controllers\PreferenceController::class, 'reset'])->name('preferences.reset');
            Route::post('/preferences/dislike/{categoryId}', [\App\Http\Controllers\PreferenceController::class, 'dislike'])->name('preferences.dislike');
            Route::get('/preferences/recommendations', [\App\Http\Controllers\PreferenceController::class, 'recommendations'])->name('preferences.recommendations');

            // Creator Tools
            Route::post('/creator-tools/ideas/generate', [\App\Http\Controllers\CreatorToolsController::class, 'generateIdeas'])->name('tools.ideas.generate');
            Route::get('/creator-tools/ideas', [\App\Http\Controllers\CreatorToolsController::class, 'listIdeas'])->name('tools.ideas.list');
            Route::post('/creator-tools/ideas/{idea}/outline', [\App\Http\Controllers\CreatorToolsController::class, 'generateOutline'])->name('tools.ideas.outline');
            Route::post('/creator-tools/seo/analyze', [\App\Http\Controllers\CreatorToolsController::class, 'analyzeSEO'])->name('tools.seo');
            Route::get('/creator-tools/audience', [\App\Http\Controllers\CreatorToolsController::class, 'audience'])->name('tools.audience');
            Route::get('/creator-tools/suggestions', [\App\Http\Controllers\CreatorToolsController::class, 'suggestions'])->name('tools.suggestions');
            Route::get('/creator-tools/report', [\App\Http\Controllers\CreatorToolsController::class, 'report'])->name('tools.report');
            Route::put('/creator-tools/ideas/{idea}', [\App\Http\Controllers\CreatorToolsController::class, 'updateIdea'])->name('tools.ideas.update');
            Route::delete('/creator-tools/ideas/{idea}', [\App\Http\Controllers\CreatorToolsController::class, 'deleteIdea'])->name('tools.ideas.delete');
        });
    });

    // Public tutorial API endpoints
    Route::prefix('tutorials')->name('tutorials.')->group(function () {
        Route::get('/config', [TutorialGenerationController::class, 'configuration'])->name('config');
    });
});
