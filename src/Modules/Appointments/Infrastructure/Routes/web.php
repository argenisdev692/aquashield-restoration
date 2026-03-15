<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Appointments\Infrastructure\Http\Controllers\Api\AppointmentController;
use Src\Modules\Appointments\Infrastructure\Http\Controllers\Web\AppointmentPageController;

Route::middleware(['permission:READ_APPOINTMENT'])->group(function (): void {
    Route::get('/appointments', [AppointmentPageController::class, 'index']);
    Route::get('/appointments/{uuid}', [AppointmentPageController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_APPOINTMENT'])->group(function (): void {
    Route::get('/appointments/create', [AppointmentPageController::class, 'create']);
});

Route::middleware(['permission:UPDATE_APPOINTMENT'])->group(function (): void {
    Route::get('/appointments/{uuid}/edit', [AppointmentPageController::class, 'edit'])->whereUuid('uuid');
});

Route::prefix('/appointments/data/admin')->group(function (): void {
    Route::middleware(['permission:READ_APPOINTMENT'])->group(function (): void {
        Route::get('/', [AppointmentController::class, 'index']);
        Route::get('/{uuid}', [AppointmentController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_APPOINTMENT'])->group(function (): void {
        Route::post('/', [AppointmentController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_APPOINTMENT'])->group(function (): void {
        Route::put('/{uuid}', [AppointmentController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_APPOINTMENT'])->group(function (): void {
        Route::delete('/{uuid}', [AppointmentController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [AppointmentController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_APPOINTMENT'])->group(function (): void {
        Route::patch('/{uuid}/restore', [AppointmentController::class, 'restore'])->whereUuid('uuid');
    });
});
