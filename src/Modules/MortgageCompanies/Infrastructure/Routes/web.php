<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\MortgageCompanies\Infrastructure\Http\Controllers\Api\MortgageCompanyController;
use Modules\MortgageCompanies\Infrastructure\Http\Controllers\Api\MortgageCompanyExportController;
use Modules\MortgageCompanies\Infrastructure\Http\Controllers\Web\MortgageCompanyPageController;

Route::middleware(['permission:READ_MORTGAGE_COMPANY'])->group(function (): void {
    Route::get('/', [MortgageCompanyPageController::class, 'index'])->name('mortgage-companies.index');
    Route::get('/{uuid}', [MortgageCompanyPageController::class, 'show'])->whereUuid('uuid')->name('mortgage-companies.show');
});

Route::middleware(['permission:CREATE_MORTGAGE_COMPANY'])->group(function (): void {
    Route::get('/create', [MortgageCompanyPageController::class, 'create'])->name('mortgage-companies.create');
});

Route::middleware(['permission:UPDATE_MORTGAGE_COMPANY'])->group(function (): void {
    Route::get('/{uuid}/edit', [MortgageCompanyPageController::class, 'edit'])->whereUuid('uuid')->name('mortgage-companies.edit');
});

Route::prefix('data/admin')->group(function (): void {
    Route::middleware(['permission:READ_MORTGAGE_COMPANY'])->group(function (): void {
        Route::get('/export', MortgageCompanyExportController::class);
        Route::get('/', [MortgageCompanyController::class, 'index']);
        Route::get('/{uuid}', [MortgageCompanyController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_MORTGAGE_COMPANY'])->group(function (): void {
        Route::post('/', [MortgageCompanyController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_MORTGAGE_COMPANY'])->group(function (): void {
        Route::put('/{uuid}', [MortgageCompanyController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_MORTGAGE_COMPANY'])->group(function (): void {
        Route::delete('/{uuid}', [MortgageCompanyController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [MortgageCompanyController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_MORTGAGE_COMPANY'])->group(function (): void {
        Route::patch('/{uuid}/restore', [MortgageCompanyController::class, 'restore'])->whereUuid('uuid');
    });
});
