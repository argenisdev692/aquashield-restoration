<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\CallHistory\Infrastructure\Http\Controllers\Api\CallHistoryExportController;
use Modules\CallHistory\Infrastructure\Http\Controllers\CallHistoryController;

Route::prefix('call-history')->name('call-history.')->group(function (): void {
    Route::get('/', [CallHistoryController::class, 'index'])->name('index');
    Route::get('/{uuid}', [CallHistoryController::class, 'show'])->name('show');

    Route::prefix('data/admin')->name('data.admin.')->group(function (): void {
        Route::get('/list', [CallHistoryController::class, 'data'])->name('list');
        Route::get('/{uuid}', [CallHistoryController::class, 'get'])->name('get');
        Route::put('/{uuid}', [CallHistoryController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [CallHistoryController::class, 'destroy'])->name('destroy');
        Route::post('/{uuid}/restore', [CallHistoryController::class, 'restore'])->name('restore');
        Route::post('/bulk-delete', [CallHistoryController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/sync', [CallHistoryController::class, 'sync'])->name('sync');
    });

    // Export endpoint
    Route::get('/export', CallHistoryExportController::class)->name('export');
});
