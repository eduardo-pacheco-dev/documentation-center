<?php

use Illuminate\Support\Facades\Route;
use Plugins\Nokia\Http\Controllers\NokiaSiteController;

Route::middleware('auth')
    ->prefix('admin/nokia/sites')
    ->name('admin.nokia.sites.')
    ->group(function (): void {
        Route::get('/', [NokiaSiteController::class, 'index'])->name('index');
        Route::get('create', [NokiaSiteController::class, 'create'])->name('create');
        Route::post('/', [NokiaSiteController::class, 'store'])->name('store');
        Route::get('{site}/edit', [NokiaSiteController::class, 'edit'])->name('edit');
        Route::put('{site}', [NokiaSiteController::class, 'update'])->name('update');
        Route::delete('{site}', [NokiaSiteController::class, 'destroy'])->name('destroy');
    });
