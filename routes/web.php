<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\FolderController;
use App\Http\Controllers\Admin\ShortLinkController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LinkController;
use App\Http\Controllers\SettingsController;
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

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings', [SettingsController::class, 'show'])->name('settings');
    Route::put('settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme.update');
    Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::delete('settings/account', [SettingsController::class, 'destroy'])->name('settings.account.destroy');
});

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
    Route::post('files/move', [DocumentController::class, 'move'])->name('files.move');
    Route::get('files/{document}/preview', [DocumentController::class, 'preview'])->name('files.preview');
    Route::put('files/{document}', [DocumentController::class, 'update'])->name('files.update');
    Route::delete('files/{document}', [DocumentController::class, 'destroy'])->name('files.destroy');

    Route::post('folders', [FolderController::class, 'store'])->name('folders.store');
    Route::put('folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    Route::get('trash', [TrashController::class, 'index'])->name('trash.index');
    Route::delete('trash', [TrashController::class, 'destroy'])->name('trash.destroy');
    Route::patch('trash/documents/{document}/restore', [TrashController::class, 'restoreDocument'])
        ->withTrashed()
        ->name('trash.documents.restore');
    Route::delete('trash/documents/{document}', [TrashController::class, 'forceDestroyDocument'])
        ->withTrashed()
        ->name('trash.documents.force-destroy');
    Route::patch('trash/folders/{folder}/restore', [TrashController::class, 'restoreFolder'])
        ->withTrashed()
        ->name('trash.folders.restore');
    Route::delete('trash/folders/{folder}', [TrashController::class, 'forceDestroyFolder'])
        ->withTrashed()
        ->name('trash.folders.force-destroy');

    Route::middleware('admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
