<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Authentication routes (public)
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Password reset routes (public)
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:3,1'); // 3 attempts per minute
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Notification routes (public, but throttled)
Route::post('/notify/service-request', [NotificationController::class, 'sendServiceRequestNotification'])
    ->middleware('throttle:10,1'); // 10 attempts per minute

// Protected routes (require JWT authentication)
Route::middleware(['auth.cookie', 'auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);
});
