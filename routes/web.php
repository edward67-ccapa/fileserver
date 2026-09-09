<?php

use App\Http\Controllers\Api\ImageUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ImageUploadController::class, 'index']);
