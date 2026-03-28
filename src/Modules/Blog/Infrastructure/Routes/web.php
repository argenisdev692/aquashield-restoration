<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Blog\Infrastructure\Http\Controllers\Api\AdminBlogCategoryController;
use Modules\Blog\Infrastructure\Http\Controllers\Api\BlogCategoryExportController;
use Modules\Blog\Infrastructure\Http\Controllers\Web\BlogCategoryPageController;

/**
 * Blog Categories Context — Web routes (Inertia pages + JSON endpoints for React Query).
 *
 * Prefix: /blog-categories (applied by ServiceProvider).
 * Middleware: web, auth (applied by ServiceProvider).
 */

// ── Inertia Pages ──
Route::get('/', [BlogCategoryPageController::class, 'index'])->name('blog-categories.index');
Route::get('/create', [BlogCategoryPageController::class, 'create'])->name('blog-categories.create');
Route::get('/{uuid}', [BlogCategoryPageController::class, 'show'])->name('blog-categories.show')->whereUuid('uuid');
Route::get('/{uuid}/edit', [BlogCategoryPageController::class, 'edit'])->name('blog-categories.edit')->whereUuid('uuid');

// ── JSON Endpoints for React Query (Internal Web API) ──
Route::prefix('data')->group(function (): void {
    Route::prefix('admin')->group(function (): void {
        Route::get('/export', BlogCategoryExportController::class)->name('blog-categories.data.export')->middleware('permission:VIEW_BLOG_CATEGORY');
        Route::get('/', [AdminBlogCategoryController::class, 'index'])->name('blog-categories.data.index')->middleware('permission:VIEW_BLOG_CATEGORY');
        Route::post('/', [AdminBlogCategoryController::class, 'store'])->name('blog-categories.data.store')->middleware('permission:CREATE_BLOG_CATEGORY');
        Route::get('/{uuid}', [AdminBlogCategoryController::class, 'show'])->name('blog-categories.data.show')->whereUuid('uuid')->middleware('permission:VIEW_BLOG_CATEGORY');
        Route::put('/{uuid}', [AdminBlogCategoryController::class, 'update'])->name('blog-categories.data.update')->whereUuid('uuid')->middleware('permission:UPDATE_BLOG_CATEGORY');
        Route::delete('/{uuid}', [AdminBlogCategoryController::class, 'destroy'])->name('blog-categories.data.destroy')->whereUuid('uuid')->middleware('permission:DELETE_BLOG_CATEGORY');
        Route::patch('/{uuid}/restore', [AdminBlogCategoryController::class, 'restore'])->name('blog-categories.data.restore')->whereUuid('uuid')->middleware('permission:RESTORE_BLOG_CATEGORY');
        Route::post('/bulk-delete', [AdminBlogCategoryController::class, 'bulkDelete'])->name('blog-categories.data.bulk-delete')->middleware('permission:DELETE_BLOG_CATEGORY');
    });
});
