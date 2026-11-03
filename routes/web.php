<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DevDocController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

/*
|--------------------------------------------------------------------------
| Public short links
|--------------------------------------------------------------------------
*/

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
    | Administration only
    */

    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        Route::resource('dev-docs', DevDocController::class)
            ->parameters(['dev-docs' => 'devDoc']);
    });
});
