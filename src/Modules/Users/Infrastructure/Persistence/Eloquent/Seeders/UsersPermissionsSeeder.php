<?php

declare(strict_types=1);

namespace Modules\Users\Infrastructure\Persistence\Eloquent\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Spatie\Permission\PermissionRegistrar;

/**
 * UsersPermissionsSeeder — Creates roles + permissions for the Users module.
 *
 * ── Naming Convention ──
 * Role:       Users
 * Permissions: VIEW_USERS, CREATE_USERS, UPDATE_USERS, DELETE_USERS
 */
final class UsersPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ──
        $permissions = [
            'VIEW_USERS',
            'CREATE_USERS',
            'UPDATE_USERS',
            'DELETE_USERS',
        ];

        foreach ($permissions as $permission) {
            PermissionEloquentModel::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['uuid' => (string) Str::uuid()],
            );
        }

        // ── Role ──
        $role = RoleEloquentModel::firstOrCreate(
            ['name' => 'Users', 'guard_name' => 'web'],
            ['uuid' => (string) Str::uuid()],
        );
        $role->syncPermissions($permissions);

        // ── Also give Super Admin all permissions ──
        $superAdmin = RoleEloquentModel::firstOrCreate(
            ['name' => 'SUPER_ADMIN', 'guard_name' => 'web'],
            ['uuid' => (string) Str::uuid()],
        );
        $superAdmin->givePermissionTo($permissions);
    }
}
