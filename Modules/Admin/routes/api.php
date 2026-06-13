<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\Api\AuthController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

// Public authentication routes
Route::prefix('v1/admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes requiring authentication
Route::middleware(['auth:api'])->prefix('v1/admin')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    
    Route::apiResource('admin', AdminController::class)->names('admin');
});
