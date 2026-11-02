<?php

use Illuminate\Support\Facades\Route;
use Plugins\Stations\Http\Controllers\StationAddressLookupController;
use Plugins\Stations\Http\Controllers\StationCommentController;
use Plugins\Stations\Http\Controllers\StationController;
use Plugins\Stations\Http\Controllers\StationDocumentController;
use Plugins\Stations\Http\Controllers\StationImportController;
use Plugins\Stations\Http\Controllers\StationLinkController;

Route::post('import', [StationImportController::class, 'store'])->name('import');
Route::get('import/template', [StationImportController::class, 'template'])->name('import.template');

Route::prefix('lookup')->name('lookup.')->group(function () {
    Route::get('zip', [StationAddressLookupController::class, 'zip'])->name('zip');
    Route::get('coordinates', [StationAddressLookupController::class, 'coordinates'])->name('coordinates');
});

Route::get('/', [StationController::class, 'index'])->name('index');
Route::get('create', [StationController::class, 'create'])->name('create');
Route::post('/', [StationController::class, 'store'])->name('store');

Route::get('{station}', [StationController::class, 'show'])->name('show');
Route::get('{station}/edit', [StationController::class, 'edit'])->name('edit');
Route::put('{station}', [StationController::class, 'update'])->name('update');
Route::delete('{station}', [StationController::class, 'destroy'])->name('destroy');

Route::post('{station}/documents', [StationDocumentController::class, 'store'])->name('documents.store');
Route::get('{station}/documents/{document}/download', [StationDocumentController::class, 'download'])->name('documents.download');
Route::delete('{station}/documents/{document}', [StationDocumentController::class, 'destroy'])->name('documents.destroy');

Route::post('{station}/comments', [StationCommentController::class, 'store'])->name('comments.store');
Route::delete('{station}/comments/{comment}', [StationCommentController::class, 'destroy'])->name('comments.destroy');

Route::post('{station}/links', [StationLinkController::class, 'store'])->name('links.store');
Route::put('{station}/links/{link}', [StationLinkController::class, 'update'])->name('links.update');
Route::delete('{station}/links/{link}', [StationLinkController::class, 'destroy'])->name('links.destroy');
