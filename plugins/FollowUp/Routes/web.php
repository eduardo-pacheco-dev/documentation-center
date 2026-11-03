<?php

use Illuminate\Support\Facades\Route;
use Plugins\FollowUp\Http\Controllers\FollowUpController;
use Plugins\FollowUp\Http\Controllers\FollowUpExportController;
use Plugins\FollowUp\Http\Controllers\FollowUpUpdateController;

Route::middleware('auth')
    ->prefix('admin/follow-ups')
    ->name('admin.follow-ups.')
    ->group(function (): void {
        Route::get('/', [FollowUpController::class, 'index'])->name('index');
        Route::get('create', [FollowUpController::class, 'create'])->name('create');
        Route::post('/', [FollowUpController::class, 'store'])->name('store');
        Route::get('export', [FollowUpExportController::class, 'export'])->name('export');
        Route::get('{followUp}', [FollowUpController::class, 'show'])->name('show');
        Route::get('{followUp}/export', [FollowUpExportController::class, 'exportOne'])->name('export-one');
        Route::get('{followUp}/edit', [FollowUpController::class, 'edit'])->name('edit');
        Route::put('{followUp}', [FollowUpController::class, 'update'])->name('update');
        Route::delete('{followUp}', [FollowUpController::class, 'destroy'])->name('destroy');

        Route::scopeBindings()->group(function (): void {
            Route::post('{followUp}/updates', [FollowUpUpdateController::class, 'store'])->name('updates.store');
            Route::delete('{followUp}/updates/{update}', [FollowUpUpdateController::class, 'destroy'])->name('updates.destroy');
        });
    });
