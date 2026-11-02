<?php

use Illuminate\Support\Facades\Route;
use Plugins\Analytics\Http\Controllers\AnalyticsController;

Route::get('/', [AnalyticsController::class, 'index'])->name('index');
Route::get('export', [AnalyticsController::class, 'export'])->name('export');
Route::post('snapshot', [AnalyticsController::class, 'snapshot'])->name('snapshot');
Route::post('records/import', [AnalyticsController::class, 'importRecords'])->name('records.import');
Route::get('records/template', [AnalyticsController::class, 'recordsTemplate'])->name('records.template');
