<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemorialController;
use App\Http\Controllers\Api\PublicMemorialController;
use App\Http\Controllers\Api\UploadController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('memorials', MemorialController::class);

    Route::post('/memorials/{memorial}/publish', [MemorialController::class, 'publish']);
    Route::post('/memorials/{memorial}/unpublish', [MemorialController::class, 'unpublish']);

    Route::post('/memorial-quotes/{quote}/approve', [MemorialController::class, 'approveQuote']);
    Route::post('/memorial-quotes/{quote}/reject', [MemorialController::class, 'rejectQuote']);
    Route::delete('/memorial-quotes/{quote}', [MemorialController::class, 'deleteQuote']);

    Route::post('/upload', [UploadController::class, 'store']);

    Route::get('/me', [AuthController::class, 'me']);
});

Route::prefix('public')->group(function () {
    Route::get('/memorials/{slug}', [PublicMemorialController::class, 'show']);
    Route::post('/memorials/{memorial}/quotes', [PublicMemorialController::class, 'storePublicQuote']);
});

