<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/signup', [AuthController::class, 'signup'])->middleware('throttle:login')->name('auth.signup');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::apiResource('links', ShortLinkController::class)
            ->parameters(['links' => 'shortLink']);
        Route::post('links/{shortLink}/documents', [ShortLinkController::class, 'storeDocuments'])->name('links.documents.store');

        Route::apiResource('files', DocumentController::class)
            ->parameters(['files' => 'document'])
            ->except(['show', 'update']);
    });
});
