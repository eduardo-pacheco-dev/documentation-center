<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\ShortLinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Public\LinkController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('s/{code}', [LinkController::class, 'show'])->name('public.short-links.show');
Route::post('s/{code}/unlock', [LinkController::class, 'unlock'])
    ->middleware('throttle:unlock')
    ->name('public.short-links.unlock');
Route::post('s/{code}/documents', [LinkController::class, 'storeDocuments'])->name('public.short-links.documents.store');
Route::get('s/documents/{document}/download', [LinkController::class, 'download'])->name('public.short-links.documents.download');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:login');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('links', ShortLinkController::class)
        ->parameters(['links' => 'shortLink'])
        ->except(['show']);
    Route::post('links/{shortLink}/documents', [ShortLinkController::class, 'storeDocuments'])->name('links.documents.store');
    Route::delete('links/{shortLink}/documents/{document}', [ShortLinkController::class, 'destroyDocument'])->name('links.documents.destroy');

    Route::get('files', [DocumentController::class, 'index'])->name('files.index');
    Route::post('files', [DocumentController::class, 'store'])->name('files.store');
    Route::post('files/generate-link', [DocumentController::class, 'generateLink'])->name('files.generate-link');
    Route::delete('files/{document}', [DocumentController::class, 'destroy'])->name('files.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
