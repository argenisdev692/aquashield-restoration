<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AICampaigns\Infrastructure\Http\Controllers\Api\AdminCampaignController;
use Modules\AICampaigns\Infrastructure\Http\Controllers\Web\CampaignPageController;

Route::get('/', [CampaignPageController::class, 'index'])->name('ai-campaigns.index')->middleware('permission:VIEW_CAMPAIGN');
Route::get('/create', [CampaignPageController::class, 'create'])->name('ai-campaigns.create')->middleware('permission:CREATE_CAMPAIGN');
Route::get('/{uuid}', [CampaignPageController::class, 'show'])->name('ai-campaigns.show')->whereUuid('uuid')->middleware('permission:VIEW_CAMPAIGN');

Route::prefix('data')->group(function (): void {
    Route::prefix('admin')->group(function (): void {
        Route::get('/', [AdminCampaignController::class, 'index'])->name('ai-campaigns.data.index')->middleware('permission:VIEW_CAMPAIGN');
        Route::post('/', [AdminCampaignController::class, 'store'])->name('ai-campaigns.data.store')->middleware('permission:CREATE_CAMPAIGN');
        Route::post('/generate', [AdminCampaignController::class, 'generate'])->name('ai-campaigns.data.generate')->middleware('permission:CREATE_CAMPAIGN');
        Route::post('/bulk-delete', [AdminCampaignController::class, 'bulkDelete'])->name('ai-campaigns.data.bulk-delete')->middleware('permission:DELETE_CAMPAIGN');
        Route::get('/{uuid}', [AdminCampaignController::class, 'show'])->name('ai-campaigns.data.show')->whereUuid('uuid')->middleware('permission:VIEW_CAMPAIGN');
        Route::put('/{uuid}', [AdminCampaignController::class, 'update'])->name('ai-campaigns.data.update')->whereUuid('uuid')->middleware('permission:UPDATE_CAMPAIGN');
        Route::delete('/{uuid}', [AdminCampaignController::class, 'destroy'])->name('ai-campaigns.data.destroy')->whereUuid('uuid')->middleware('permission:DELETE_CAMPAIGN');
        Route::patch('/{uuid}/restore', [AdminCampaignController::class, 'restore'])->name('ai-campaigns.data.restore')->whereUuid('uuid')->middleware('permission:DELETE_CAMPAIGN');
    });
});
