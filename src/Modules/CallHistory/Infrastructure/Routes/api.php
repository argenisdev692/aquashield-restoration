<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\CallHistory\Infrastructure\Http\Controllers\Api\CallHistoryExportController;
use Modules\CallHistory\Infrastructure\Http\Controllers\CallHistoryController;

/*
|--------------------------------------------------------------------------
| Call History API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the CallHistoryServiceProvider and
| are prefixed with /api automatically by Laravel.
|
*/

Route::prefix('call-history')->name('api.call-history.')->middleware(['auth:sanctum'])->group(function (): void {
    // List and detail
    Route::get('/list', [CallHistoryController::class, 'data'])->name('list');
    Route::get('/{uuid}', [CallHistoryController::class, 'get'])->name('get');

    // Update and delete
    Route::put('/{uuid}', [CallHistoryController::class, 'update'])->name('update');
    Route::delete('/{uuid}', [CallHistoryController::class, 'destroy'])->name('destroy');
    Route::post('/{uuid}/restore', [CallHistoryController::class, 'restore'])->name('restore');

    // Bulk operations
    Route::post('/bulk-delete', [CallHistoryController::class, 'bulkDelete'])->name('bulk-delete');

    // Sync from Retell AI
    Route::post('/sync', [CallHistoryController::class, 'sync'])->name('sync');

    // Export
    Route::get('/export', CallHistoryExportController::class)->name('export');
});
