<?php

use Illuminate\Support\Facades\Route;
use Plugins\Projects\Http\Controllers\ProjectApiController;

Route::get('projects', [ProjectApiController::class, 'index'])->name('projects.index');
Route::get('projects/{project}', [ProjectApiController::class, 'show'])->name('projects.show');
