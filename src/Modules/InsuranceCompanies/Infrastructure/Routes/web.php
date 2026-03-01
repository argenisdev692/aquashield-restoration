<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Web\InsuranceCompanyPageController;
use Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api\InsuranceCompanyController;

// Inertia Pages
Route::get('/', [InsuranceCompanyPageController::class, 'index'])->name('insurance-companies.index');
Route::get('/create', [InsuranceCompanyPageController::class, 'create'])->name('insurance-companies.create');
Route::get('/{uuid}', [InsuranceCompanyPageController::class, 'show'])->name('insurance-companies.show')->whereUuid('uuid');
Route::get('/{uuid}/edit', [InsuranceCompanyPageController::class, 'edit'])->name('insurance-companies.edit')->whereUuid('uuid');

// JSON Data endpoints (Internal for React Query)
Route::prefix('data')->group(function () {
    Route::get('/', [InsuranceCompanyController::class, 'index']);
    Route::post('/', [InsuranceCompanyController::class, 'store']);
    Route::get('/{uuid}', [InsuranceCompanyController::class, 'show'])->whereUuid('uuid');
    Route::put('/{uuid}', [InsuranceCompanyController::class, 'update'])->whereUuid('uuid');
    Route::delete('/{uuid}', [InsuranceCompanyController::class, 'destroy'])->whereUuid('uuid');
    Route::patch('/{uuid}/restore', [InsuranceCompanyController::class, 'restore'])->whereUuid('uuid');
});
