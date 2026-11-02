<?php

use Illuminate\Support\Facades\Route;
use Plugins\Files\Http\Controllers\DocumentController;
use Plugins\Files\Http\Controllers\FolderController;
use Plugins\Files\Http\Controllers\TrashController;

Route::prefix('files')->name('files.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::post('/', [DocumentController::class, 'store'])->name('store');
    Route::post('generate-link', [DocumentController::class, 'generateLink'])->name('generate-link');
    Route::post('move', [DocumentController::class, 'move'])->name('move');
    Route::match(['put', 'patch'], '{document}', [DocumentController::class, 'update'])->name('update');
    Route::delete('{document}', [DocumentController::class, 'destroy'])->name('destroy');
    Route::get('{document}/preview', [DocumentController::class, 'preview'])->name('preview');
    Route::get('{document}/download', [DocumentController::class, 'download'])->name('download');
});

Route::prefix('folders')->name('folders.')->group(function () {
    Route::post('/', [FolderController::class, 'store'])->name('store');
    Route::match(['put', 'patch'], '{folder}', [FolderController::class, 'update'])->name('update');
    Route::delete('{folder}', [FolderController::class, 'destroy'])->name('destroy');
});

Route::prefix('trash')->name('trash.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.files.index', ['view' => 'trash']))->name('index');
    Route::delete('/', [TrashController::class, 'destroy'])->name('destroy');
    Route::patch('documents/{document}/restore', [TrashController::class, 'restoreDocument'])
        ->withTrashed()
        ->name('documents.restore');
    Route::delete('documents/{document}', [TrashController::class, 'forceDestroyDocument'])
        ->withTrashed()
        ->name('documents.force-destroy');
    Route::patch('folders/{folder}/restore', [TrashController::class, 'restoreFolder'])
        ->withTrashed()
        ->name('folders.restore');
    Route::delete('folders/{folder}', [TrashController::class, 'forceDestroyFolder'])
        ->withTrashed()
        ->name('folders.force-destroy');
});
