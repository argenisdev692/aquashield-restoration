<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\CategoryProducts\Infrastructure\Http\Controllers\Api\CategoryProductController;
use Src\Modules\CategoryProducts\Infrastructure\Http\Controllers\Web\CategoryProductPageController;

Route::middleware(['permission:READ_CATEGORY_PRODUCT'])->group(function (): void {
    Route::get('/category-products', [CategoryProductPageController::class, 'index']);
    Route::get('/category-products/{uuid}', [CategoryProductPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_CATEGORY_PRODUCT'])->group(function (): void {
    Route::get('/category-products/create', [CategoryProductPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_CATEGORY_PRODUCT'])->group(function (): void {
    Route::get('/category-products/{uuid}/edit', [CategoryProductPageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/category-products/data/admin')->group(function (): void {
    Route::middleware(['permission:READ_CATEGORY_PRODUCT'])->group(function (): void {
        Route::get('/', [CategoryProductController::class, 'index']);
        Route::get('/export', [CategoryProductController::class, 'export']);
        Route::get('/{uuid}', [CategoryProductController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_CATEGORY_PRODUCT'])->group(function (): void {
        Route::post('/', [CategoryProductController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_CATEGORY_PRODUCT'])->group(function (): void {
        Route::put('/{uuid}', [CategoryProductController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_CATEGORY_PRODUCT'])->group(function (): void {
        Route::delete('/{uuid}', [CategoryProductController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [CategoryProductController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_CATEGORY_PRODUCT'])->group(function (): void {
        Route::patch('/{uuid}/restore', [CategoryProductController::class, 'restore'])->whereUuid('uuid');
    });
});
