<?php

declare(strict_types=1);

use Src\Contexts\CompanyData\Infrastructure\Adapters\Http\Controllers\Api\CompanyDataController;
use Src\Contexts\CompanyData\Infrastructure\Adapters\Http\Controllers\Api\CompanyDataExportController;
use Illuminate\Support\Facades\Route;

Route::get('/export', CompanyDataExportController::class)->name('company-data.export');

// The typical resource routes via explicit binding for UUIDs
Route::get('/', [CompanyDataController::class, 'index'])->name('company-data.index');
Route::post('/', [CompanyDataController::class, 'store'])->name('company-data.store');
Route::get('/{uuid}', [CompanyDataController::class, 'show'])->name('company-data.show');
Route::put('/{uuid}', [CompanyDataController::class, 'update'])->name('company-data.update');
Route::delete('/{uuid}', [CompanyDataController::class, 'destroy'])->name('company-data.destroy');
Route::patch('/{uuid}/restore', [CompanyDataController::class, 'restore'])->name('company-data.restore');
