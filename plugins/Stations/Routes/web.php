<?php

use Illuminate\Support\Facades\Route;
use Plugins\Stations\Http\Controllers\StationController;

Route::middleware('auth')
    ->prefix('admin/stations')
    ->name('admin.stations.')
    ->group(function (): void {
        Route::get('/', [StationController::class, 'index'])->name('index');
        Route::get('create', [StationController::class, 'create'])->name('create');
        Route::post('/', [StationController::class, 'store'])->name('store');
        Route::get('{station}', [StationController::class, 'show'])->name('show');
        Route::get('{station}/edit', [StationController::class, 'edit'])->name('edit');
        Route::put('{station}', [StationController::class, 'update'])->name('update');
        Route::delete('{station}', [StationController::class, 'destroy'])->name('destroy');
    });
