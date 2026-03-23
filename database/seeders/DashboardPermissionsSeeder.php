<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class DashboardPermissionsSeeder extends Seeder
{
    private const PERMISSIONS = [
        'VIEW_DASHBOARD_KANBAN',
    ];

    private const ROLES_WITH_ACCESS = [
        'SUPER_ADMIN',
        'ADMINISTRADOR',
        'MANAGER',
        'COLLECTIONS',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['uuid' => Uuid::uuid4()->toString()]
            );

            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'sanctum'],
                ['uuid' => Uuid::uuid4()->toString()]
            );
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::ROLES_WITH_ACCESS as $roleName) {
            $roleWeb = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['uuid' => Uuid::uuid4()->toString()]
            );

            $roleSanctum = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'sanctum'],
                ['uuid' => Uuid::uuid4()->toString()]
            );

            $roleWeb->givePermissionTo(self::PERMISSIONS);
            $roleSanctum->givePermissionTo(self::PERMISSIONS);
        }
    }
}
