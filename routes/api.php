<?php

use App\Http\Controllers\Api\ImageUploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Image Server & WebP Converter
|--------------------------------------------------------------------------
|
| Direct API endpoints for image upload, validation (max 2MB), automatic
| conversion to WebP format, and organized folder structure per company/description.
|
*/

// Primary upload endpoints (compatible with Postman form-data)
Route::get('/upload', [ImageUploadController::class, 'index']);
Route::post('/upload', [ImageUploadController::class, 'store']);

// Versioned API v1 aliases
Route::prefix('v1')->group(function () {
    Route::get('/images', [ImageUploadController::class, 'index']);
    Route::post('/images/upload', [ImageUploadController::class, 'store']);
});
