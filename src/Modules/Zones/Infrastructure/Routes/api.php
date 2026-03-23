<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Zones\Infrastructure\Http\Controllers\Api\ZoneController;
use Src\Modules\Zones\Infrastructure\Http\Controllers\Api\ZoneExportController;

Route::middleware(['permission:VIEW_ZONE'])->group(function (): void {
    Route::get('/export', ZoneExportController::class);
    Route::get('/', [ZoneController::class, 'index']);
    Route::get('/{uuid}', [ZoneController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_ZONE'])->group(function (): void {
    Route::post('/', [ZoneController::class, 'store']);
});

Route::middleware(['permission:UPDATE_ZONE'])->group(function (): void {
    Route::put('/{uuid}', [ZoneController::class, 'update'])->whereUuid('uuid');
});

Route::middleware(['permission:DELETE_ZONE'])->group(function (): void {
    Route::delete('/{uuid}', [ZoneController::class, 'destroy'])->whereUuid('uuid');
    Route::post('/bulk-delete', [ZoneController::class, 'bulkDelete']);
});

Route::middleware(['permission:RESTORE_ZONE'])->group(function (): void {
    Route::patch('/{uuid}/restore', [ZoneController::class, 'restore'])->whereUuid('uuid');
});
