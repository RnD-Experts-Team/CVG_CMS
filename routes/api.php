<?php

use App\Http\Controllers\AdminCMS\AdminCMSController;
use App\Http\Controllers\AdminCMS\CategoryController;
use App\Http\Controllers\AdminCMS\ProjectController;
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

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    // Site Metadata
    Route::controller(AdminCMSController::class)->group(function () {
        Route::get('/site-metadata', 'getSiteMetadata');
        Route::put('/site-metadata', 'updateSiteMetadata');
    });

    // Footer
    Route::get('/footer', [AdminCMSController::class, 'getFooter']);
    Route::put('/footer', [AdminCMSController::class, 'updateFooter']);

    // Hero Section
    Route::get('/hero', [AdminCMSController::class, 'getHero']);
    Route::put('/hero', [AdminCMSController::class, 'updateHero']);

    // Projects Section
    Route::get('/projects-section', [AdminCMSController::class, 'getProjectsSection']);
    Route::put('/projects-section', [AdminCMSController::class, 'updateProjectsSection']);

    // Projects CRUD
    Route::get('/projects', [ProjectController::class, 'getProjects']);
    Route::post('/projects', [ProjectController::class, 'createProject']);
    Route::get('/projects/{id}', [ProjectController::class, 'getProjectById']);
    Route::put('/projects/{id}', [ProjectController::class, 'updateProject']);
    Route::delete('/projects/{id}', [ProjectController::class, 'deleteProject']);

    // Categories CRUD
    Route::get('/categories', [CategoryController::class, 'getAllCategories']);
    Route::post('/categories', [CategoryController::class, 'createCategory']);
    Route::put('/categories/{id}', [CategoryController::class, 'updateCategory']);
    Route::get('/categories/{id}', [CategoryController::class, 'getCategoryById']);
    Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategory']);
});
