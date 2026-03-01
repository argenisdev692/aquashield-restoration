<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\PublicCompanies\Infrastructure\Http\Controllers\Web\PublicCompanyPageController;
use Modules\PublicCompanies\Infrastructure\Http\Controllers\Api\PublicCompanyController;

// Inertia Pages
Route::get('/', [PublicCompanyPageController::class, 'index'])->name('public-companies.index');
Route::get('/create', [PublicCompanyPageController::class, 'create'])->name('public-companies.create');
Route::get('/{uuid}', [PublicCompanyPageController::class, 'show'])->name('public-companies.show')->whereUuid('uuid');
Route::get('/{uuid}/edit', [PublicCompanyPageController::class, 'edit'])->name('public-companies.edit')->whereUuid('uuid');

// JSON Data endpoints (Internal for React Query)
Route::prefix('data')->group(function () {
    Route::get('/', [PublicCompanyController::class, 'index']);
    Route::post('/', [PublicCompanyController::class, 'store']);
    Route::get('/{uuid}', [PublicCompanyController::class, 'show'])->whereUuid('uuid');
    Route::put('/{uuid}', [PublicCompanyController::class, 'update'])->whereUuid('uuid');
    Route::delete('/{uuid}', [PublicCompanyController::class, 'destroy'])->whereUuid('uuid');
    Route::patch('/{uuid}/restore', [PublicCompanyController::class, 'restore'])->whereUuid('uuid');
});
