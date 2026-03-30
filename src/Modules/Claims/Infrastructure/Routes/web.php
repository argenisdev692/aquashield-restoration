<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Claims\Infrastructure\Http\Controllers\Api\ClaimController;
use Src\Modules\Claims\Infrastructure\Http\Controllers\Api\ClaimExportController;
use Src\Modules\Claims\Infrastructure\Http\Controllers\Web\ClaimPageController;

Route::middleware(['permission:VIEW_CLAIM'])->group(function (): void {
    Route::get('/claims', [ClaimPageController::class, 'index'])->name('claims.index');
    Route::get('/claims/{uuid}', [ClaimPageController::class, 'show'])->name('claims.show')->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_CLAIM'])->group(function (): void {
    Route::get('/claims/create', [ClaimPageController::class, 'create'])->name('claims.create');
});

Route::middleware(['permission:UPDATE_CLAIM'])->group(function (): void {
    Route::get('/claims/{uuid}/edit', [ClaimPageController::class, 'edit'])->name('claims.edit')->whereUuid('uuid');
});

Route::prefix('/claims/data/admin')->group(function (): void {
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
});
