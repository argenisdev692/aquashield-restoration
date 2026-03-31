<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Api\ScopeSheetController;
use Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Api\ScopeSheetDocumentController;
use Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Api\ScopeSheetExportController;
use Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Web\ScopeSheetPageController;

Route::prefix('/scope-sheets')->group(function (): void {

    Route::get('/', [ScopeSheetPageController::class, 'index'])->name('scope-sheets.index');
    Route::get('/create', [ScopeSheetPageController::class, 'create'])->name('scope-sheets.create');
    Route::get('/{uuid}', [ScopeSheetPageController::class, 'show'])->name('scope-sheets.show')->whereUuid('uuid');
    Route::get('/{uuid}/edit', [ScopeSheetPageController::class, 'edit'])->name('scope-sheets.edit')->whereUuid('uuid');

    Route::prefix('data')->group(function (): void {
        Route::middleware(['permission:VIEW_SCOPE_SHEET'])->group(function (): void {
            Route::get('/admin', [ScopeSheetController::class, 'index']);
            Route::get('/admin/export', ScopeSheetExportController::class);
            Route::get('/admin/{uuid}', [ScopeSheetController::class, 'show'])->whereUuid('uuid');
            Route::get('/admin/{uuid}/generate-pdf', ScopeSheetDocumentController::class)->whereUuid('uuid');
        });

        Route::middleware(['permission:CREATE_SCOPE_SHEET'])->group(function (): void {
            Route::post('/admin', [ScopeSheetController::class, 'store']);
        });

        Route::middleware(['permission:UPDATE_SCOPE_SHEET'])->group(function (): void {
            Route::put('/admin/{uuid}', [ScopeSheetController::class, 'update'])->whereUuid('uuid');
        });

        Route::middleware(['permission:DELETE_SCOPE_SHEET'])->group(function (): void {
            Route::delete('/admin/{uuid}', [ScopeSheetController::class, 'destroy'])->whereUuid('uuid');
            Route::post('/admin/bulk-delete', [ScopeSheetController::class, 'bulkDelete']);
        });

        Route::middleware(['permission:RESTORE_SCOPE_SHEET'])->group(function (): void {
            Route::patch('/admin/{uuid}/restore', [ScopeSheetController::class, 'restore'])->whereUuid('uuid');
        });
    });
});
