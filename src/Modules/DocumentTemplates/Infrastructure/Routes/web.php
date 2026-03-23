<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Controllers\Api\DocumentTemplateController;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Controllers\Api\DocumentTemplateExportController;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Controllers\Web\DocumentTemplatePageController;

Route::middleware(['permission:READ_DOCUMENT_TEMPLATE'])->group(function (): void {
    Route::get('/', [DocumentTemplatePageController::class, 'index'])->name('document-templates.index');
    Route::get('/{uuid}', [DocumentTemplatePageController::class, 'show'])->whereUuid('uuid')->name('document-templates.show');
});

Route::middleware(['permission:CREATE_DOCUMENT_TEMPLATE'])->group(function (): void {
    Route::get('/create', [DocumentTemplatePageController::class, 'create'])->name('document-templates.create');
});

Route::middleware(['permission:UPDATE_DOCUMENT_TEMPLATE'])->group(function (): void {
    Route::get('/{uuid}/edit', [DocumentTemplatePageController::class, 'edit'])->whereUuid('uuid')->name('document-templates.edit');
});

Route::prefix('data/admin')->group(function (): void {
    Route::middleware(['permission:READ_DOCUMENT_TEMPLATE'])->group(function (): void {
        Route::get('/export', DocumentTemplateExportController::class);
        Route::get('/', [DocumentTemplateController::class, 'index']);
        Route::get('/{uuid}', [DocumentTemplateController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_DOCUMENT_TEMPLATE'])->group(function (): void {
        Route::post('/', [DocumentTemplateController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_DOCUMENT_TEMPLATE'])->group(function (): void {
        Route::post('/{uuid}', [DocumentTemplateController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_DOCUMENT_TEMPLATE'])->group(function (): void {
        Route::delete('/{uuid}', [DocumentTemplateController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [DocumentTemplateController::class, 'bulkDelete']);
    });
});
