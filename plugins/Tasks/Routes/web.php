<?php

use Illuminate\Support\Facades\Route;
use Plugins\Tasks\Http\Controllers\TodoListController;
use Plugins\Tasks\Http\Controllers\TodoStepController;
use Plugins\Tasks\Http\Controllers\TodoTaskController;

Route::prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TodoTaskController::class, 'index'])->name('index');
    Route::get('export', [TodoTaskController::class, 'export'])->name('export');
    Route::post('/', [TodoTaskController::class, 'store'])->name('store');
    Route::put('{todoTask}', [TodoTaskController::class, 'update'])->name('update');
    Route::delete('{todoTask}', [TodoTaskController::class, 'destroy'])->name('destroy');
    Route::patch('{todoTask}/toggle', [TodoTaskController::class, 'toggle'])->name('toggle');
    Route::patch('{todoTask}/important', [TodoTaskController::class, 'toggleImportant'])->name('important');
    Route::patch('{todoTask}/my-day', [TodoTaskController::class, 'toggleMyDay'])->name('my-day');
    Route::patch('{todoTask}/move', [TodoTaskController::class, 'move'])->name('move');

    Route::prefix('{todoTask}/steps')->name('steps.')->group(function () {
        Route::post('/', [TodoStepController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '{step}', [TodoStepController::class, 'update'])->name('update');
        Route::delete('{step}', [TodoStepController::class, 'destroy'])->name('destroy');
    });
});

Route::prefix('todo-lists')->name('todo-lists.')->group(function () {
    Route::post('/', [TodoListController::class, 'store'])->name('store');
    Route::match(['put', 'patch'], '{todoList}', [TodoListController::class, 'update'])->name('update');
    Route::delete('{todoList}', [TodoListController::class, 'destroy'])->name('destroy');
});
