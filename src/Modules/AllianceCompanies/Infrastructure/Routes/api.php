<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AllianceCompanies\Infrastructure\Http\Controllers\Api\AllianceCompanyController;

// External API (Sanctum)
Route::get('/', [AllianceCompanyController::class, 'index']);
Route::post('/', [AllianceCompanyController::class, 'store']);
Route::get('/{uuid}', [AllianceCompanyController::class, 'show'])->whereUuid('uuid');
Route::put('/{uuid}', [AllianceCompanyController::class, 'update'])->whereUuid('uuid');
Route::delete('/{uuid}', [AllianceCompanyController::class, 'destroy'])->whereUuid('uuid');
Route::patch('/{uuid}/restore', [AllianceCompanyController::class, 'restore'])->whereUuid('uuid');
