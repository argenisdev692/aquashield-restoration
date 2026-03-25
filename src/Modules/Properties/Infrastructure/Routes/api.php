<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Properties\Infrastructure\Http\Controllers\Api\PropertyController;
use Src\Modules\Properties\Infrastructure\Http\Controllers\Api\PropertyExportController;

Route::middleware(['permission:VIEW_PROPERTY'])->group(function (): void {
    Route::get('/export', PropertyExportController::class);
    Route::get('/', [PropertyController::class, 'index']);
    Route::get('/{uuid}', [PropertyController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_PROPERTY'])->group(function (): void {
    Route::post('/', [PropertyController::class, 'store']);
});

Route::middleware(['permission:UPDATE_PROPERTY'])->group(function (): void {
    Route::put('/{uuid}', [PropertyController::class, 'update'])->whereUuid('uuid');
});

Route::middleware(['permission:DELETE_PROPERTY'])->group(function (): void {
    Route::delete('/{uuid}', [PropertyController::class, 'destroy'])->whereUuid('uuid');
    Route::post('/bulk-delete', [PropertyController::class, 'bulkDelete']);
});

Route::middleware(['permission:RESTORE_PROPERTY'])->group(function (): void {
    Route::patch('/{uuid}/restore', [PropertyController::class, 'restore'])->whereUuid('uuid');
});
