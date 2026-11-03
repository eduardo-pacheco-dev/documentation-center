<?php

use Illuminate\Support\Facades\Route;
use Plugins\Analytics\Http\Controllers\AnalyticsController;

Route::get('/', [AnalyticsController::class, 'index'])->name('index');
Route::get('export', [AnalyticsController::class, 'export'])->name('export');
Route::post('snapshot', [AnalyticsController::class, 'snapshot'])->name('snapshot');
Route::post('records/import', [AnalyticsController::class, 'importRecords'])->name('records.import');
Route::get('records/template', [AnalyticsController::class, 'recordsTemplate'])->name('records.template');
Route::post('afl/import', [AnalyticsController::class, 'importAfl'])->name('afl.import');
Route::get('afl/template', [AnalyticsController::class, 'aflTemplate'])->name('afl.template');
Route::post('nokia/import', [AnalyticsController::class, 'importNokia'])->name('nokia.import');
Route::get('nokia/template', [AnalyticsController::class, 'nokiaTemplate'])->name('nokia.template');
Route::get('imports/status', [AnalyticsController::class, 'importsStatus'])->name('imports.status');
Route::post('imports/{import}/cancel', [AnalyticsController::class, 'cancelImport'])
    ->whereNumber('import')
    ->name('imports.cancel');
