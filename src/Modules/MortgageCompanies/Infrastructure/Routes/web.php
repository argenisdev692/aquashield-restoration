<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\MortgageCompanies\Infrastructure\Http\Controllers\Api\MortgageCompanyController;
use Modules\MortgageCompanies\Infrastructure\Http\Controllers\Web\MortgageCompanyPageController;

// Inertia pages
Route::middleware(['auth'])->group(function () {
    Route::get('/mortgage-companies', [MortgageCompanyPageController::class, 'index'])->name('mortgage-companies.index');
    Route::get('/mortgage-companies/create', [MortgageCompanyPageController::class, 'create'])->name('mortgage-companies.create');
    Route::get('/mortgage-companies/{uuid}', [MortgageCompanyPageController::class, 'show'])->whereUuid('uuid')->name('mortgage-companies.show');
    Route::get('/mortgage-companies/{uuid}/edit', [MortgageCompanyPageController::class, 'edit'])->whereUuid('uuid')->name('mortgage-companies.edit');
});

// JSON data endpoints (used by TanStack Query)
Route::middleware(['auth', 'role:SUPER_ADMIN'])
    ->prefix('/mortgage-companies/data/admin')
    ->group(function () {
        Route::get('/', [MortgageCompanyController::class, 'index']);
        Route::post('/', [MortgageCompanyController::class, 'store']);
        Route::get('/{uuid}', [MortgageCompanyController::class, 'show'])->whereUuid('uuid');
        Route::put('/{uuid}', [MortgageCompanyController::class, 'update'])->whereUuid('uuid');
        Route::delete('/{uuid}', [MortgageCompanyController::class, 'destroy'])->whereUuid('uuid');
        Route::patch('/{uuid}/restore', [MortgageCompanyController::class, 'restore'])->whereUuid('uuid');
    });
