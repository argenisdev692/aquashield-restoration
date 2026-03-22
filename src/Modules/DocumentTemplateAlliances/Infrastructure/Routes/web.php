<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Controllers\Api\DocumentTemplateAllianceController;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Controllers\Api\DocumentTemplateAllianceExportController;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Controllers\Web\DocumentTemplateAlliancePageController;

Route::middleware(['permission:VIEW_DOCUMENT_TEMPLATE_ALLIANCE'])->group(function (): void {
    Route::get('/', [DocumentTemplateAlliancePageController::class, 'index'])->name('document-template-alliances.index');
    Route::get('/{uuid}', [DocumentTemplateAlliancePageController::class, 'show'])->whereUuid('uuid')->name('document-template-alliances.show');
});

Route::middleware(['permission:CREATE_DOCUMENT_TEMPLATE_ALLIANCE'])->group(function (): void {
    Route::get('/create', [DocumentTemplateAlliancePageController::class, 'create'])->name('document-template-alliances.create');
});

Route::middleware(['permission:UPDATE_DOCUMENT_TEMPLATE_ALLIANCE'])->group(function (): void {
    Route::get('/{uuid}/edit', [DocumentTemplateAlliancePageController::class, 'edit'])->whereUuid('uuid')->name('document-template-alliances.edit');
});

Route::prefix('data/admin')->group(function (): void {
    Route::middleware(['permission:VIEW_DOCUMENT_TEMPLATE_ALLIANCE'])->group(function (): void {
        Route::get('/export', DocumentTemplateAllianceExportController::class);
        Route::get('/', [DocumentTemplateAllianceController::class, 'index']);
        Route::get('/{uuid}', [DocumentTemplateAllianceController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_DOCUMENT_TEMPLATE_ALLIANCE'])->group(function (): void {
        Route::post('/', [DocumentTemplateAllianceController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_DOCUMENT_TEMPLATE_ALLIANCE'])->group(function (): void {
        Route::post('/{uuid}', [DocumentTemplateAllianceController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_DOCUMENT_TEMPLATE_ALLIANCE'])->group(function (): void {
        Route::delete('/{uuid}', [DocumentTemplateAllianceController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [DocumentTemplateAllianceController::class, 'bulkDelete']);
    });
});
