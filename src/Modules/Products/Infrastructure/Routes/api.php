<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Products\Infrastructure\Http\Controllers\Api\ProductController;

Route::middleware(['auth:sanctum', 'role:super-admin'])
    ->prefix('/api/products/admin')
    ->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{uuid}', [ProductController::class, 'show'])->whereUuid('uuid');
        Route::put('/{uuid}', [ProductController::class, 'update'])->whereUuid('uuid');
        Route::delete('/{uuid}', [ProductController::class, 'destroy'])->whereUuid('uuid');
        Route::patch('/{uuid}/restore', [ProductController::class, 'restore'])->whereUuid('uuid');
    });
