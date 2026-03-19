<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AllianceCompanies\Infrastructure\Http\Controllers\Api\AllianceCompanyController;
use Modules\AllianceCompanies\Infrastructure\Http\Controllers\Web\AllianceCompanyPageController;

Route::middleware(['permission:READ_ALLIANCE_COMPANY'])->group(function (): void {
    Route::get('/', [AllianceCompanyPageController::class, 'index'])->name('alliance-companies.index');
    Route::get('/{uuid}', [AllianceCompanyPageController::class, 'show'])->whereUuid('uuid')->name('alliance-companies.show');
});

Route::middleware(['permission:CREATE_ALLIANCE_COMPANY'])->group(function (): void {
    Route::get('/create', [AllianceCompanyPageController::class, 'create'])->name('alliance-companies.create');
});

Route::middleware(['permission:UPDATE_ALLIANCE_COMPANY'])->group(function (): void {
    Route::get('/{uuid}/edit', [AllianceCompanyPageController::class, 'edit'])->whereUuid('uuid')->name('alliance-companies.edit');
});

Route::prefix('data/admin')->group(function (): void {
    Route::middleware(['permission:READ_ALLIANCE_COMPANY'])->group(function (): void {
        Route::get('/', [AllianceCompanyController::class, 'index']);
        Route::get('/{uuid}', [AllianceCompanyController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_ALLIANCE_COMPANY'])->group(function (): void {
        Route::post('/', [AllianceCompanyController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_ALLIANCE_COMPANY'])->group(function (): void {
        Route::put('/{uuid}', [AllianceCompanyController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_ALLIANCE_COMPANY'])->group(function (): void {
        Route::delete('/{uuid}', [AllianceCompanyController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [AllianceCompanyController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_ALLIANCE_COMPANY'])->group(function (): void {
        Route::patch('/{uuid}/restore', [AllianceCompanyController::class, 'restore'])->whereUuid('uuid');
    });
});
