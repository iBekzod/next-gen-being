<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\BloggerProfileController;
use Illuminate\Support\Facades\Auth;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
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
Route::get('/categories/{category:slug}', [PostController::class, 'index'])->name('categories.show');
Route::get('/tags/{tag:slug}', [PostController::class, 'index'])->name('tags.show');

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
    Route::get('/dashboard/settings', [UserDashboardController::class, 'settings'])->name('dashboard.settings');

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

