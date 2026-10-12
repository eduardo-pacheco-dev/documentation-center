<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ShortLinkController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\UploadController;
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

        Route::get('sites', [SiteController::class, 'index'])->name('sites.index');
        Route::get('sites/{site}', [SiteController::class, 'show'])->name('sites.show');

        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

        Route::get('forms', [FormController::class, 'index'])->name('forms.index');
        Route::get('forms/{form}', [FormController::class, 'show'])->name('forms.show');
        Route::patch('forms/{form}', [FormController::class, 'update'])->name('forms.update');

        Route::post('uploads', [UploadController::class, 'store'])->name('uploads.store');
    });
});
