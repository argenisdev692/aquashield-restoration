<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\ServiceRequests\Infrastructure\Http\Controllers\Api\ServiceRequestController;

Route::middleware(['permission:READ_SERVICE_REQUEST'])->group(function (): void {
    Route::get('/', [ServiceRequestController::class, 'index']);
    Route::get('/{uuid}', [ServiceRequestController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_SERVICE_REQUEST'])->group(function (): void {
    Route::post('/', [ServiceRequestController::class, 'store']);
});

Route::middleware(['permission:UPDATE_SERVICE_REQUEST'])->group(function (): void {
    Route::put('/{uuid}', [ServiceRequestController::class, 'update'])->whereUuid('uuid');
});

Route::middleware(['permission:DELETE_SERVICE_REQUEST'])->group(function (): void {
    Route::delete('/{uuid}', [ServiceRequestController::class, 'destroy'])->whereUuid('uuid');
    Route::post('/bulk-delete', [ServiceRequestController::class, 'bulkDelete']);
});

Route::middleware(['permission:RESTORE_SERVICE_REQUEST'])->group(function (): void {
    Route::patch('/{uuid}/restore', [ServiceRequestController::class, 'restore'])->whereUuid('uuid');
});
