<?php

use Illuminate\Support\Facades\Route;
use Plugins\Templates\Http\Controllers\FormTemplateController;

Route::prefix('form-templates')->name('form-templates.')->group(function () {
    Route::get('/', [FormTemplateController::class, 'index'])->name('index');
    Route::get('create', [FormTemplateController::class, 'create'])->name('create');
    Route::post('/', [FormTemplateController::class, 'store'])->name('store');
    Route::get('{formTemplate}/edit', [FormTemplateController::class, 'edit'])->name('edit');
    Route::match(['put', 'patch'], '{formTemplate}', [FormTemplateController::class, 'update'])->name('update');
    Route::delete('{formTemplate}', [FormTemplateController::class, 'destroy'])->name('destroy');
    Route::patch('{formTemplate}/steps', [FormTemplateController::class, 'updateSteps'])->name('steps.update');
});
