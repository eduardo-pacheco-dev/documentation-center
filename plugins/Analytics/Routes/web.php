<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Plugins\Analytics\Http\Controllers\AnalyticsController;
use Plugins\Analytics\Http\Controllers\AnalyticsImportController;

Route::middleware(['auth', 'admin'])
    ->prefix('admin/analytics')
    ->name('admin.analytics.')
    ->group(function (): void {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('import', [AnalyticsImportController::class, 'create'])->name('import');
        Route::get('import/template', [AnalyticsImportController::class, 'template'])->name('import.template');
        Route::post('import', [AnalyticsImportController::class, 'store'])->name('import.store');
    });
