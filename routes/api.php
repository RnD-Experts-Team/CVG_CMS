<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicCMS\PublicCMSController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    // Public route for user login
    Route::post('/login', [AuthController::class, 'login']);

    // Protected route for user logout (requires authentication via Sanctum)
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Public endpoints (no authentication required)
Route::controller(PublicCMSController::class)->group(function () {
    Route::get('/home', 'getHomeData');
    Route::get('/categories', 'getCategories');
    Route::get('/projects', 'getProjects');
    Route::get('/projects/category/{slug}', 'getProjectsByCategory');
    Route::get('/projects/{slug}', 'getProjectBySlug');
    Route::get('/services', 'getServices');
    Route::post('/contact-submissions', 'submitContactForm');
});
