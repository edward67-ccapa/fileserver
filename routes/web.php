<?php

use App\Http\Controllers\Api\ImageUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ImageUploadController::class, 'index']);

// Fallback for POST /admin/login to prevent MethodNotAllowedHttpException if Livewire JS is delayed
Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
    return redirect()->to(url('/admin/login'));
});
