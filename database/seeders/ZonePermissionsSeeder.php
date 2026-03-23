<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class ZonePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'VIEW_ZONE',
            'CREATE_ZONE',
            'UPDATE_ZONE',
            'DELETE_ZONE',
            'RESTORE_ZONE',
        ];

        foreach ($permissions as $name) {
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

        $superAdminWeb = Role::firstOrCreate(
            ['name' => 'SUPER_ADMIN', 'guard_name' => 'web'],
            ['uuid' => Uuid::uuid4()->toString()]
        );

        $superAdminSanctum = Role::firstOrCreate(
            ['name' => 'SUPER_ADMIN', 'guard_name' => 'sanctum'],
            ['uuid' => Uuid::uuid4()->toString()]
        );

        $superAdminWeb->givePermissionTo($permissions);
        $superAdminSanctum->givePermissionTo($permissions);
    }
}
