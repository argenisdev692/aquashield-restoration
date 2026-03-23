<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Controllers\Api\DocumentTemplateController;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Controllers\Api\DocumentTemplateExportController;

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
