<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\ProjectTypes\Infrastructure\Http\Controllers\Api\ProjectTypeController;
use Src\Modules\ProjectTypes\Infrastructure\Http\Controllers\Api\ProjectTypeExportController;

Route::middleware(['permission:READ_PROJECT_TYPE'])->group(function (): void {
    Route::get('/export', ProjectTypeExportController::class);
    Route::get('/service-categories', [ProjectTypeController::class, 'serviceCategories']);
    Route::get('/', [ProjectTypeController::class, 'index']);
    Route::get('/{uuid}', [ProjectTypeController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_PROJECT_TYPE'])->group(function (): void {
    Route::post('/', [ProjectTypeController::class, 'store']);
});

Route::middleware(['permission:UPDATE_PROJECT_TYPE'])->group(function (): void {
    Route::put('/{uuid}', [ProjectTypeController::class, 'update'])->whereUuid('uuid');
});

Route::middleware(['permission:DELETE_PROJECT_TYPE'])->group(function (): void {
    Route::delete('/{uuid}', [ProjectTypeController::class, 'destroy'])->whereUuid('uuid');
    Route::post('/bulk-delete', [ProjectTypeController::class, 'bulkDelete']);
});

Route::middleware(['permission:RESTORE_PROJECT_TYPE'])->group(function (): void {
    Route::patch('/{uuid}/restore', [ProjectTypeController::class, 'restore'])->whereUuid('uuid');
});
