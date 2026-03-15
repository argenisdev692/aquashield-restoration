<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\ContactSupports\Infrastructure\Http\Controllers\Api\ContactSupportController;
use Src\Modules\ContactSupports\Infrastructure\Http\Controllers\Web\ContactSupportPageController;

Route::middleware(['permission:READ_CONTACT_SUPPORT'])->group(function (): void {
    Route::get('/contact-supports', [ContactSupportPageController::class, 'index']);
    Route::get('/contact-supports/{uuid}', [ContactSupportPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_CONTACT_SUPPORT'])->group(function (): void {
    Route::get('/contact-supports/create', [ContactSupportPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_CONTACT_SUPPORT'])->group(function (): void {
    Route::get('/contact-supports/{uuid}/edit', [ContactSupportPageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/contact-supports/data/admin')->group(function (): void {
    Route::middleware(['permission:READ_CONTACT_SUPPORT'])->group(function (): void {
        Route::get('/', [ContactSupportController::class, 'index']);
        Route::get('/{uuid}', [ContactSupportController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_CONTACT_SUPPORT'])->group(function (): void {
        Route::post('/', [ContactSupportController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_CONTACT_SUPPORT'])->group(function (): void {
        Route::put('/{uuid}', [ContactSupportController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_CONTACT_SUPPORT'])->group(function (): void {
        Route::delete('/{uuid}', [ContactSupportController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [ContactSupportController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_CONTACT_SUPPORT'])->group(function (): void {
        Route::patch('/{uuid}/restore', [ContactSupportController::class, 'restore'])->whereUuid('uuid');
    });
});
