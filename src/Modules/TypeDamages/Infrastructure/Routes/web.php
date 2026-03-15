<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\TypeDamages\Infrastructure\Http\Controllers\Api\TypeDamageController;
use Src\Modules\TypeDamages\Infrastructure\Http\Controllers\Web\TypeDamagePageController;

Route::middleware(['permission:READ_TYPE_DAMAGE'])->group(function (): void {
    Route::get('/type-damages', [TypeDamagePageController::class, 'index']);
    Route::get('/type-damages/{uuid}', [TypeDamagePageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_TYPE_DAMAGE'])->group(function (): void {
    Route::get('/type-damages/create', [TypeDamagePageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_TYPE_DAMAGE'])->group(function (): void {
    Route::get('/type-damages/{uuid}/edit', [TypeDamagePageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/type-damages/data/admin')->group(function (): void {
    Route::middleware(['permission:READ_TYPE_DAMAGE'])->group(function (): void {
        Route::get('/', [TypeDamageController::class, 'index']);
        Route::get('/{uuid}', [TypeDamageController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_TYPE_DAMAGE'])->group(function (): void {
        Route::post('/', [TypeDamageController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_TYPE_DAMAGE'])->group(function (): void {
        Route::put('/{uuid}', [TypeDamageController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_TYPE_DAMAGE'])->group(function (): void {
        Route::delete('/{uuid}', [TypeDamageController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [TypeDamageController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_TYPE_DAMAGE'])->group(function (): void {
        Route::patch('/{uuid}/restore', [TypeDamageController::class, 'restore'])->whereUuid('uuid');
    });
});
