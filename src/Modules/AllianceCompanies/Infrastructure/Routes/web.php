<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AllianceCompanies\Infrastructure\Http\Controllers\Web\AllianceCompanyPageController;
use Modules\AllianceCompanies\Infrastructure\Http\Controllers\Api\AllianceCompanyController;

// Inertia Pages
Route::get('/', [AllianceCompanyPageController::class, 'index'])->name('alliance-companies.index');
Route::get('/create', [AllianceCompanyPageController::class, 'create'])->name('alliance-companies.create');
Route::get('/{uuid}', [AllianceCompanyPageController::class, 'show'])->name('alliance-companies.show')->whereUuid('uuid');
Route::get('/{uuid}/edit', [AllianceCompanyPageController::class, 'edit'])->name('alliance-companies.edit')->whereUuid('uuid');

// JSON Data endpoints (Internal for React Query)
Route::prefix('data')->group(function () {
    Route::get('/', [AllianceCompanyController::class, 'index']);
    Route::post('/', [AllianceCompanyController::class, 'store']);
    Route::get('/{uuid}', [AllianceCompanyController::class, 'show'])->whereUuid('uuid');
    Route::put('/{uuid}', [AllianceCompanyController::class, 'update'])->whereUuid('uuid');
    Route::delete('/{uuid}', [AllianceCompanyController::class, 'destroy'])->whereUuid('uuid');
    Route::patch('/{uuid}/restore', [AllianceCompanyController::class, 'restore'])->whereUuid('uuid');
});
