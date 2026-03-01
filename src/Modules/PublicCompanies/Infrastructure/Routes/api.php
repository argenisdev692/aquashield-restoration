<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\PublicCompanies\Infrastructure\Http\Controllers\Api\PublicCompanyController;

// External API (Sanctum)
Route::get('/', [PublicCompanyController::class, 'index']);
Route::post('/', [PublicCompanyController::class, 'store']);
Route::get('/{uuid}', [PublicCompanyController::class, 'show'])->whereUuid('uuid');
Route::put('/{uuid}', [PublicCompanyController::class, 'update'])->whereUuid('uuid');
Route::delete('/{uuid}', [PublicCompanyController::class, 'destroy'])->whereUuid('uuid');
Route::patch('/{uuid}/restore', [PublicCompanyController::class, 'restore'])->whereUuid('uuid');
