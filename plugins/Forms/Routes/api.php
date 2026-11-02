<?php

use Illuminate\Support\Facades\Route;
use Plugins\Forms\Http\Controllers\FormApiController;

Route::get('forms', [FormApiController::class, 'index'])->name('forms.index');
Route::get('forms/{form}', [FormApiController::class, 'show'])->name('forms.show');
Route::patch('forms/{form}', [FormApiController::class, 'update'])->name('forms.update');
