<?php

use Illuminate\Support\Facades\Route;
use Plugins\Colaboradores\Http\Controllers\ColaboradorController;
use Plugins\Colaboradores\Http\Controllers\ColaboradorImportController;

Route::post('import', [ColaboradorImportController::class, 'store'])->name('import');
Route::get('import/template', [ColaboradorImportController::class, 'template'])->name('import.template');
Route::get('export', [ColaboradorController::class, 'export'])->name('export');

Route::get('/', [ColaboradorController::class, 'index'])->name('index');
Route::get('create', [ColaboradorController::class, 'create'])->name('create');
Route::post('/', [ColaboradorController::class, 'store'])->name('store');

Route::get('{colaborador}', [ColaboradorController::class, 'show'])->name('show');
Route::get('{colaborador}/edit', [ColaboradorController::class, 'edit'])->name('edit');
Route::match(['put', 'patch'], '{colaborador}', [ColaboradorController::class, 'update'])->name('update');
Route::delete('{colaborador}', [ColaboradorController::class, 'destroy'])->name('destroy');
