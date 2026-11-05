<?php

use Illuminate\Support\Facades\Route;
use Plugins\Nokia\Http\Controllers\NokiaSiteAttachmentController;
use Plugins\Nokia\Http\Controllers\NokiaSiteController;
use Plugins\Nokia\Http\Controllers\NokiaSiteExportController;
use Plugins\Nokia\Http\Controllers\NokiaSiteImportController;

Route::middleware('auth')
    ->prefix('admin/nokia/sites')
    ->name('admin.nokia.sites.')
    ->group(function (): void {
        Route::get('/', [NokiaSiteController::class, 'index'])->name('index');
        Route::get('create', [NokiaSiteController::class, 'create'])->name('create');
        Route::post('/', [NokiaSiteController::class, 'store'])->name('store');
        Route::get('export', [NokiaSiteExportController::class, 'export'])->name('export');
        Route::get('import', [NokiaSiteImportController::class, 'create'])->name('import');
        Route::post('import', [NokiaSiteImportController::class, 'store'])->name('import.store');
        Route::get('import/template', [NokiaSiteImportController::class, 'template'])->name('import.template');
        Route::get('{site}/edit', [NokiaSiteController::class, 'edit'])->name('edit');
        Route::put('{site}', [NokiaSiteController::class, 'update'])->name('update');
        Route::delete('{site}', [NokiaSiteController::class, 'destroy'])->name('destroy');

        Route::scopeBindings()->group(function (): void {
            Route::post('{site}/attachments', [NokiaSiteAttachmentController::class, 'store'])->name('attachments.store');
            Route::get('{site}/attachments/{attachment}', [NokiaSiteAttachmentController::class, 'download'])->name('attachments.download');
            Route::delete('{site}/attachments/{attachment}', [NokiaSiteAttachmentController::class, 'destroy'])->name('attachments.destroy');
        });
    });
