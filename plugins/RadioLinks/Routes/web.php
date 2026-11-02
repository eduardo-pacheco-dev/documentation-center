<?php

use Illuminate\Support\Facades\Route;
use Plugins\RadioLinks\Http\Controllers\RadioLinkCommentController;
use Plugins\RadioLinks\Http\Controllers\RadioLinkController;
use Plugins\RadioLinks\Http\Controllers\RadioLinkDocumentController;

Route::get('/', [RadioLinkController::class, 'index'])->name('index');
Route::post('/', [RadioLinkController::class, 'store'])->name('store');

Route::get('{radioLink}', [RadioLinkController::class, 'show'])->name('show');
Route::get('{radioLink}/edit', [RadioLinkController::class, 'edit'])->name('edit');
Route::match(['put', 'patch'], '{radioLink}', [RadioLinkController::class, 'update'])->name('update');
Route::delete('{radioLink}', [RadioLinkController::class, 'destroy'])->name('destroy');

Route::post('{radioLink}/documents', [RadioLinkDocumentController::class, 'store'])->name('documents.store');
Route::delete('{radioLink}/documents/{document}', [RadioLinkDocumentController::class, 'destroy'])->name('documents.destroy');

Route::post('{radioLink}/comments', [RadioLinkCommentController::class, 'store'])->name('comments.store');
Route::delete('{radioLink}/comments/{comment}', [RadioLinkCommentController::class, 'destroy'])->name('comments.destroy');
