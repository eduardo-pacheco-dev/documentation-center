<?php

use Illuminate\Support\Facades\Route;
use Plugins\Forms\Http\Controllers\FormFillController;

Route::get('{form}', [FormFillController::class, 'show'])->name('fill');
Route::post('{form}/rascunho', [FormFillController::class, 'autosave'])
    ->middleware('throttle:60,1')
    ->name('autosave');
Route::post('{form}/imagens/excluir', [FormFillController::class, 'destroyImage'])
    ->middleware('throttle:60,1')
    ->name('images.destroy');
Route::post('{form}/arquivos/excluir', [FormFillController::class, 'destroyFile'])
    ->middleware('throttle:60,1')
    ->name('files.destroy');
Route::post('{form}', [FormFillController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('submit');
