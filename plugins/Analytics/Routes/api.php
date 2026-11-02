<?php

use Illuminate\Support\Facades\Route;
use Plugins\Analytics\Http\Controllers\AnalyticsApiController;

Route::get('/', [AnalyticsApiController::class, 'index'])->name('index');

Route::get('summary', [AnalyticsApiController::class, 'summary'])->name('summary');
Route::get('series', [AnalyticsApiController::class, 'series'])->name('series');

Route::get('fams', [AnalyticsApiController::class, 'fams'])->name('fams');
Route::get('fams/records', [AnalyticsApiController::class, 'famsRecords'])->name('fams.records');
Route::get('fams/records/{oc}/{os}/history', [AnalyticsApiController::class, 'famsHistory'])->name('fams.history');
Route::get('fams/changes', [AnalyticsApiController::class, 'famsChanges'])->name('fams.changes');

Route::get('afl', [AnalyticsApiController::class, 'afl'])->name('afl');
Route::get('afl/records', [AnalyticsApiController::class, 'aflRecords'])->name('afl.records');
Route::get('afl/records/{code}/history', [AnalyticsApiController::class, 'aflHistory'])->name('afl.history');
Route::get('afl/changes', [AnalyticsApiController::class, 'aflChanges'])->name('afl.changes');

Route::get('snapshots', [AnalyticsApiController::class, 'snapshots'])->name('snapshots');
