<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Controllers\Api\ClaimStatusController;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Controllers\Api\ClaimStatusExportController;

Route::middleware(['permission:READ_CLAIM_STATUS'])->group(function (): void {
    Route::get('/export', ClaimStatusExportController::class);
    Route::get('/', [ClaimStatusController::class, 'index']);
    Route::get('/{uuid}', [ClaimStatusController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_CLAIM_STATUS'])->group(function (): void {
    Route::post('/', [ClaimStatusController::class, 'store']);
});

Route::middleware(['permission:UPDATE_CLAIM_STATUS'])->group(function (): void {
    Route::put('/{uuid}', [ClaimStatusController::class, 'update'])->whereUuid('uuid');
});

Route::middleware(['permission:DELETE_CLAIM_STATUS'])->group(function (): void {
    Route::delete('/{uuid}', [ClaimStatusController::class, 'destroy'])->whereUuid('uuid');
    Route::post('/bulk-delete', [ClaimStatusController::class, 'bulkDelete']);
});

Route::middleware(['permission:RESTORE_CLAIM_STATUS'])->group(function (): void {
    Route::patch('/{uuid}/restore', [ClaimStatusController::class, 'restore'])->whereUuid('uuid');
});
