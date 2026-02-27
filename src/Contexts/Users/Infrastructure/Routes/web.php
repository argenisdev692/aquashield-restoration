<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Contexts\Users\Infrastructure\Adapters\Http\Controllers\Web\UserPageController;

/**
 * Users Context — Web routes (Inertia pages).
 *
 * Prefix: /users (applied by ServiceProvider).
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/', [UserPageController::class, 'index'])->name('users.index');
    Route::get('/create', [UserPageController::class, 'create'])->name('users.create');
    Route::get('/{uuid}', [UserPageController::class, 'show'])->name('users.show')->whereUuid('uuid');
    Route::get('/{uuid}/edit', [UserPageController::class, 'edit'])->name('users.edit')->whereUuid('uuid');
    Route::delete('/{uuid}', [UserPageController::class, 'destroy'])->name('users.destroy')->whereUuid('uuid');
    Route::patch('/{uuid}/restore', [UserPageController::class, 'restore'])->name('users.restore')->whereUuid('uuid');

});
