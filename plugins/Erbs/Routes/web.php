<?php

use Illuminate\Support\Facades\Route;
use Plugins\Erbs\Http\Controllers\ErbAddressLookupController;
use Plugins\Erbs\Http\Controllers\ErbCommentController;
use Plugins\Erbs\Http\Controllers\ErbController;
use Plugins\Erbs\Http\Controllers\ErbDocumentController;
use Plugins\Erbs\Http\Controllers\ErbImportController;
use Plugins\Erbs\Http\Controllers\ErbRelationController;

Route::post('import', [ErbImportController::class, 'store'])->name('import');
Route::get('import/template', [ErbImportController::class, 'template'])->name('import.template');

Route::prefix('lookup')->name('lookup.')->group(function () {
    Route::get('zip', [ErbAddressLookupController::class, 'zip'])->name('zip');
    Route::get('coordinates', [ErbAddressLookupController::class, 'coordinates'])->name('coordinates');
});

Route::get('/', [ErbController::class, 'index'])->name('index');
Route::get('create', [ErbController::class, 'create'])->name('create');
Route::post('/', [ErbController::class, 'store'])->name('store');

Route::get('{erb}', [ErbController::class, 'show'])->name('show');
Route::get('{erb}/edit', [ErbController::class, 'edit'])->name('edit');
Route::match(['put', 'patch'], '{erb}', [ErbController::class, 'update'])->name('update');
Route::delete('{erb}', [ErbController::class, 'destroy'])->name('destroy');

Route::post('{erb}/documents', [ErbDocumentController::class, 'store'])->name('documents.store');
Route::delete('{erb}/documents/{document}', [ErbDocumentController::class, 'destroy'])->name('documents.destroy');

Route::post('{erb}/comments', [ErbCommentController::class, 'store'])->name('comments.store');
Route::delete('{erb}/comments/{comment}', [ErbCommentController::class, 'destroy'])->name('comments.destroy');

Route::prefix('{erb}/relations')->name('relations.')->group(function () {
    Route::put('projects', [ErbRelationController::class, 'projects'])->name('projects');
    Route::put('work-orders', [ErbRelationController::class, 'workOrders'])->name('work-orders');
    Route::put('radio-links', [ErbRelationController::class, 'radioLinks'])->name('radio-links');
});
