<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class InvoicePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'VIEW_INVOICE',
            'CREATE_INVOICE',
            'UPDATE_INVOICE',
            'DELETE_INVOICE',
            'RESTORE_INVOICE',
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
