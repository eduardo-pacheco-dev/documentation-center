<?php

use Illuminate\Support\Facades\Route;
use Plugins\Links\Http\Controllers\ShortLinkApiController;

Route::apiResource('links', ShortLinkApiController::class)
    ->parameters(['links' => 'shortLink']);
Route::post('links/{shortLink}/documents', [ShortLinkApiController::class, 'storeDocuments'])->name('links.documents.store');
