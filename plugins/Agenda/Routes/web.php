<?php

use Illuminate\Support\Facades\Route;
use Plugins\Agenda\Http\Controllers\AgendaCalendarController;
use Plugins\Agenda\Http\Controllers\AgendaController;
use Plugins\Agenda\Http\Controllers\AgendaEventController;
use Plugins\Agenda\Http\Controllers\AgendaImportExportController;

Route::middleware('auth')
    ->prefix('admin/agenda')
    ->name('admin.agenda.')
    ->group(function (): void {
        Route::get('/', [AgendaController::class, 'index'])->name('index');
        Route::get('feed', [AgendaController::class, 'feed'])->name('feed');
        Route::get('free-busy', [AgendaController::class, 'freeBusy'])->name('free-busy');
        Route::get('export', [AgendaImportExportController::class, 'export'])->name('export');
        Route::post('import', [AgendaImportExportController::class, 'import'])->name('import');

        Route::post('events', [AgendaEventController::class, 'store'])->name('events.store');
        Route::put('events/{agendaEvent}', [AgendaEventController::class, 'update'])->name('events.update');
        Route::delete('events/{agendaEvent}', [AgendaEventController::class, 'destroy'])->name('events.destroy');
        Route::patch('events/{agendaEvent}/move', [AgendaController::class, 'move'])->name('events.move');
        Route::post('events/{agendaEvent}/duplicate', [AgendaEventController::class, 'duplicate'])->name('events.duplicate');
        Route::patch('events/{agendaEvent}/respond', [AgendaEventController::class, 'respond'])->name('events.respond');

        Route::get('calendars', [AgendaCalendarController::class, 'index'])->name('calendars.index');
        Route::post('calendars', [AgendaCalendarController::class, 'store'])->name('calendars.store');
        Route::put('calendars/{agendaCalendar}', [AgendaCalendarController::class, 'update'])->name('calendars.update');
        Route::delete('calendars/{agendaCalendar}', [AgendaCalendarController::class, 'destroy'])->name('calendars.destroy');
        Route::patch('calendars/{agendaCalendar}/visibility', [AgendaCalendarController::class, 'visibility'])->name('calendars.visibility');
        Route::patch('calendars/{agendaCalendar}/default', [AgendaCalendarController::class, 'makeDefault'])->name('calendars.default');
        Route::post('calendars/{agendaCalendar}/shares', [AgendaCalendarController::class, 'share'])->name('calendars.share');
        Route::delete('calendars/{agendaCalendar}/shares/{share}', [AgendaCalendarController::class, 'unshare'])->name('calendars.unshare');
    });
