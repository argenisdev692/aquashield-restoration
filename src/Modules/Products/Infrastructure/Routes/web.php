<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Products\Infrastructure\Http\Controllers\Api\ProductController;
use Src\Modules\Products\Infrastructure\Http\Controllers\Web\ProductPageController;

// Inertia pages
Route::middleware(['permission:READ_PRODUCT'])->group(function () {
    Route::get('/products', [ProductPageController::class, 'index']);
    Route::get('/products/{uuid}', [ProductPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_PRODUCT'])->group(function () {
    Route::get('/products/create', [ProductPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_PRODUCT'])->group(function () {
    Route::get('/products/{uuid}/edit', [ProductPageController::class, 'edit'])->whereUuid('uuid');
});

// JSON data endpoints (used by TanStack Query — web session auth)
Route::prefix('/products/data/admin')->group(function () {
    Route::middleware(['permission:READ_PRODUCT'])->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/export', [ProductController::class, 'export']);
        Route::get('/{uuid}', [ProductController::class, 'show'])->whereUuid('uuid');
    });
    
    Route::middleware(['permission:CREATE_PRODUCT'])->group(function () {
        Route::post('/', [ProductController::class, 'store']);
    });
    
    Route::middleware(['permission:UPDATE_PRODUCT'])->group(function () {
        Route::put('/{uuid}', [ProductController::class, 'update'])->whereUuid('uuid');
    });
    
    Route::middleware(['permission:DELETE_PRODUCT'])->group(function () {
        Route::delete('/{uuid}', [ProductController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [ProductController::class, 'bulkDelete']);
    });
    
    Route::middleware(['permission:RESTORE_PRODUCT'])->group(function () {
        Route::patch('/{uuid}/restore', [ProductController::class, 'restore'])->whereUuid('uuid');
    });
});
