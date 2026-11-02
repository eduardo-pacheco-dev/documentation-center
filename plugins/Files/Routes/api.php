<?php

use Illuminate\Support\Facades\Route;
use Plugins\Files\Http\Controllers\DocumentApiController;

Route::apiResource('files', DocumentApiController::class)
    ->parameters(['files' => 'document'])
    ->except(['show', 'update']);
