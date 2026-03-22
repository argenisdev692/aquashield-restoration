<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\ServiceCategories\Infrastructure\Http\Controllers\Api\ServiceCategoryController;
use Src\Modules\ServiceCategories\Infrastructure\Http\Controllers\Api\ServiceCategoryExportController;

Route::middleware(['permission:READ_SERVICE_CATEGORY'])->group(function (): void {
    Route::get('/export', ServiceCategoryExportController::class);
    Route::get('/', [ServiceCategoryController::class, 'index']);
    Route::get('/{uuid}', [ServiceCategoryController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_SERVICE_CATEGORY'])->group(function (): void {
    Route::post('/', [ServiceCategoryController::class, 'store']);
});

Route::middleware(['permission:UPDATE_SERVICE_CATEGORY'])->group(function (): void {
    Route::put('/{uuid}', [ServiceCategoryController::class, 'update'])->whereUuid('uuid');
});

Route::middleware(['permission:DELETE_SERVICE_CATEGORY'])->group(function (): void {
    Route::delete('/{uuid}', [ServiceCategoryController::class, 'destroy'])->whereUuid('uuid');
    Route::post('/bulk-delete', [ServiceCategoryController::class, 'bulkDelete']);
});

Route::middleware(['permission:RESTORE_SERVICE_CATEGORY'])->group(function (): void {
    Route::patch('/{uuid}/restore', [ServiceCategoryController::class, 'restore'])->whereUuid('uuid');
});
