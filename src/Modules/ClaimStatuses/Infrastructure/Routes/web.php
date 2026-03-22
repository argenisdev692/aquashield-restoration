<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Controllers\Api\ClaimStatusController;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Controllers\Api\ClaimStatusExportController;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Controllers\Web\ClaimStatusPageController;

Route::middleware(['permission:READ_CLAIM_STATUS'])->group(function (): void {
    Route::get('/claim-statuses', [ClaimStatusPageController::class, 'index']);
    Route::get('/claim-statuses/{uuid}', [ClaimStatusPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_CLAIM_STATUS'])->group(function (): void {
    Route::get('/claim-statuses/create', [ClaimStatusPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_CLAIM_STATUS'])->group(function (): void {
    Route::get('/claim-statuses/{uuid}/edit', [ClaimStatusPageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/claim-statuses/data/admin')->group(function (): void {
    Route::middleware(['permission:READ_CLAIM_STATUS'])->group(function (): void {
        Route::get('/', [ClaimStatusController::class, 'index']);
        Route::get('/export', ClaimStatusExportController::class);
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
});
