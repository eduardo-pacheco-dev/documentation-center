<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/signup', [AuthController::class, 'signup'])->middleware('throttle:login')->name('auth.signup');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::get('sites', [SiteController::class, 'index'])->name('sites.index');
        Route::get('sites/{site}', [SiteController::class, 'show'])->name('sites.show');

        Route::post('uploads', [UploadController::class, 'store'])->name('uploads.store');
    });
});
