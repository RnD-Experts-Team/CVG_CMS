<?php

use App\Http\Controllers\Auth\AuthController;
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
