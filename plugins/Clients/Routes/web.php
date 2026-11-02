<?php

use Illuminate\Support\Facades\Route;
use Plugins\Clients\Http\Controllers\ClientContactController;
use Plugins\Clients\Http\Controllers\ClientController;
use Plugins\Clients\Http\Controllers\ClientDocumentController;
use Plugins\Clients\Http\Controllers\ClientImportController;

Route::post('import', [ClientImportController::class, 'store'])->name('import');
Route::get('import/template', [ClientImportController::class, 'template'])->name('import.template');
Route::get('export', [ClientController::class, 'export'])->name('export');

Route::get('/', [ClientController::class, 'index'])->name('index');
Route::get('create', [ClientController::class, 'create'])->name('create');
Route::post('/', [ClientController::class, 'store'])->name('store');

Route::get('{client}', [ClientController::class, 'show'])->name('show');
Route::get('{client}/edit', [ClientController::class, 'edit'])->name('edit');
Route::match(['put', 'patch'], '{client}', [ClientController::class, 'update'])->name('update');
Route::delete('{client}', [ClientController::class, 'destroy'])->name('destroy');

Route::post('{client}/contacts', [ClientContactController::class, 'store'])->name('contacts.store');
Route::match(['put', 'patch'], '{client}/contacts/{contact}', [ClientContactController::class, 'update'])->name('contacts.update');
Route::delete('{client}/contacts/{contact}', [ClientContactController::class, 'destroy'])->name('contacts.destroy');

Route::post('{client}/documents', [ClientDocumentController::class, 'store'])->name('documents.store');
Route::delete('{client}/documents/{document}', [ClientDocumentController::class, 'destroy'])->name('documents.destroy');
