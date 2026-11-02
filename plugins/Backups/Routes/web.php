<?php

use Illuminate\Support\Facades\Route;
use Plugins\Backups\Http\Controllers\BackupController;

Route::get('/', [BackupController::class, 'index'])->name('index');
Route::post('/', [BackupController::class, 'store'])->name('store');
Route::delete('{backup}', [BackupController::class, 'destroy'])->name('destroy');
Route::post('import', [BackupController::class, 'import'])->name('import');
Route::get('status', [BackupController::class, 'status'])->name('status');
Route::get('{backup}/download', [BackupController::class, 'download'])->name('download');
Route::post('{backup}/restore', [BackupController::class, 'restore'])->name('restore');
