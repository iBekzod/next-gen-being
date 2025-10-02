<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/health', HealthCheckController::class)->name('health.check');
// Public routes
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/categories/{category:slug}', [PostController::class, 'index'])->name('categories.show');
Route::get('/tags/{tag:slug}', [PostController::class, 'index'])->name('tags.show');

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

    // Post management
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post:slug}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

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
});

// Cashier webhook routes
Route::post('/stripe/webhook', '\\Laravel\\Cashier\\Http\\Controllers\\WebhookController@handleWebhook');

Route::post('/lemonsqueezy/webhook', [App\Http\Controllers\WebhookController::class, 'lemonSqueezyWebhook'])->name('lemonsqueezy.webhook')->withoutMiddleware([VerifyCsrfToken::class]);

Auth::routes(['verify' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard.home');
// Policy pages
Route::view('/privacy', 'privacy')->name('privacy');

Route::view('/terms', 'terms')->name('terms');


