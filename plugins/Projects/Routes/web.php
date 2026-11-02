<?php

use Illuminate\Support\Facades\Route;
use Plugins\Projects\Http\Controllers\ProjectAssignmentController;
use Plugins\Projects\Http\Controllers\ProjectBaselineController;
use Plugins\Projects\Http\Controllers\ProjectController;
use Plugins\Projects\Http\Controllers\ProjectDependencyController;
use Plugins\Projects\Http\Controllers\ProjectGanttController;
use Plugins\Projects\Http\Controllers\ProjectMemberController;
use Plugins\Projects\Http\Controllers\ProjectResourceController;
use Plugins\Projects\Http\Controllers\ProjectScheduleController;
use Plugins\Projects\Http\Controllers\ProjectTaskController;

Route::get('/', [ProjectController::class, 'index'])->name('index');
Route::get('create', [ProjectController::class, 'create'])->name('create');
Route::post('/', [ProjectController::class, 'store'])->name('store');
Route::get('{project}', [ProjectController::class, 'show'])->name('show');
Route::get('{project}/edit', [ProjectController::class, 'edit'])->name('edit');
Route::match(['put', 'patch'], '{project}', [ProjectController::class, 'update'])->name('update');
Route::delete('{project}', [ProjectController::class, 'destroy'])->name('destroy');

Route::post('{project}/tasks', [ProjectTaskController::class, 'store'])->name('tasks.store');
Route::match(['put', 'patch'], '{project}/tasks/{task}', [ProjectTaskController::class, 'update'])->name('tasks.update');
Route::delete('{project}/tasks/{task}', [ProjectTaskController::class, 'destroy'])->name('tasks.destroy');
Route::patch('{project}/tasks/{task}/progress', [ProjectTaskController::class, 'progress'])->name('tasks.progress');

Route::post('{project}/dependencies', [ProjectDependencyController::class, 'store'])->name('dependencies.store');
Route::delete('{project}/dependencies/{dependency}', [ProjectDependencyController::class, 'destroy'])->name('dependencies.destroy');

Route::post('{project}/resources', [ProjectResourceController::class, 'store'])->name('resources.store');
Route::match(['put', 'patch'], '{project}/resources/{resource}', [ProjectResourceController::class, 'update'])->name('resources.update');
Route::delete('{project}/resources/{resource}', [ProjectResourceController::class, 'destroy'])->name('resources.destroy');

Route::post('{project}/assignments', [ProjectAssignmentController::class, 'store'])->name('assignments.store');
Route::delete('{project}/assignments/{assignment}', [ProjectAssignmentController::class, 'destroy'])->name('assignments.destroy');

Route::post('{project}/members', [ProjectMemberController::class, 'store'])->name('members.store');
Route::match(['put', 'patch'], '{project}/members/{member}', [ProjectMemberController::class, 'update'])->name('members.update');
Route::delete('{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('members.destroy');

Route::post('{project}/schedule', [ProjectScheduleController::class, 'store'])->name('schedule.store');
Route::post('{project}/level', [ProjectScheduleController::class, 'level'])->name('level.store');

Route::post('{project}/baselines', [ProjectBaselineController::class, 'store'])->name('baselines.store');
Route::post('{project}/baselines/{baseline}/restore', [ProjectBaselineController::class, 'restore'])->name('baselines.restore');

Route::get('{project}/gantt', [ProjectGanttController::class, 'data'])->name('gantt.data');
Route::put('{project}/gantt/tasks/{task}', [ProjectGanttController::class, 'move'])->name('gantt.move');
