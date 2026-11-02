<?php

use Illuminate\Support\Facades\Route;
use Plugins\WorkOrders\Http\Controllers\WorkOrderCommentController;
use Plugins\WorkOrders\Http\Controllers\WorkOrderController;
use Plugins\WorkOrders\Http\Controllers\WorkOrderDocumentController;
use Plugins\WorkOrders\Http\Controllers\WorkOrderImportController;
use Plugins\WorkOrders\Http\Controllers\WorkOrderItemController;
use Plugins\WorkOrders\Http\Controllers\WorkOrderItemDocumentController;
use Plugins\WorkOrders\Http\Controllers\WorkOrderProjectController;

Route::post('import', [WorkOrderImportController::class, 'store'])->name('import');
Route::get('import/template', [WorkOrderImportController::class, 'template'])->name('import.template');
Route::get('export', [WorkOrderController::class, 'export'])->name('export');

Route::get('/', [WorkOrderController::class, 'index'])->name('index');
Route::get('create', [WorkOrderController::class, 'create'])->name('create');
Route::post('/', [WorkOrderController::class, 'store'])->name('store');

Route::get('{workOrder}', [WorkOrderController::class, 'show'])->name('show');
Route::get('{workOrder}/edit', [WorkOrderController::class, 'edit'])->name('edit');
Route::match(['put', 'patch'], '{workOrder}', [WorkOrderController::class, 'update'])->name('update');
Route::delete('{workOrder}', [WorkOrderController::class, 'destroy'])->name('destroy');

Route::post('{workOrder}/project', [WorkOrderProjectController::class, 'store'])->name('project.store');
Route::put('{workOrder}/items', [WorkOrderController::class, 'updateItems'])->name('items.update');
Route::put('{workOrder}/items/{item}/status', [WorkOrderItemController::class, 'updateStatus'])->name('items.status.update');
Route::post('{workOrder}/documents', [WorkOrderDocumentController::class, 'store'])->name('documents.store');
Route::delete('{workOrder}/documents/{document}', [WorkOrderDocumentController::class, 'destroy'])->name('documents.destroy');
Route::post('{workOrder}/items/{item}/documents', [WorkOrderItemDocumentController::class, 'store'])->name('items.documents.store');
Route::delete('{workOrder}/items/{item}/documents/{document}', [WorkOrderItemDocumentController::class, 'destroy'])->name('items.documents.destroy');
Route::post('{workOrder}/comments', [WorkOrderCommentController::class, 'store'])->name('comments.store');
Route::delete('{workOrder}/comments/{comment}', [WorkOrderCommentController::class, 'destroy'])->name('comments.destroy');
Route::put('{workOrder}/dates', [WorkOrderController::class, 'updateDates'])->name('dates.update');
Route::put('{workOrder}/description', [WorkOrderController::class, 'updateDescription'])->name('description.update');
Route::put('{workOrder}/notes', [WorkOrderController::class, 'updateNotes'])->name('notes.update');
