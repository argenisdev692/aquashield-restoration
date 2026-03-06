<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Blog\Infrastructure\Http\Controllers\Api\AdminPostController;
use Modules\Blog\Infrastructure\Http\Controllers\Api\PostExportController;
use Modules\Blog\Infrastructure\Http\Controllers\Web\PostPageController;

Route::get('/', [PostPageController::class, 'index'])->name('posts.index')->middleware('permission:VIEW_POST');
Route::get('/create', [PostPageController::class, 'create'])->name('posts.create')->middleware('permission:CREATE_POST');
Route::get('/{uuid}', [PostPageController::class, 'show'])->name('posts.show')->whereUuid('uuid')->middleware('permission:VIEW_POST');
Route::get('/{uuid}/edit', [PostPageController::class, 'edit'])->name('posts.edit')->whereUuid('uuid')->middleware('permission:UPDATE_POST');

Route::prefix('data')->group(function (): void {
    Route::prefix('admin')->group(function (): void {
        Route::get('/export', PostExportController::class)->name('posts.data.export')->middleware('permission:VIEW_POST');
        Route::get('/', [AdminPostController::class, 'index'])->name('posts.data.index')->middleware('permission:VIEW_POST');
        Route::post('/', [AdminPostController::class, 'store'])->name('posts.data.store')->middleware('permission:CREATE_POST');
        Route::get('/{uuid}', [AdminPostController::class, 'show'])->name('posts.data.show')->whereUuid('uuid')->middleware('permission:VIEW_POST');
        Route::put('/{uuid}', [AdminPostController::class, 'update'])->name('posts.data.update')->whereUuid('uuid')->middleware('permission:UPDATE_POST');
        Route::delete('/{uuid}', [AdminPostController::class, 'destroy'])->name('posts.data.destroy')->whereUuid('uuid')->middleware('permission:DELETE_POST');
        Route::patch('/{uuid}/restore', [AdminPostController::class, 'restore'])->name('posts.data.restore')->whereUuid('uuid')->middleware('permission:DELETE_POST');
        Route::post('/bulk-delete', [AdminPostController::class, 'bulkDelete'])->name('posts.data.bulk-delete')->middleware('permission:DELETE_POST');
    });
});
