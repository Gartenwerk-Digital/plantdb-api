<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FamilyController;
use App\Http\Controllers\Api\V1\GenusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Routes for API version 1.
|
*/

Route::middleware('api.version:v1')->group(function (): void {
    // Health / liveness probe — public, no auth, no throttle
    Route::get('ping', fn () => response()->json(['status' => 'ok']))
        ->name('api.v1.ping');

    // Public routes with auth rate limiter (5/min - brute force protection)
    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register'])->name('api.v1.register');
        Route::post('login', [AuthController::class, 'login'])->name('api.v1.login');
    });

    // Public read endpoints (taxonomy) - 60/min per IP
    Route::middleware('throttle:api')->group(function (): void {
        Route::get('families', [FamilyController::class, 'index'])->name('api.v1.families.index');
        Route::get('families/{slug}', [FamilyController::class, 'show'])->name('api.v1.families.show');
        Route::get('genera', [GenusController::class, 'index'])->name('api.v1.genera.index');
        Route::get('genera/{slug}', [GenusController::class, 'show'])->name('api.v1.genera.show');
    });

    // Protected routes with authenticated rate limiter (120/min)
    Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('me', [AuthController::class, 'me'])->name('api.v1.me');

        // Email verification
        Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware('signed')
            ->name('verification.verify');
        Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });

    // Password reset routes (public with rate limiting)
    Route::middleware('throttle:6,1')->group(function (): void {
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
            ->name('password.email');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])
            ->name('password.reset');
    });
});
