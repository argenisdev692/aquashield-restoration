<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Contexts\Users\Infrastructure\Adapters\Http\Controllers\Api\UserController;

/**
 * Users Context — API routes.
 *
 * Prefix: /api/users (applied by ServiceProvider).
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/export', \Src\Contexts\Users\Infrastructure\Adapters\Http\Controllers\Api\UserExportController::class)->name('api.users.export');
    Route::get('/', [UserController::class, 'index'])->name('api.users.index');
    Route::get('/{uuid}', [UserController::class, 'show'])->name('api.users.show')->whereUuid('uuid');
    Route::post('/', [UserController::class, 'store'])->name('api.users.store');
    Route::put('/{uuid}', [UserController::class, 'update'])->name('api.users.update')->whereUuid('uuid');
    Route::delete('/{uuid}', [UserController::class, 'destroy'])->name('api.users.destroy')->whereUuid('uuid');
    Route::patch('/{uuid}/restore', [UserController::class, 'restore'])->name('api.users.restore')->whereUuid('uuid');

});
