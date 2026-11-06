<?php

use Illuminate\Support\Facades\Route;
use Plugins\Nokia\Http\Controllers\NokiaRomController;
use Plugins\Nokia\Http\Controllers\NokiaSiteAttachmentController;
use Plugins\Nokia\Http\Controllers\NokiaSiteController;
use Plugins\Nokia\Http\Controllers\NokiaSiteExportController;
use Plugins\Nokia\Http\Controllers\NokiaSiteImportController;
use Plugins\Nokia\Http\Controllers\NokiaTaskController;

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
        Route::get('{site}', [NokiaSiteController::class, 'show'])->name('show');
        Route::get('{site}/edit', [NokiaSiteController::class, 'edit'])->name('edit');
        Route::put('{site}', [NokiaSiteController::class, 'update'])->name('update');
        Route::delete('{site}', [NokiaSiteController::class, 'destroy'])->name('destroy');
        Route::post('{site}/tasks/{task}', [NokiaSiteController::class, 'toggleTask'])->name('tasks.toggle');
        Route::put('{site}/checklist/{field}', [NokiaSiteController::class, 'updateChecklist'])
            ->where('field', '[a-z_]+')
            ->name('checklist.update');
        Route::put('{site}/dates', [NokiaSiteController::class, 'updateDates'])->name('dates.update');
        Route::put('{site}/interruption', [NokiaSiteController::class, 'updateInterruption'])->name('interruption.update');
        Route::delete('{site}/interruption', [NokiaSiteController::class, 'clearInterruption'])->name('interruption.destroy');
        Route::put('{site}/rejection', [NokiaSiteController::class, 'updateRejection'])->name('rejection.update');
        Route::delete('{site}/rejection', [NokiaSiteController::class, 'clearRejection'])->name('rejection.destroy');

        Route::scopeBindings()->group(function (): void {
            Route::post('{site}/attachments', [NokiaSiteAttachmentController::class, 'store'])->name('attachments.store');
            Route::get('{site}/attachments/{attachment}', [NokiaSiteAttachmentController::class, 'download'])->name('attachments.download');
            Route::delete('{site}/attachments/{attachment}', [NokiaSiteAttachmentController::class, 'destroy'])->name('attachments.destroy');
        });
    });

Route::middleware(['auth', 'admin'])
    ->prefix('admin/nokia/settings')
    ->name('admin.nokia.settings.')
    ->group(function (): void {
        Route::get('/', [NokiaRomController::class, 'index'])->name('index');
        Route::post('roms', [NokiaRomController::class, 'store'])->name('roms.store');
        Route::get('roms/{rom}/edit', [NokiaRomController::class, 'edit'])->name('roms.edit');
        Route::put('roms/{rom}', [NokiaRomController::class, 'update'])->name('roms.update');
        Route::delete('roms/{rom}', [NokiaRomController::class, 'destroy'])->name('roms.destroy');

        Route::post('tasks', [NokiaTaskController::class, 'store'])->name('tasks.store');
        Route::put('tasks/{task}', [NokiaTaskController::class, 'update'])->name('tasks.update');
        Route::delete('tasks/{task}', [NokiaTaskController::class, 'destroy'])->name('tasks.destroy');
    });
