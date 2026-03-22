<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Portfolios\Infrastructure\Http\Controllers\Api\PortfolioController;
use Src\Modules\Portfolios\Infrastructure\Http\Controllers\Api\PortfolioExportController;
use Src\Modules\Portfolios\Infrastructure\Http\Controllers\Web\PortfolioPageController;

Route::middleware(['permission:VIEW_PORTFOLIO'])->group(function (): void {
    Route::get('/portfolios', [PortfolioPageController::class, 'index']);
    Route::get('/portfolios/{uuid}', [PortfolioPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_PORTFOLIO'])->group(function (): void {
    Route::get('/portfolios/create', [PortfolioPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_PORTFOLIO'])->group(function (): void {
    Route::get('/portfolios/{uuid}/edit', [PortfolioPageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/portfolios/data/admin')->group(function (): void {
    Route::middleware(['permission:VIEW_PORTFOLIO'])->group(function (): void {
        Route::get('/', [PortfolioController::class, 'index']);
        Route::get('/export', PortfolioExportController::class);
        Route::get('/project-types', [PortfolioController::class, 'projectTypes']);
        Route::get('/{uuid}', [PortfolioController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_PORTFOLIO'])->group(function (): void {
        Route::post('/', [PortfolioController::class, 'store']);
        Route::post('/{uuid}/images', [PortfolioController::class, 'uploadImage'])->whereUuid('uuid');
    });

    Route::middleware(['permission:UPDATE_PORTFOLIO'])->group(function (): void {
        Route::put('/{uuid}', [PortfolioController::class, 'update'])->whereUuid('uuid');
        Route::put('/{uuid}/images/reorder', [PortfolioController::class, 'reorderImages'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_PORTFOLIO'])->group(function (): void {
        Route::delete('/{uuid}', [PortfolioController::class, 'destroy'])->whereUuid('uuid');
        Route::delete('/{uuid}/images/{imageUuid}', [PortfolioController::class, 'deleteImage'])->whereUuid('uuid');
        Route::post('/bulk-delete', [PortfolioController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_PORTFOLIO'])->group(function (): void {
        Route::patch('/{uuid}/restore', [PortfolioController::class, 'restore'])->whereUuid('uuid');
    });
});
