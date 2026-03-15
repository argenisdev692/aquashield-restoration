<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\CauseOfLosses\Infrastructure\Http\Controllers\Api\CauseOfLossController;
use Src\Modules\CauseOfLosses\Infrastructure\Http\Controllers\Web\CauseOfLossPageController;

Route::middleware(['permission:READ_CAUSE_OF_LOSS'])->group(function (): void {
    Route::get('/cause-of-losses', [CauseOfLossPageController::class, 'index']);
    Route::get('/cause-of-losses/{uuid}', [CauseOfLossPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_CAUSE_OF_LOSS'])->group(function (): void {
    Route::get('/cause-of-losses/create', [CauseOfLossPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_CAUSE_OF_LOSS'])->group(function (): void {
    Route::get('/cause-of-losses/{uuid}/edit', [CauseOfLossPageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/cause-of-losses/data/admin')->group(function (): void {
    Route::middleware(['permission:READ_CAUSE_OF_LOSS'])->group(function (): void {
        Route::get('/', [CauseOfLossController::class, 'index']);
        Route::get('/{uuid}', [CauseOfLossController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_CAUSE_OF_LOSS'])->group(function (): void {
        Route::post('/', [CauseOfLossController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_CAUSE_OF_LOSS'])->group(function (): void {
        Route::put('/{uuid}', [CauseOfLossController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_CAUSE_OF_LOSS'])->group(function (): void {
        Route::delete('/{uuid}', [CauseOfLossController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [CauseOfLossController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_CAUSE_OF_LOSS'])->group(function (): void {
        Route::patch('/{uuid}/restore', [CauseOfLossController::class, 'restore'])->whereUuid('uuid');
    });
});
