<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Web\InsuranceCompanyPageController;
use Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api\InsuranceCompanyController;
use Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api\InsuranceCompanyExportController;

// Inertia Pages
Route::get('/', [InsuranceCompanyPageController::class, 'index'])->name('insurance-companies.index')->middleware('permission:READ_INSURANCE_COMPANY');
Route::get('/create', [InsuranceCompanyPageController::class, 'create'])->name('insurance-companies.create')->middleware('permission:CREATE_INSURANCE_COMPANY');
Route::get('/{uuid}', [InsuranceCompanyPageController::class, 'show'])->name('insurance-companies.show')->whereUuid('uuid')->middleware('permission:READ_INSURANCE_COMPANY');
Route::get('/{uuid}/edit', [InsuranceCompanyPageController::class, 'edit'])->name('insurance-companies.edit')->whereUuid('uuid')->middleware('permission:UPDATE_INSURANCE_COMPANY');

// JSON Data endpoints (Internal for React Query)
Route::prefix('data')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/', [InsuranceCompanyController::class, 'index'])->middleware('permission:READ_INSURANCE_COMPANY');
        Route::post('/', [InsuranceCompanyController::class, 'store'])->middleware('permission:CREATE_INSURANCE_COMPANY');
        Route::get('/export', [InsuranceCompanyExportController::class, '__invoke'])->middleware('permission:READ_INSURANCE_COMPANY');
        Route::get('/{uuid}', [InsuranceCompanyController::class, 'show'])->whereUuid('uuid')->middleware('permission:READ_INSURANCE_COMPANY');
        Route::put('/{uuid}', [InsuranceCompanyController::class, 'update'])->whereUuid('uuid')->middleware('permission:UPDATE_INSURANCE_COMPANY');
        Route::delete('/{uuid}', [InsuranceCompanyController::class, 'destroy'])->whereUuid('uuid')->middleware('permission:DELETE_INSURANCE_COMPANY');
        Route::patch('/{uuid}/restore', [InsuranceCompanyController::class, 'restore'])->whereUuid('uuid')->middleware('permission:RESTORE_INSURANCE_COMPANY');
        Route::post('/bulk-delete', [InsuranceCompanyController::class, 'bulkDelete'])->middleware('permission:DELETE_INSURANCE_COMPANY');
    });
});
