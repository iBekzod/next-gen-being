<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController as ApiPostController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\UserController as ApiUserController;

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
    });
});
