<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\PublicCompanies\Infrastructure\Http\Controllers\Api\PublicCompanyController;
use Modules\PublicCompanies\Infrastructure\Http\Controllers\Api\PublicCompanyExportController;
use Modules\PublicCompanies\Infrastructure\Http\Controllers\Web\PublicCompanyPageController;

Route::middleware(['permission:READ_PUBLIC_COMPANY'])->group(function (): void {
    Route::get('/public-companies', [PublicCompanyPageController::class, 'index'])->name('public-companies.index');
    Route::get('/public-companies/{uuid}', [PublicCompanyPageController::class, 'show'])->name('public-companies.show')->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_PUBLIC_COMPANY'])->group(function (): void {
    Route::get('/public-companies/create', [PublicCompanyPageController::class, 'create'])->name('public-companies.create');
});

Route::middleware(['permission:UPDATE_PUBLIC_COMPANY'])->group(function (): void {
    Route::get('/public-companies/{uuid}/edit', [PublicCompanyPageController::class, 'edit'])->name('public-companies.edit')->whereUuid('uuid');
});

Route::prefix('/public-companies/data/admin')->group(function (): void {
    Route::middleware(['permission:READ_PUBLIC_COMPANY'])->group(function (): void {
        Route::get('/export', PublicCompanyExportController::class);
        Route::get('/', [PublicCompanyController::class, 'index']);
        Route::get('/{uuid}', [PublicCompanyController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_PUBLIC_COMPANY'])->group(function (): void {
        Route::post('/', [PublicCompanyController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_PUBLIC_COMPANY'])->group(function (): void {
        Route::put('/{uuid}', [PublicCompanyController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_PUBLIC_COMPANY'])->group(function (): void {
        Route::delete('/{uuid}', [PublicCompanyController::class, 'destroy'])->whereUuid('uuid');
    });

    Route::middleware(['permission:RESTORE_PUBLIC_COMPANY'])->group(function (): void {
        Route::patch('/{uuid}/restore', [PublicCompanyController::class, 'restore'])->whereUuid('uuid');
    });
});
