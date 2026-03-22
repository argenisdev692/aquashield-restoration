<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Controllers\Api\DocumentTemplateAllianceController;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Controllers\Api\DocumentTemplateAllianceExportController;

Route::get('/export', DocumentTemplateAllianceExportController::class);
Route::get('/', [DocumentTemplateAllianceController::class, 'index']);
Route::post('/', [DocumentTemplateAllianceController::class, 'store']);
Route::get('/{uuid}', [DocumentTemplateAllianceController::class, 'show'])->whereUuid('uuid');
Route::post('/{uuid}', [DocumentTemplateAllianceController::class, 'update'])->whereUuid('uuid');
Route::delete('/{uuid}', [DocumentTemplateAllianceController::class, 'destroy'])->whereUuid('uuid');
Route::post('/bulk-delete', [DocumentTemplateAllianceController::class, 'bulkDelete']);
