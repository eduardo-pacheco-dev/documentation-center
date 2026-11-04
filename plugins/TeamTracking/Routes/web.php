<?php

use Illuminate\Support\Facades\Route;
use Plugins\TeamTracking\Http\Controllers\TeamCheckInController;
use Plugins\TeamTracking\Http\Controllers\TeamController;
use Plugins\TeamTracking\Http\Controllers\TeamMemberController;

Route::middleware('auth')
    ->prefix('admin/teams')
    ->name('admin.teams.')
    ->group(function (): void {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::get('create', [TeamController::class, 'create'])->name('create');
        Route::post('/', [TeamController::class, 'store'])->name('store');
        Route::get('{team}', [TeamController::class, 'show'])->name('show');
        Route::get('{team}/edit', [TeamController::class, 'edit'])->name('edit');
        Route::put('{team}', [TeamController::class, 'update'])->name('update');
        Route::delete('{team}', [TeamController::class, 'destroy'])->name('destroy');

        Route::post('{team}/members', [TeamMemberController::class, 'store'])->name('members.store');
        Route::delete('{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('members.destroy');

        Route::scopeBindings()->group(function (): void {
            Route::post('{team}/check-ins', [TeamCheckInController::class, 'store'])->name('check-ins.store');
            Route::get('{team}/check-ins/{checkIn}/edit', [TeamCheckInController::class, 'edit'])->name('check-ins.edit');
            Route::put('{team}/check-ins/{checkIn}', [TeamCheckInController::class, 'update'])->name('check-ins.update');
            Route::delete('{team}/check-ins/{checkIn}', [TeamCheckInController::class, 'destroy'])->name('check-ins.destroy');
        });
    });
