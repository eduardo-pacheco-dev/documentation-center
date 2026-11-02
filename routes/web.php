<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\EquipmentDocumentController;
use App\Http\Controllers\Admin\ShortLinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LinkController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

/*
|--------------------------------------------------------------------------
| Public short links
|--------------------------------------------------------------------------
*/

Route::prefix('s')->name('public.short-links.')->group(function () {
    Route::get('{code}', [LinkController::class, 'show'])->name('show');
    Route::post('{code}/unlock', [LinkController::class, 'unlock'])
        ->middleware('throttle:unlock')
        ->name('unlock');
    Route::post('{code}/documents', [LinkController::class, 'storeDocuments'])->name('documents.store');
    Route::get('documents/{document}/download', [LinkController::class, 'download'])->name('documents.download');
});

/*
|--------------------------------------------------------------------------
| Public forms
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:login');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated area
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings', [SettingsController::class, 'show'])->name('settings');
    Route::put('settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme.update');
    Route::put('settings/modules', [SettingsController::class, 'updateModules'])->name('settings.modules.update');
    Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::delete('settings/account', [SettingsController::class, 'destroy'])->name('settings.account.destroy');

    Route::middleware('admin')->group(function () {
        Route::post('settings/plugins', [PluginController::class, 'store'])->name('settings.plugins.store');
        Route::patch('settings/plugins/{alias}', [PluginController::class, 'update'])->name('settings.plugins.update');
        Route::delete('settings/plugins/{alias}', [PluginController::class, 'destroy'])->name('settings.plugins.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Administration
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    | Short links
    */

    Route::resource('links', ShortLinkController::class)
        ->parameters(['links' => 'shortLink'])
        ->except(['show', 'edit']);
    Route::post('links/{shortLink}/documents', [ShortLinkController::class, 'storeDocuments'])->name('links.documents.store');
    Route::delete('links/{shortLink}/documents/{document}', [ShortLinkController::class, 'destroyDocument'])->name('links.documents.destroy');

    /*
    | Equipments
    */

    Route::resource('equipments', EquipmentController::class)
        ->parameters(['equipments' => 'equipment']);
    Route::resource('equipments.documents', EquipmentDocumentController::class)
        ->parameters(['equipments' => 'equipment'])
        ->only(['store', 'destroy']);

    /*
    | Administration only
    */

    Route::middleware('admin')->group(function () {
        Route::resource('backups', BackupController::class)->only(['index', 'store', 'destroy']);
        Route::post('backups/import', [BackupController::class, 'import'])->name('backups.import');
        Route::get('backups/status', [BackupController::class, 'status'])->name('backups.status');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');

        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    });
});
