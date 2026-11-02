<?php

use Illuminate\Support\Facades\Route;
use Plugins\Equipments\Http\Controllers\EquipmentController;
use Plugins\Equipments\Http\Controllers\EquipmentDocumentController;

Route::get('/', [EquipmentController::class, 'index'])->name('index');
Route::get('create', [EquipmentController::class, 'create'])->name('create');
Route::post('/', [EquipmentController::class, 'store'])->name('store');
Route::get('{equipment}', [EquipmentController::class, 'show'])->name('show');
Route::get('{equipment}/edit', [EquipmentController::class, 'edit'])->name('edit');
Route::match(['put', 'patch'], '{equipment}', [EquipmentController::class, 'update'])->name('update');
Route::delete('{equipment}', [EquipmentController::class, 'destroy'])->name('destroy');

Route::post('{equipment}/documents', [EquipmentDocumentController::class, 'store'])->name('documents.store');
Route::delete('{equipment}/documents/{document}', [EquipmentDocumentController::class, 'destroy'])->name('documents.destroy');
