<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\EmailData\Infrastructure\Http\Controllers\Api\EmailDataController;
use Modules\EmailData\Infrastructure\Http\Controllers\Web\EmailDataPageController;

Route::middleware(['permission:READ_EMAIL_DATA'])->group(function (): void {
    Route::get('/email-data', [EmailDataPageController::class, 'index']);
    Route::get('/email-data/{uuid}', [EmailDataPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_EMAIL_DATA'])->group(function (): void {
    Route::get('/email-data/create', [EmailDataPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_EMAIL_DATA'])->group(function (): void {
    Route::get('/email-data/{uuid}/edit', [EmailDataPageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/email-data/data/admin')->group(function (): void {
    Route::middleware(['permission:READ_EMAIL_DATA'])->group(function (): void {
        Route::get('/', [EmailDataController::class, 'index']);
        Route::get('/export', [EmailDataController::class, 'export']);
        Route::get('/{uuid}', [EmailDataController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_EMAIL_DATA'])->group(function (): void {
        Route::post('/', [EmailDataController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_EMAIL_DATA'])->group(function (): void {
        Route::put('/{uuid}', [EmailDataController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_EMAIL_DATA'])->group(function (): void {
        Route::delete('/{uuid}', [EmailDataController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [EmailDataController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_EMAIL_DATA'])->group(function (): void {
        Route::patch('/{uuid}/restore', [EmailDataController::class, 'restore'])->whereUuid('uuid');
    });
});
