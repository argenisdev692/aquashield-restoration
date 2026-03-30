<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Claims\Infrastructure\Http\Controllers\Api\ClaimController;
use Src\Modules\Claims\Infrastructure\Http\Controllers\Api\ClaimExportController;

Route::middleware(['permission:VIEW_CLAIM'])->group(function (): void {
    Route::get('/export', ClaimExportController::class);
    Route::get('/', [ClaimController::class, 'index']);
    Route::get('/{uuid}', [ClaimController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_CLAIM'])->group(function (): void {
    Route::post('/', [ClaimController::class, 'store']);
});

Route::middleware(['permission:UPDATE_CLAIM'])->group(function (): void {
    Route::put('/{uuid}', [ClaimController::class, 'update'])->whereUuid('uuid');
});

Route::middleware(['permission:DELETE_CLAIM'])->group(function (): void {
    Route::delete('/{uuid}', [ClaimController::class, 'destroy'])->whereUuid('uuid');
    Route::post('/bulk-delete', [ClaimController::class, 'bulkDelete']);
});

Route::middleware(['permission:RESTORE_CLAIM'])->group(function (): void {
    Route::patch('/{uuid}/restore', [ClaimController::class, 'restore'])->whereUuid('uuid');
});
