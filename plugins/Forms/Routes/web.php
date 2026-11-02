<?php

use Illuminate\Support\Facades\Route;
use Plugins\Forms\Http\Controllers\FormController;
use Plugins\Forms\Http\Controllers\FormEvaluationController;
use Plugins\Forms\Http\Controllers\FormSettingsController;

Route::get('forms/settings', [FormSettingsController::class, 'edit'])->name('forms.settings.edit');
Route::put('forms/settings', [FormSettingsController::class, 'update'])->name('forms.settings.update');
Route::get('forms/settings/export', [FormSettingsController::class, 'export'])->name('forms.settings.export');
Route::post('forms/settings/import', [FormSettingsController::class, 'import'])->name('forms.settings.import');

Route::resource('forms', FormController::class);
Route::patch('forms/{form}/steps', [FormController::class, 'updateSteps'])->name('forms.steps.update');

Route::prefix('forms/{form}/avaliacao')->name('forms.')->group(function () {
    Route::get('/', [FormEvaluationController::class, 'create'])->name('evaluate');
    Route::post('/', [FormEvaluationController::class, 'store'])->name('evaluations.store');
    Route::post('auto-save', [FormEvaluationController::class, 'autosave'])->name('evaluations.autosave');
});
