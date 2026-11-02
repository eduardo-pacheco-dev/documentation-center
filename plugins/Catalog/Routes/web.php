<?php

use Illuminate\Support\Facades\Route;
use Plugins\Catalog\Http\Controllers\CatalogItemController;

Route::get('/', [CatalogItemController::class, 'index'])->name('index');
Route::get('create', [CatalogItemController::class, 'create'])->name('create');
Route::post('/', [CatalogItemController::class, 'store'])->name('store');

Route::get('{catalogItem}', [CatalogItemController::class, 'show'])->name('show');
Route::get('{catalogItem}/edit', [CatalogItemController::class, 'edit'])->name('edit');
Route::match(['put', 'patch'], '{catalogItem}', [CatalogItemController::class, 'update'])->name('update');
Route::delete('{catalogItem}', [CatalogItemController::class, 'destroy'])->name('destroy');
