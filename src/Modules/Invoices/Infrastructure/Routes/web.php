<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Modules\Invoices\Infrastructure\Http\Controllers\Api\InvoiceController;
use Src\Modules\Invoices\Infrastructure\Http\Controllers\Api\InvoiceExportController;
use Src\Modules\Invoices\Infrastructure\Http\Controllers\Api\InvoiceSinglePdfController;
use Src\Modules\Invoices\Infrastructure\Http\Controllers\Web\InvoicePageController;

Route::middleware(['permission:VIEW_INVOICE'])->group(function (): void {
    Route::get('/invoices', [InvoicePageController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{uuid}', [InvoicePageController::class, 'show'])->name('invoices.show')->whereUuid('uuid');
});

Route::middleware(['permission:CREATE_INVOICE'])->group(function (): void {
    Route::get('/invoices/create', [InvoicePageController::class, 'create'])->name('invoices.create');
});

Route::middleware(['permission:UPDATE_INVOICE'])->group(function (): void {
    Route::get('/invoices/{uuid}/edit', [InvoicePageController::class, 'edit'])->name('invoices.edit')->whereUuid('uuid');
});

Route::prefix('/invoices/data/admin')->group(function (): void {
    Route::middleware(['permission:VIEW_INVOICE'])->group(function (): void {
        Route::get('/export', InvoiceExportController::class);
        Route::get('/{uuid}/invoice-pdf', InvoiceSinglePdfController::class)->whereUuid('uuid');
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/{uuid}', [InvoiceController::class, 'show'])->whereUuid('uuid');
    });

    Route::middleware(['permission:CREATE_INVOICE'])->group(function (): void {
        Route::post('/', [InvoiceController::class, 'store']);
    });

    Route::middleware(['permission:UPDATE_INVOICE'])->group(function (): void {
        Route::put('/{uuid}', [InvoiceController::class, 'update'])->whereUuid('uuid');
    });

    Route::middleware(['permission:DELETE_INVOICE'])->group(function (): void {
        Route::delete('/{uuid}', [InvoiceController::class, 'destroy'])->whereUuid('uuid');
        Route::post('/bulk-delete', [InvoiceController::class, 'bulkDelete']);
    });

    Route::middleware(['permission:RESTORE_INVOICE'])->group(function (): void {
        Route::patch('/{uuid}/restore', [InvoiceController::class, 'restore'])->whereUuid('uuid');
    });
});
