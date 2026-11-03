<?php

use Illuminate\Support\Facades\Route;
use Plugins\Stations\Http\Controllers\StationAttachmentController;
use Plugins\Stations\Http\Controllers\StationCommentController;
use Plugins\Stations\Http\Controllers\StationController;
use Plugins\Stations\Http\Controllers\StationImportController;

Route::middleware('auth')
    ->prefix('admin/stations')
    ->name('admin.stations.')
    ->group(function (): void {
        Route::get('/', [StationController::class, 'index'])->name('index');
        Route::get('create', [StationController::class, 'create'])->name('create');
        Route::post('/', [StationController::class, 'store'])->name('store');
        Route::get('import', [StationImportController::class, 'create'])->name('import');
        Route::post('import', [StationImportController::class, 'store'])->name('import.store');
        Route::get('import/template', [StationImportController::class, 'template'])->name('import.template');
        Route::get('{station}', [StationController::class, 'show'])->name('show');
        Route::get('{station}/edit', [StationController::class, 'edit'])->name('edit');
        Route::put('{station}', [StationController::class, 'update'])->name('update');
        Route::delete('{station}', [StationController::class, 'destroy'])->name('destroy');

        Route::scopeBindings()->group(function (): void {
            Route::post('{station}/anexos', [StationAttachmentController::class, 'store'])->name('attachments.store');
            Route::get('{station}/anexos/{attachment}', [StationAttachmentController::class, 'download'])->name('attachments.download');
            Route::delete('{station}/anexos/{attachment}', [StationAttachmentController::class, 'destroy'])->name('attachments.destroy');

            Route::post('{station}/comentarios', [StationCommentController::class, 'store'])->name('comments.store');
            Route::delete('{station}/comentarios/{comment}', [StationCommentController::class, 'destroy'])->name('comments.destroy');
        });
    });
