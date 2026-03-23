<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\FilesEsx\Infrastructure\Http\Controllers\Api\FileEsxController;
use Src\Modules\FilesEsx\Infrastructure\Http\Controllers\Api\FileEsxExportController;
use Src\Modules\FilesEsx\Infrastructure\Http\Controllers\Web\FileEsxPageController;

Route::middleware(['permission:VIEW_FILES_ESX'])->group(function (): void {
    Route::get('/files-esx', [FileEsxPageController::class, 'index'])->name('files-esx.index');
    Route::get('/files-esx/{uuid}', [FileEsxPageController::class, 'show'])->name('files-esx.show')->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_FILES_ESX'])->group(function (): void {
    Route::get('/files-esx/create', [FileEsxPageController::class, 'create'])->name('files-esx.create');
});

Route::middleware(['permission:UPDATE_FILES_ESX'])->group(function (): void {
    Route::get('/files-esx/{uuid}/edit', [FileEsxPageController::class, 'edit'])->name('files-esx.edit')->whereUuid('uuid');
});

Route::prefix('/files-esx/data/admin')->group(function (): void {
    Route::middleware(['permission:VIEW_FILES_ESX'])->group(function (): void {
        Route::get('/export', FileEsxExportController::class);
        Route::get('/', [FileEsxController::class, 'index']);
        Route::get('/{uuid}', [FileEsxController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_FILES_ESX'])->group(function (): void {
        Route::post('/', [FileEsxController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_FILES_ESX'])->group(function (): void {
        Route::put('/{uuid}', [FileEsxController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:ASSIGN_FILES_ESX'])->group(function (): void {
        Route::post('/{uuid}/assign', [FileEsxController::class, 'assign'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_FILES_ESX'])->group(function (): void {
        Route::delete('/{uuid}', [FileEsxController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [FileEsxController::class, 'bulkDelete']);
    });
});
