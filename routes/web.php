<?php

use App\Http\Controllers\Admin\CatalogItemController;
use App\Http\Controllers\Admin\ClientContactController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientDocumentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\ErbCommentController;
use App\Http\Controllers\Admin\ErbController;
use App\Http\Controllers\Admin\ErbDocumentController;
use App\Http\Controllers\Admin\ErbImportController;
use App\Http\Controllers\Admin\FolderController;
use App\Http\Controllers\Admin\ProjectAssignmentController;
use App\Http\Controllers\Admin\ProjectBaselineController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectDependencyController;
use App\Http\Controllers\Admin\ProjectGanttController;
use App\Http\Controllers\Admin\ProjectMemberController;
use App\Http\Controllers\Admin\ProjectResourceController;
use App\Http\Controllers\Admin\ProjectScheduleController;
use App\Http\Controllers\Admin\ProjectTaskController;
use App\Http\Controllers\Admin\RadioLinkController;
use App\Http\Controllers\Admin\ShortLinkController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkOrderController;
use App\Http\Controllers\Admin\WorkOrderImportController;
use App\Http\Controllers\Admin\WorkOrderProjectController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LinkController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('s/{code}', [LinkController::class, 'show'])->name('public.short-links.show');
Route::post('s/{code}/unlock', [LinkController::class, 'unlock'])
    ->middleware('throttle:unlock')
    ->name('public.short-links.unlock');
Route::post('s/{code}/documents', [LinkController::class, 'storeDocuments'])->name('public.short-links.documents.store');
Route::get('s/documents/{document}/download', [LinkController::class, 'download'])->name('public.short-links.documents.download');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:login');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings', [SettingsController::class, 'show'])->name('settings');
    Route::put('settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme.update');
    Route::put('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::delete('settings/account', [SettingsController::class, 'destroy'])->name('settings.account.destroy');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('links', ShortLinkController::class)
        ->parameters(['links' => 'shortLink'])
        ->except(['show']);
    Route::post('links/{shortLink}/documents', [ShortLinkController::class, 'storeDocuments'])->name('links.documents.store');
    Route::delete('links/{shortLink}/documents/{document}', [ShortLinkController::class, 'destroyDocument'])->name('links.documents.destroy');

    Route::get('files', [DocumentController::class, 'index'])->name('files.index');
    Route::post('files', [DocumentController::class, 'store'])->name('files.store');
    Route::post('files/generate-link', [DocumentController::class, 'generateLink'])->name('files.generate-link');
    Route::post('files/move', [DocumentController::class, 'move'])->name('files.move');
    Route::get('files/{document}/preview', [DocumentController::class, 'preview'])->name('files.preview');
    Route::put('files/{document}', [DocumentController::class, 'update'])->name('files.update');
    Route::delete('files/{document}', [DocumentController::class, 'destroy'])->name('files.destroy');

    Route::post('folders', [FolderController::class, 'store'])->name('folders.store');
    Route::put('folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::post('clients/{client}/contacts', [ClientContactController::class, 'store'])->name('clients.contacts.store');
    Route::put('clients/{client}/contacts/{contact}', [ClientContactController::class, 'update'])->name('clients.contacts.update');
    Route::delete('clients/{client}/contacts/{contact}', [ClientContactController::class, 'destroy'])->name('clients.contacts.destroy');

    Route::post('clients/{client}/documents', [ClientDocumentController::class, 'store'])->name('clients.documents.store');
    Route::delete('clients/{client}/documents/{document}', [ClientDocumentController::class, 'destroy'])->name('clients.documents.destroy');

    Route::get('catalog', [CatalogItemController::class, 'index'])->name('catalog.index');
    Route::get('catalog/create', [CatalogItemController::class, 'create'])->name('catalog.create');
    Route::post('catalog', [CatalogItemController::class, 'store'])->name('catalog.store');
    Route::get('catalog/{catalogItem}', [CatalogItemController::class, 'show'])->name('catalog.show');
    Route::get('catalog/{catalogItem}/edit', [CatalogItemController::class, 'edit'])->name('catalog.edit');
    Route::put('catalog/{catalogItem}', [CatalogItemController::class, 'update'])->name('catalog.update');
    Route::delete('catalog/{catalogItem}', [CatalogItemController::class, 'destroy'])->name('catalog.destroy');

    Route::get('erbs', [ErbController::class, 'index'])->name('erbs.index');
    Route::get('erbs/create', [ErbController::class, 'create'])->name('erbs.create');
    Route::post('erbs', [ErbController::class, 'store'])->name('erbs.store');
    Route::post('erbs/import', [ErbImportController::class, 'store'])->name('erbs.import');
    Route::get('erbs/import/template', [ErbImportController::class, 'template'])->name('erbs.import.template');
    Route::get('erbs/{erb}', [ErbController::class, 'show'])->name('erbs.show');
    Route::get('erbs/{erb}/edit', [ErbController::class, 'edit'])->name('erbs.edit');
    Route::put('erbs/{erb}', [ErbController::class, 'update'])->name('erbs.update');
    Route::delete('erbs/{erb}', [ErbController::class, 'destroy'])->name('erbs.destroy');

    Route::post('erbs/{erb}/documents', [ErbDocumentController::class, 'store'])->name('erbs.documents.store');
    Route::delete('erbs/{erb}/documents/{document}', [ErbDocumentController::class, 'destroy'])->name('erbs.documents.destroy');

    Route::post('erbs/{erb}/comments', [ErbCommentController::class, 'store'])->name('erbs.comments.store');
    Route::delete('erbs/{erb}/comments/{comment}', [ErbCommentController::class, 'destroy'])->name('erbs.comments.destroy');

    Route::get('radio-links', [RadioLinkController::class, 'index'])->name('radio-links.index');
    Route::post('radio-links', [RadioLinkController::class, 'store'])->name('radio-links.store');
    Route::get('radio-links/{radioLink}', [RadioLinkController::class, 'show'])->name('radio-links.show');
    Route::get('radio-links/{radioLink}/edit', [RadioLinkController::class, 'edit'])->name('radio-links.edit');
    Route::put('radio-links/{radioLink}', [RadioLinkController::class, 'update'])->name('radio-links.update');
    Route::delete('radio-links/{radioLink}', [RadioLinkController::class, 'destroy'])->name('radio-links.destroy');

    Route::get('work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
    Route::post('work-orders/import', [WorkOrderImportController::class, 'store'])->name('work-orders.import');
    Route::get('work-orders/import/template', [WorkOrderImportController::class, 'template'])->name('work-orders.import.template');
    Route::post('work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::post('work-orders/{workOrder}/project', [WorkOrderProjectController::class, 'store'])->name('work-orders.project.store');
    Route::get('work-orders/{workOrder}/edit', [WorkOrderController::class, 'edit'])->name('work-orders.edit');
    Route::put('work-orders/{workOrder}', [WorkOrderController::class, 'update'])->name('work-orders.update');
    Route::delete('work-orders/{workOrder}', [WorkOrderController::class, 'destroy'])->name('work-orders.destroy');

    Route::post('projects/{project}/tasks', [ProjectTaskController::class, 'store'])->name('projects.tasks.store');
    Route::put('projects/{project}/tasks/{task}', [ProjectTaskController::class, 'update'])->name('projects.tasks.update');
    Route::delete('projects/{project}/tasks/{task}', [ProjectTaskController::class, 'destroy'])->name('projects.tasks.destroy');
    Route::patch('projects/{project}/tasks/{task}/progress', [ProjectTaskController::class, 'progress'])->name('projects.tasks.progress');

    Route::post('projects/{project}/dependencies', [ProjectDependencyController::class, 'store'])->name('projects.dependencies.store');
    Route::delete('projects/{project}/dependencies/{dependency}', [ProjectDependencyController::class, 'destroy'])->name('projects.dependencies.destroy');

    Route::post('projects/{project}/resources', [ProjectResourceController::class, 'store'])->name('projects.resources.store');
    Route::put('projects/{project}/resources/{resource}', [ProjectResourceController::class, 'update'])->name('projects.resources.update');
    Route::delete('projects/{project}/resources/{resource}', [ProjectResourceController::class, 'destroy'])->name('projects.resources.destroy');

    Route::post('projects/{project}/assignments', [ProjectAssignmentController::class, 'store'])->name('projects.assignments.store');
    Route::delete('projects/{project}/assignments/{assignment}', [ProjectAssignmentController::class, 'destroy'])->name('projects.assignments.destroy');

    Route::post('projects/{project}/schedule', [ProjectScheduleController::class, 'store'])->name('projects.schedule.store');
    Route::post('projects/{project}/level', [ProjectScheduleController::class, 'level'])->name('projects.level.store');

    Route::post('projects/{project}/baselines', [ProjectBaselineController::class, 'store'])->name('projects.baselines.store');
    Route::post('projects/{project}/baselines/{baseline}/restore', [ProjectBaselineController::class, 'restore'])->name('projects.baselines.restore');

    Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::put('projects/{project}/members/{member}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
    Route::delete('projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

    Route::get('projects/{project}/gantt', [ProjectGanttController::class, 'data'])->name('projects.gantt.data');
    Route::put('projects/{project}/gantt/tasks/{task}', [ProjectGanttController::class, 'move'])->name('projects.gantt.move');

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

    Route::middleware('admin')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
