<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Customers\Infrastructure\Http\Controllers\Api\CustomerController;
use Src\Modules\Customers\Infrastructure\Http\Controllers\Api\CustomerExportController;
use Src\Modules\Customers\Infrastructure\Http\Controllers\Web\CustomerPageController;

Route::middleware(['permission:VIEW_CUSTOMER'])->group(function (): void {
    Route::get('/customers', [CustomerPageController::class, 'index']);
    Route::get('/customers/{uuid}', [CustomerPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_CUSTOMER'])->group(function (): void {
    Route::get('/customers/create', [CustomerPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_CUSTOMER'])->group(function (): void {
    Route::get('/customers/{uuid}/edit', [CustomerPageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/customers/data/admin')->group(function (): void {
    Route::middleware(['permission:VIEW_CUSTOMER'])->group(function (): void {
        Route::get('/export', CustomerExportController::class);
        Route::get('/', [CustomerController::class, 'index']);
        Route::get('/{uuid}', [CustomerController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_CUSTOMER'])->group(function (): void {
        Route::post('/', [CustomerController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_CUSTOMER'])->group(function (): void {
        Route::put('/{uuid}', [CustomerController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_CUSTOMER'])->group(function (): void {
        Route::delete('/{uuid}', [CustomerController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [CustomerController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_CUSTOMER'])->group(function (): void {
        Route::patch('/{uuid}/restore', [CustomerController::class, 'restore'])->whereUuid('uuid');
    });
});
