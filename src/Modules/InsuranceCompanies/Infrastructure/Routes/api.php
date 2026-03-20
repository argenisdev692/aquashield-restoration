<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api\InsuranceCompanyController;
use Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api\InsuranceCompanyExportController;

// External API (Sanctum)
Route::middleware(['permission:READ_INSURANCE_COMPANY'])->group(function (): void {
    Route::get('/export', InsuranceCompanyExportController::class);
    Route::get('/', [InsuranceCompanyController::class, 'index']);
    Route::get('/{uuid}', [InsuranceCompanyController::class, 'show'])->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_INSURANCE_COMPANY'])->group(function (): void {
    Route::post('/', [InsuranceCompanyController::class, 'store']);
});

Route::middleware(['permission:UPDATE_INSURANCE_COMPANY'])->group(function (): void {
    Route::put('/{uuid}', [InsuranceCompanyController::class, 'update'])->whereUuid('uuid');
});

Route::middleware(['permission:DELETE_INSURANCE_COMPANY'])->group(function (): void {
    Route::delete('/{uuid}', [InsuranceCompanyController::class, 'destroy'])->whereUuid('uuid');
    Route::post('/bulk-delete', [InsuranceCompanyController::class, 'bulkDelete']);
});

Route::middleware(['permission:RESTORE_INSURANCE_COMPANY'])->group(function (): void {
    Route::patch('/{uuid}/restore', [InsuranceCompanyController::class, 'restore'])->whereUuid('uuid');
});
