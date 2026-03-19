<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api\InsuranceCompanyController;

// External API (Sanctum)
Route::get('/', [InsuranceCompanyController::class, 'index']);
Route::post('/', [InsuranceCompanyController::class, 'store']);
Route::get('/{uuid}', [InsuranceCompanyController::class, 'show'])->whereUuid('uuid');
Route::put('/{uuid}', [InsuranceCompanyController::class, 'update'])->whereUuid('uuid');
Route::delete('/{uuid}', [InsuranceCompanyController::class, 'destroy'])->whereUuid('uuid');
Route::patch('/{uuid}/restore', [InsuranceCompanyController::class, 'restore'])->whereUuid('uuid');
