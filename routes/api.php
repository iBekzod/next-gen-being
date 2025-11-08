<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController as ApiPostController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\WritingAssistantController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\InvoiceController;

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
    });
});
