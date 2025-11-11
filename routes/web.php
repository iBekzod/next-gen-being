<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\BloggerProfileController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WriteEarnController;
use Illuminate\Support\Facades\Auth;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/write', [WriteEarnController::class, 'show'])->name('write.earn');
Route::post('/landing/subscribe', [LandingPageController::class, 'store'])->name('landing.subscribe');
Route::get('/health', HealthCheckController::class)->name('health.check');

// Post creation route (must come before posts.show to avoid slug conflict)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post:slug}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

// Public routes
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/tutorials', [PostController::class, 'tutorials'])->name('tutorials.index');
Route::get('/series/{seriesSlug}', [PostController::class, 'series'])->name('series.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/api/search/trending', [SearchController::class, 'trending'])->name('search.trending');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');

// Feed routes
Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
Route::get('/explore', [FeedController::class, 'global'])->name('feed.global');

// Blogger routes
Route::get('/bloggers', [BloggerProfileController::class, 'index'])->name('bloggers.index');
Route::get('/blogger/{username}', [BloggerProfileController::class, 'show'])->name('bloggers.profile');

// Subscription routes
Route::get('/pricing', [SubscriptionController::class, 'plans'])->name('subscription.plans');

// Authentication routes

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // User dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/posts', [UserDashboardController::class, 'posts'])->name('dashboard.posts');
    Route::get('/dashboard/bookmarks', [UserDashboardController::class, 'bookmarks'])->name('dashboard.bookmarks');
    Route::get('/dashboard/earnings', [UserDashboardController::class, 'earnings'])->name('dashboard.earnings');
    Route::get('/dashboard/payouts', [UserDashboardController::class, 'payouts'])->name('dashboard.payouts');
    Route::get('/dashboard/videos', [UserDashboardController::class, 'videos'])->name('dashboard.videos');
    Route::get('/dashboard/social-media', [UserDashboardController::class, 'socialMedia'])->name('dashboard.social-media');
    Route::get('/dashboard/analytics', [UserDashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::get('/dashboard/webhooks', [UserDashboardController::class, 'webhooks'])->name('dashboard.webhooks');
    Route::get('/dashboard/notifications', [UserDashboardController::class, 'notifications'])->name('dashboard.notifications');
    Route::get('/dashboard/jobs', [UserDashboardController::class, 'jobStatus'])->name('dashboard.jobs');
    Route::get('/dashboard/calendar', [UserDashboardController::class, 'contentCalendar'])->name('dashboard.calendar');
    Route::get('/dashboard/quota', [UserDashboardController::class, 'aiQuota'])->name('dashboard.quota');
    Route::get('/dashboard/leaderboard', [UserDashboardController::class, 'leaderboard'])->name('dashboard.learning-leaderboard');
    Route::get('/dashboard/settings', [UserDashboardController::class, 'settings'])->name('dashboard.settings');

    // Webhook management routes
    Route::get('/webhooks/create', [WebhookController::class, 'create'])->name('webhooks.create');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::get('/webhooks/{webhook}', [WebhookController::class, 'show'])->name('webhooks.show');
    Route::get('/webhooks/{webhook}/edit', [WebhookController::class, 'edit'])->name('webhooks.edit');
    Route::put('/webhooks/{webhook}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');

    // Notification management routes
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notification.mark-read');
    Route::post('/notifications/{notification}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('notification.mark-unread');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'delete'])->name('notification.delete');

    // Subscription management
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/subscription/manage', [SubscriptionController::class, 'manage'])->name('subscription.manage');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel.post');
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume'])->name('subscription.resume');
    Route::post('/subscription/pause', [SubscriptionController::class, 'pauseSubscription'])->name('subscription.pause');

    Route::put('/dashboard/settings', [UserDashboardController::class, 'updateSettings'])->name('dashboard.settings.update');
    Route::put('/dashboard/settings/password', [UserDashboardController::class, 'updatePassword'])->name('dashboard.settings.password');
    Route::delete('/dashboard/settings', [UserDashboardController::class, 'deleteAccount'])->name('dashboard.settings.delete');

    // Admin moderation routes (add role check middleware later)
    Route::prefix('admin/moderation')->name('admin.moderation.')->group(function () {
        Route::get('/', [ModerationController::class, 'index'])->name('index');
        Route::get('/{post}', [ModerationController::class, 'show'])->name('show');
        Route::post('/{post}/approve', [ModerationController::class, 'approve'])->name('approve');
        Route::post('/{post}/reject', [ModerationController::class, 'reject'])->name('reject');
        Route::post('/{post}/recheck', [ModerationController::class, 'recheck'])->name('recheck');
        Route::post('/bulk-approve', [ModerationController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [ModerationController::class, 'bulkReject'])->name('bulk-reject');
    });
});

// LemonSqueezy webhook routes (handled automatically by LemonSqueezy Laravel package)

Route::get('/feed.xml', [FeedController::class, 'rss'])->name('feed.rss');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');

Auth::routes(['verify' => true]);

// OAuth Login Routes
use App\Http\Controllers\Auth\OAuthController;

Route::prefix('auth/oauth')->name('auth.social.')->group(function () {
    Route::get('{provider}/redirect', [OAuthController::class, 'redirect'])->name('redirect')->where('provider', 'google|github|facebook|discord');
    Route::get('{provider}/callback', [OAuthController::class, 'callback'])->name('callback')->where('provider', 'google|github|facebook|discord');
});

Route::middleware(['auth'])->prefix('auth/oauth')->name('auth.social.')->group(function () {
    Route::delete('{provider}/disconnect', [OAuthController::class, 'disconnect'])->name('disconnect')->where('provider', 'google|github|facebook|discord');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard.home');
// Policy pages
Route::view('/privacy', 'privacy')->name('privacy');

Route::view('/terms', 'terms')->name('terms');

Route::view('/refund-policy', 'refund')->name('refund');





// Newsletter Routes
use App\Http\Controllers\NewsletterController;

Route::prefix('newsletter')->name('newsletter.')->group(function () {
    Route::get('/verify/{token}', [NewsletterController::class, 'verify'])->name('verify');
    Route::get('/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('unsubscribe');
    Route::get('/preferences/{token}', [NewsletterController::class, 'preferences'])->name('preferences');
    Route::post('/preferences/{token}', [NewsletterController::class, 'updatePreferences'])->name('preferences.update');
    Route::get('/track/open/{engagement}', [NewsletterController::class, 'trackOpen'])->name('track.open');
    Route::get('/track/click/{engagement}', [NewsletterController::class, 'trackClick'])->name('track.click');
    Route::post('/subscribe', [NewsletterController::class, 'subscribe'])->name('subscribe');
});

// Social Sharing Routes
Route::prefix('api/social-share')->name('social-share.')->group(function () {
    Route::post('/track', [App\Http\Controllers\SocialShareController::class, 'track'])->name('track');
    Route::get('/count/{postId}', [App\Http\Controllers\SocialShareController::class, 'getShareCount'])->name('count');
    Route::get('/breakdown/{postId}', [App\Http\Controllers\SocialShareController::class, 'getPlatformBreakdown'])->name('breakdown');
    Route::get('/top-shared', [App\Http\Controllers\SocialShareController::class, 'getTopShared'])->name('top-shared');
    Route::post('/generate-utm-url', [App\Http\Controllers\SocialShareController::class, 'generateUtmUrl'])->name('generate-utm-url');
})->middleware('throttle:60,1');

// Social Media OAuth Routes
use App\Http\Controllers\Auth\SocialAuthController;

Route::middleware(['auth', 'verified'])->prefix('auth')->name('social.auth.')->group(function () {
    Route::get('/{platform}/redirect', [SocialAuthController::class, 'redirect'])->name('redirect');
    Route::get('/{platform}/callback', [SocialAuthController::class, 'callback'])->name('callback');
    Route::delete('/social-account/{accountId}', [SocialAuthController::class, 'disconnect'])->name('disconnect');
});

// Collaboration Routes
use App\Http\Controllers\CollaborationController;
    // Reader Tracking Routes
use App\Http\Controllers\ReaderTrackingController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Invitation acceptance (public link but requires auth)
    Route::get('/collaboration/invitation/accept', [CollaborationController::class, 'acceptInvitation'])->name('collaboration.invitation.accept');
    Route::post('/collaboration/invitation/{invitation}/decline', [CollaborationController::class, 'declineInvitation'])->name('collaboration.invitation.decline');

    // Collaboration notifications
    Route::get('/collaborations', [CollaborationController::class, 'notifications'])->name('collaboration.notifications');

    // Post collaboration management
    Route::prefix('posts/{post}/collaboration')->name('collaboration.')->group(function () {
        Route::get('/', [CollaborationController::class, 'show'])->name('show');
        Route::get('/history', [CollaborationController::class, 'history'])->name('history');
        Route::get('/export', [CollaborationController::class, 'exportReport'])->name('export');
    });


    Route::prefix('api/posts/{post}/readers')->name('readers.')->group(function () {
        Route::post('/activity', [ReaderTrackingController::class, 'recordActivity'])->name('activity');
        Route::get('/count', [ReaderTrackingController::class, 'getLiveCount'])->name('count');
        Route::get('/list', [ReaderTrackingController::class, 'getLiveReadersList'])->name('list');
        Route::get('/locations', [ReaderTrackingController::class, 'getReaderLocations'])->name('locations');
        Route::get('/countries', [ReaderTrackingController::class, 'getTopCountries'])->name('countries');
        Route::get('/analytics', [ReaderTrackingController::class, 'getAnalytics'])->name('analytics');
    });

    Route::post('/api/readers/cleanup', [ReaderTrackingController::class, 'cleanupInactive'])->name('readers.cleanup');
});

