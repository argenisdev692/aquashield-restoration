<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Controllers\Api\DocumentTemplateAdjusterController;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Controllers\Api\DocumentTemplateAdjusterExportController;

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
