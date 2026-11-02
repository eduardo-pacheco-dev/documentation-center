<?php

use Illuminate\Support\Facades\Route;
use Plugins\Links\Http\Controllers\ShortLinkController;

Route::prefix('links')->name('links.')->group(function () {
    Route::get('/', [ShortLinkController::class, 'index'])->name('index');
    Route::get('create', [ShortLinkController::class, 'create'])->name('create');
    Route::post('/', [ShortLinkController::class, 'store'])->name('store');
    Route::match(['put', 'patch'], '{shortLink}', [ShortLinkController::class, 'update'])->name('update');
    Route::delete('{shortLink}', [ShortLinkController::class, 'destroy'])->name('destroy');
    Route::post('{shortLink}/documents', [ShortLinkController::class, 'storeDocuments'])->name('documents.store');
    Route::delete('{shortLink}/documents/{document}', [ShortLinkController::class, 'destroyDocument'])->name('documents.destroy');
});
