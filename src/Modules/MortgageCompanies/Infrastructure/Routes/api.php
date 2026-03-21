<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\MortgageCompanies\Infrastructure\Http\Controllers\Api\MortgageCompanyController;

// External API (Sanctum)
Route::middleware(['permission:READ_MORTGAGE_COMPANY'])->group(function (): void {
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
