<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Controllers\Api\DocumentTemplateAdjusterController;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Controllers\Api\DocumentTemplateAdjusterExportController;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Controllers\Web\DocumentTemplateAdjusterPageController;

Route::middleware(['permission:VIEW_DOCUMENT_TEMPLATE_ADJUSTER'])->group(function (): void {
    Route::get('/', [DocumentTemplateAdjusterPageController::class, 'index'])->name('document-template-adjusters.index');
    Route::get('/{uuid}', [DocumentTemplateAdjusterPageController::class, 'show'])->whereUuid('uuid')->name('document-template-adjusters.show');
});

Route::middleware(['permission:CREATE_DOCUMENT_TEMPLATE_ADJUSTER'])->group(function (): void {
    Route::get('/create', [DocumentTemplateAdjusterPageController::class, 'create'])->name('document-template-adjusters.create');
});

Route::middleware(['permission:UPDATE_DOCUMENT_TEMPLATE_ADJUSTER'])->group(function (): void {
    Route::get('/{uuid}/edit', [DocumentTemplateAdjusterPageController::class, 'edit'])->whereUuid('uuid')->name('document-template-adjusters.edit');
});

Route::prefix('data/admin')->group(function (): void {
    Route::middleware(['permission:VIEW_DOCUMENT_TEMPLATE_ADJUSTER'])->group(function (): void {
        Route::get('/export', DocumentTemplateAdjusterExportController::class);
        Route::get('/', [DocumentTemplateAdjusterController::class, 'index']);
        Route::get('/{uuid}', [DocumentTemplateAdjusterController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_DOCUMENT_TEMPLATE_ADJUSTER'])->group(function (): void {
        Route::post('/', [DocumentTemplateAdjusterController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_DOCUMENT_TEMPLATE_ADJUSTER'])->group(function (): void {
        Route::post('/{uuid}', [DocumentTemplateAdjusterController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_DOCUMENT_TEMPLATE_ADJUSTER'])->group(function (): void {
        Route::delete('/{uuid}', [DocumentTemplateAdjusterController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [DocumentTemplateAdjusterController::class, 'bulkDelete']);
    });
});
