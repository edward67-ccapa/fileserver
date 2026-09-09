<?php

use App\Http\Controllers\Api\CryptoController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Middleware\VerifyEncryptedToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Image Server & WebP Converter
|--------------------------------------------------------------------------
|
| Direct API endpoints for image upload, validation (max 2MB), automatic
| conversion to WebP format, and mandatory AES-256 token verification.
|
*/

// Public status/documentation endpoint
Route::get('/upload', [ImageUploadController::class, 'index']);

// Protected upload endpoint (Requires valid AES-256 encrypted token)
Route::post('/upload', [ImageUploadController::class, 'store'])
    ->middleware(VerifyEncryptedToken::class);

// Encryption & Decryption Helper Endpoints
Route::prefix('crypto')->group(function () {
    Route::post('/encrypt', [CryptoController::class, 'encrypt']);
    Route::post('/decrypt', [CryptoController::class, 'decrypt']);
    Route::post('/verify', [CryptoController::class, 'verify']);
});

// Versioned API v1 aliases
Route::prefix('v1')->group(function () {
    Route::get('/images', [ImageUploadController::class, 'index']);
    Route::post('/images/upload', [ImageUploadController::class, 'store'])
        ->middleware(VerifyEncryptedToken::class);

    Route::prefix('crypto')->group(function () {
        Route::post('/encrypt', [CryptoController::class, 'encrypt']);
        Route::post('/decrypt', [CryptoController::class, 'decrypt']);
        Route::post('/verify', [CryptoController::class, 'verify']);
    });
});
