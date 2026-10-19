<?php

use App\Http\Controllers\Admin\AgendaCalendarController;
use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\AgendaEventController;
use App\Http\Controllers\Admin\AgendaImportExportController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CatalogItemController;
use App\Http\Controllers\Admin\ClientContactController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientDocumentController;
use App\Http\Controllers\Admin\ClientImportController;
use App\Http\Controllers\Admin\ColaboradorController;
use App\Http\Controllers\Admin\ColaboradorImportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\ErbAddressLookupController;
use App\Http\Controllers\Admin\ErbCommentController;
use App\Http\Controllers\Admin\ErbController;
use App\Http\Controllers\Admin\ErbDocumentController;
use App\Http\Controllers\Admin\ErbImportController;
use App\Http\Controllers\Admin\ErbRelationController;
use App\Http\Controllers\Admin\FolderController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\FormEvaluationController;
use App\Http\Controllers\Admin\FormTemplateController;
use App\Http\Controllers\Admin\ProjectAssignmentController;
use App\Http\Controllers\Admin\ProjectBaselineController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectDependencyController;
use App\Http\Controllers\Admin\ProjectGanttController;
use App\Http\Controllers\Admin\ProjectMemberController;
use App\Http\Controllers\Admin\ProjectResourceController;
use App\Http\Controllers\Admin\ProjectScheduleController;
use App\Http\Controllers\Admin\ProjectTaskController;
use App\Http\Controllers\Admin\RadioLinkCommentController;
use App\Http\Controllers\Admin\RadioLinkController;
use App\Http\Controllers\Admin\RadioLinkDocumentController;
use App\Http\Controllers\Admin\ShortLinkController;
use App\Http\Controllers\Admin\TodoListController;
use App\Http\Controllers\Admin\TodoStepController;
use App\Http\Controllers\Admin\TodoTaskController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderCommentController;
use App\Http\Controllers\Admin\WorkOrderController;
use App\Http\Controllers\Admin\WorkOrderDocumentController;
use App\Http\Controllers\Admin\WorkOrderImportController;
use App\Http\Controllers\Admin\WorkOrderItemController;
use App\Http\Controllers\Admin\WorkOrderItemDocumentController;
use App\Http\Controllers\Admin\WorkOrderProjectController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LinkController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

/*
|--------------------------------------------------------------------------
| Public short links
|--------------------------------------------------------------------------
*/

Route::prefix('s')->name('public.short-links.')->group(function () {
    Route::get('{code}', [LinkController::class, 'show'])->name('show');
    Route::post('{code}/unlock', [LinkController::class, 'unlock'])
        ->middleware('throttle:unlock')
        ->name('unlock');
    Route::post('{code}/documents', [LinkController::class, 'storeDocuments'])->name('documents.store');
    Route::get('documents/{document}/download', [LinkController::class, 'download'])->name('documents.download');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:login');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated area
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings', [SettingsController::class, 'show'])->name('settings');
    Route::put('settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme.update');
    Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::delete('settings/account', [SettingsController::class, 'destroy'])->name('settings.account.destroy');
});

/*
|--------------------------------------------------------------------------
| Administration
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    | Short links
    */

    Route::resource('links', ShortLinkController::class)
        ->parameters(['links' => 'shortLink'])
        ->except(['show', 'edit']);
    Route::post('links/{shortLink}/documents', [ShortLinkController::class, 'storeDocuments'])->name('links.documents.store');
    Route::delete('links/{shortLink}/documents/{document}', [ShortLinkController::class, 'destroyDocument'])->name('links.documents.destroy');

    /*
    | Files and folders
    */

    Route::resource('files', DocumentController::class)
        ->parameters(['files' => 'document'])
        ->only(['index', 'store', 'update', 'destroy']);
    Route::post('files/generate-link', [DocumentController::class, 'generateLink'])->name('files.generate-link');
    Route::post('files/move', [DocumentController::class, 'move'])->name('files.move');
    Route::get('files/{document}/preview', [DocumentController::class, 'preview'])->name('files.preview');
    Route::get('files/{document}/download', [DocumentController::class, 'download'])->name('files.download');

    Route::resource('folders', FolderController::class)->only(['store', 'update', 'destroy']);

    /*
    | Clients
    */

    Route::post('clients/import', [ClientImportController::class, 'store'])->name('clients.import');
    Route::get('clients/import/template', [ClientImportController::class, 'template'])->name('clients.import.template');
    Route::get('clients/export', [ClientController::class, 'export'])->name('clients.export');

    Route::resource('clients', ClientController::class);
    Route::resource('clients.contacts', ClientContactController::class)->only(['store', 'update', 'destroy']);
    Route::resource('clients.documents', ClientDocumentController::class)->only(['store', 'destroy']);

    /*
    | Catalog
    */

    Route::resource('catalog', CatalogItemController::class)->parameters(['catalog' => 'catalogItem']);

    /*
    | ERBs
    */

    Route::post('erbs/import', [ErbImportController::class, 'store'])->name('erbs.import');
    Route::get('erbs/import/template', [ErbImportController::class, 'template'])->name('erbs.import.template');

    Route::prefix('erbs/lookup')->name('erbs.lookup.')->group(function () {
        Route::get('zip', [ErbAddressLookupController::class, 'zip'])->name('zip');
        Route::get('coordinates', [ErbAddressLookupController::class, 'coordinates'])->name('coordinates');
    });

    Route::resource('erbs', ErbController::class);
    Route::resource('erbs.documents', ErbDocumentController::class)->only(['store', 'destroy']);
    Route::resource('erbs.comments', ErbCommentController::class)->only(['store', 'destroy']);

    Route::prefix('erbs/{erb}/relations')->name('erbs.relations.')->group(function () {
        Route::put('projects', [ErbRelationController::class, 'projects'])->name('projects');
        Route::put('work-orders', [ErbRelationController::class, 'workOrders'])->name('work-orders');
        Route::put('radio-links', [ErbRelationController::class, 'radioLinks'])->name('radio-links');
    });

    /*
    | Forms
    */

    Route::resource('forms', FormController::class);
    Route::patch('forms/{form}/steps', [FormController::class, 'updateSteps'])->name('forms.steps.update');

    Route::prefix('forms/{form}/avaliacao')->name('forms.')->group(function () {
        Route::get('/', [FormEvaluationController::class, 'create'])->name('evaluate');
        Route::post('/', [FormEvaluationController::class, 'store'])->name('evaluations.store');
        Route::post('auto-save', [FormEvaluationController::class, 'autosave'])->name('evaluations.autosave');
    });

    /*
    | Form templates
    */

    Route::resource('form-templates', FormTemplateController::class)
        ->parameters(['form-templates' => 'formTemplate'])
        ->except(['show']);
    Route::patch('form-templates/{formTemplate}/steps', [FormTemplateController::class, 'updateSteps'])->name('form-templates.steps.update');

    /*
    | Radio links
    */

    Route::resource('radio-links', RadioLinkController::class)
        ->parameters(['radio-links' => 'radioLink'])
        ->except(['create']);
    Route::resource('radio-links.documents', RadioLinkDocumentController::class)
        ->parameters(['radio-links' => 'radioLink'])
        ->only(['store', 'destroy']);
    Route::resource('radio-links.comments', RadioLinkCommentController::class)
        ->parameters(['radio-links' => 'radioLink'])
        ->only(['store', 'destroy']);

    /*
    | Colaboradores
    */

    Route::post('colaboradores/import', [ColaboradorImportController::class, 'store'])->name('colaboradores.import');
    Route::get('colaboradores/import/template', [ColaboradorImportController::class, 'template'])->name('colaboradores.import.template');
    Route::get('colaboradores/export', [ColaboradorController::class, 'export'])->name('colaboradores.export');

    Route::resource('colaboradores', ColaboradorController::class)
        ->parameters(['colaboradores' => 'colaborador']);

    /*
    | Work orders
    */

    Route::post('work-orders/import', [WorkOrderImportController::class, 'store'])->name('work-orders.import');
    Route::get('work-orders/import/template', [WorkOrderImportController::class, 'template'])->name('work-orders.import.template');

    Route::resource('work-orders', WorkOrderController::class)
        ->parameters(['work-orders' => 'workOrder']);

    Route::post('work-orders/{workOrder}/project', [WorkOrderProjectController::class, 'store'])->name('work-orders.project.store');
    Route::put('work-orders/{workOrder}/items', [WorkOrderController::class, 'updateItems'])->name('work-orders.items.update');
    Route::put('work-orders/{workOrder}/items/{item}/status', [WorkOrderItemController::class, 'updateStatus'])->name('work-orders.items.status.update');
    Route::post('work-orders/{workOrder}/documents', [WorkOrderDocumentController::class, 'store'])->name('work-orders.documents.store');
    Route::delete('work-orders/{workOrder}/documents/{document}', [WorkOrderDocumentController::class, 'destroy'])->name('work-orders.documents.destroy');
    Route::resource('work-orders.items.documents', WorkOrderItemDocumentController::class)
        ->parameters(['work-orders' => 'workOrder'])
        ->only(['store', 'destroy']);
    Route::resource('work-orders.comments', WorkOrderCommentController::class)
        ->parameters(['work-orders' => 'workOrder'])
        ->only(['store', 'destroy']);
    Route::put('work-orders/{workOrder}/dates', [WorkOrderController::class, 'updateDates'])->name('work-orders.dates.update');
    Route::put('work-orders/{workOrder}/description', [WorkOrderController::class, 'updateDescription'])->name('work-orders.description.update');
    Route::put('work-orders/{workOrder}/notes', [WorkOrderController::class, 'updateNotes'])->name('work-orders.notes.update');

    /*
    | Tasks
    */

    Route::get('tasks', [TodoTaskController::class, 'index'])->name('tasks.index');
    Route::get('tasks/export', [TodoTaskController::class, 'export'])->name('tasks.export');
    Route::post('tasks', [TodoTaskController::class, 'store'])->name('tasks.store');
    Route::put('tasks/{todoTask}', [TodoTaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{todoTask}', [TodoTaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('tasks/{todoTask}/toggle', [TodoTaskController::class, 'toggle'])->name('tasks.toggle');
    Route::patch('tasks/{todoTask}/important', [TodoTaskController::class, 'toggleImportant'])->name('tasks.important');
    Route::patch('tasks/{todoTask}/my-day', [TodoTaskController::class, 'toggleMyDay'])->name('tasks.my-day');
    Route::patch('tasks/{todoTask}/move', [TodoTaskController::class, 'move'])->name('tasks.move');

    Route::resource('tasks.steps', TodoStepController::class)
        ->parameters(['tasks' => 'todoTask'])
        ->only(['store', 'update', 'destroy']);

    Route::resource('todo-lists', TodoListController::class)
        ->parameters(['todo-lists' => 'todoList'])
        ->only(['store', 'update', 'destroy']);

    /*
    | Agenda
    */

    Route::get('agenda', [AgendaController::class, 'index'])->name('agenda.index');
    Route::get('agenda/feed', [AgendaController::class, 'feed'])->name('agenda.feed');
    Route::get('agenda/free-busy', [AgendaController::class, 'freeBusy'])->name('agenda.free-busy');
    Route::get('agenda/export', [AgendaImportExportController::class, 'export'])->name('agenda.export');
    Route::post('agenda/import', [AgendaImportExportController::class, 'import'])->name('agenda.import');

    Route::post('agenda/events', [AgendaEventController::class, 'store'])->name('agenda.events.store');
    Route::put('agenda/events/{agendaEvent}', [AgendaEventController::class, 'update'])->name('agenda.events.update');
    Route::delete('agenda/events/{agendaEvent}', [AgendaEventController::class, 'destroy'])->name('agenda.events.destroy');
    Route::patch('agenda/events/{agendaEvent}/move', [AgendaController::class, 'move'])->name('agenda.events.move');
    Route::post('agenda/events/{agendaEvent}/duplicate', [AgendaEventController::class, 'duplicate'])->name('agenda.events.duplicate');
    Route::patch('agenda/events/{agendaEvent}/respond', [AgendaEventController::class, 'respond'])->name('agenda.events.respond');

    Route::post('agenda/calendars', [AgendaCalendarController::class, 'store'])->name('agenda.calendars.store');
    Route::put('agenda/calendars/{agendaCalendar}', [AgendaCalendarController::class, 'update'])->name('agenda.calendars.update');
    Route::delete('agenda/calendars/{agendaCalendar}', [AgendaCalendarController::class, 'destroy'])->name('agenda.calendars.destroy');
    Route::patch('agenda/calendars/{agendaCalendar}/visibility', [AgendaCalendarController::class, 'visibility'])->name('agenda.calendars.visibility');
    Route::patch('agenda/calendars/{agendaCalendar}/default', [AgendaCalendarController::class, 'makeDefault'])->name('agenda.calendars.default');
    Route::post('agenda/calendars/{agendaCalendar}/shares', [AgendaCalendarController::class, 'share'])->name('agenda.calendars.share');
    Route::delete('agenda/calendars/{agendaCalendar}/shares/{share}', [AgendaCalendarController::class, 'unshare'])->name('agenda.calendars.unshare');

    /*
    | Projects
    */

    Route::resource('projects', ProjectController::class);

    Route::resource('projects.tasks', ProjectTaskController::class)->only(['store', 'update', 'destroy']);
    Route::patch('projects/{project}/tasks/{task}/progress', [ProjectTaskController::class, 'progress'])->name('projects.tasks.progress');

    Route::resource('projects.dependencies', ProjectDependencyController::class)->only(['store', 'destroy']);
    Route::resource('projects.resources', ProjectResourceController::class)->only(['store', 'update', 'destroy']);
    Route::resource('projects.assignments', ProjectAssignmentController::class)->only(['store', 'destroy']);
    Route::resource('projects.members', ProjectMemberController::class)->only(['store', 'update', 'destroy']);

    Route::post('projects/{project}/schedule', [ProjectScheduleController::class, 'store'])->name('projects.schedule.store');
    Route::post('projects/{project}/level', [ProjectScheduleController::class, 'level'])->name('projects.level.store');

    Route::resource('projects.baselines', ProjectBaselineController::class)->only(['store']);
    Route::post('projects/{project}/baselines/{baseline}/restore', [ProjectBaselineController::class, 'restore'])->name('projects.baselines.restore');

    Route::get('projects/{project}/gantt', [ProjectGanttController::class, 'data'])->name('projects.gantt.data');
    Route::put('projects/{project}/gantt/tasks/{task}', [ProjectGanttController::class, 'move'])->name('projects.gantt.move');

    /*
    | Trash
    */

    Route::get('trash', fn () => redirect()->route('admin.files.index', ['view' => 'trash']))->name('trash.index');
    Route::delete('trash', [TrashController::class, 'destroy'])->name('trash.destroy');
    Route::patch('trash/documents/{document}/restore', [TrashController::class, 'restoreDocument'])
        ->withTrashed()
        ->name('trash.documents.restore');
    Route::delete('trash/documents/{document}', [TrashController::class, 'forceDestroyDocument'])
        ->withTrashed()
        ->name('trash.documents.force-destroy');
    Route::patch('trash/folders/{folder}/restore', [TrashController::class, 'restoreFolder'])
        ->withTrashed()
        ->name('trash.folders.restore');
    Route::delete('trash/folders/{folder}', [TrashController::class, 'forceDestroyFolder'])
        ->withTrashed()
        ->name('trash.folders.force-destroy');

    /*
    | Administration only
    */

    Route::middleware('admin')->group(function () {
        Route::resource('backups', BackupController::class)->only(['index', 'store', 'destroy']);
        Route::post('backups/import', [BackupController::class, 'import'])->name('backups.import');
        Route::get('backups/status', [BackupController::class, 'status'])->name('backups.status');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');

        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    });
});
