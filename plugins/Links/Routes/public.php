<?php

use Illuminate\Support\Facades\Route;
use Plugins\Links\Http\Controllers\LinkController;

Route::get('{code}', [LinkController::class, 'show'])->name('show');
Route::post('{code}/unlock', [LinkController::class, 'unlock'])
    ->middleware('throttle:unlock')
    ->name('unlock');
Route::post('{code}/documents', [LinkController::class, 'storeDocuments'])->name('documents.store');
Route::get('documents/{document}/download', [LinkController::class, 'download'])->name('documents.download');
