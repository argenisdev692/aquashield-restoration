<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AccessControl\Infrastructure\Http\Controllers\Api\AdminAccessControlController;
use Modules\AccessControl\Infrastructure\Http\Controllers\Web\AccessControlPageController;

Route::get('/', [AccessControlPageController::class, 'index'])->name('permissions.index')->middleware('permission:READ_PERMISSION|VIEW_USERS');

Route::prefix('data')->group(function (): void {
    Route::prefix('admin')->group(function (): void {
        Route::get('/permissions', [AdminAccessControlController::class, 'permissions'])->name('permissions.data.permissions')->middleware('permission:READ_PERMISSION|VIEW_USERS');
        Route::post('/permissions', [AdminAccessControlController::class, 'createPermission'])->name('permissions.data.permissions.store')->middleware('permission:CREATE_PERMISSION');
        Route::get('/roles', [AdminAccessControlController::class, 'roles'])->name('permissions.data.roles')->middleware('permission:READ_ROLE|READ_PERMISSION|VIEW_USERS');
        Route::get('/roles/{uuid}', [AdminAccessControlController::class, 'role'])->name('permissions.data.roles.show')->whereUuid('uuid')->middleware('permission:READ_ROLE|READ_PERMISSION');
        Route::put('/roles/{uuid}/permissions', [AdminAccessControlController::class, 'syncRolePermissions'])->name('permissions.data.roles.sync')->whereUuid('uuid')->middleware('permission:UPDATE_ROLE|UPDATE_PERMISSION');
        Route::get('/users', [AdminAccessControlController::class, 'users'])->name('permissions.data.users')->middleware('permission:VIEW_USERS|READ_PERMISSION');
        Route::get('/users/{uuid}', [AdminAccessControlController::class, 'user'])->name('permissions.data.users.show')->whereUuid('uuid')->middleware('permission:VIEW_USERS|READ_PERMISSION');
        Route::put('/users/{uuid}/access', [AdminAccessControlController::class, 'syncUserAccess'])->name('permissions.data.users.sync')->whereUuid('uuid')->middleware('permission:UPDATE_USERS|UPDATE_PERMISSION');
    });
});
