<?php

use Illuminate\Support\Facades\Route;
use Plugins\FollowUp\Http\Controllers\FollowUpController;
use Plugins\FollowUp\Http\Controllers\FollowUpEmailController;
use Plugins\FollowUp\Http\Controllers\FollowUpExportController;
use Plugins\FollowUp\Http\Controllers\FollowUpSettingsController;
use Plugins\FollowUp\Http\Controllers\FollowUpUpdateController;

Route::middleware('auth')
    ->prefix('admin/follow-ups')
    ->name('admin.follow-ups.')
    ->group(function (): void {
        Route::get('/', [FollowUpController::class, 'index'])->name('index');
        Route::get('create', [FollowUpController::class, 'create'])->name('create');
        Route::post('/', [FollowUpController::class, 'store'])->name('store');
        Route::get('export', [FollowUpExportController::class, 'export'])->name('export');
        Route::post('email', [FollowUpEmailController::class, 'sendTable'])->name('email.table');

        Route::middleware('admin')->group(function (): void {
            Route::get('settings', [FollowUpSettingsController::class, 'edit'])->name('settings');
            Route::put('settings', [FollowUpSettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/test-email', [FollowUpSettingsController::class, 'sendTestEmail'])->name('settings.test-email');
        });

        Route::get('{followUp}', [FollowUpController::class, 'show'])->name('show');
        Route::get('{followUp}/export', [FollowUpExportController::class, 'exportOne'])->name('export-one');
        Route::post('{followUp}/email', [FollowUpEmailController::class, 'send'])->name('email.send');
        Route::get('{followUp}/edit', [FollowUpController::class, 'edit'])->name('edit');
        Route::put('{followUp}', [FollowUpController::class, 'update'])->name('update');
        Route::delete('{followUp}', [FollowUpController::class, 'destroy'])->name('destroy');

        Route::scopeBindings()->group(function (): void {
            Route::post('{followUp}/updates', [FollowUpUpdateController::class, 'store'])->name('updates.store');
            Route::delete('{followUp}/updates/{update}', [FollowUpUpdateController::class, 'destroy'])->name('updates.destroy');
        });
    });
