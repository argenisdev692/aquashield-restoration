<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class FilesEsxPermissionsSeeder extends Seeder
{
    private const array ALL_PERMISSIONS = [
        'VIEW_FILES_ESX',
        'CREATE_FILES_ESX',
        'UPDATE_FILES_ESX',
        'DELETE_FILES_ESX',
        'ASSIGN_FILES_ESX',
    ];

    private const array PUBLIC_ADJUSTER_PERMISSIONS = [
        'VIEW_FILES_ESX',
        'ASSIGN_FILES_ESX',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['web', 'sanctum'] as $guard) {
            foreach (self::ALL_PERMISSIONS as $permissionName) {
                Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => $guard],
                    ['uuid' => Uuid::uuid4()->toString()],
                );
            }
        }

        foreach (['web', 'sanctum'] as $guard) {
            $superAdmin = Role::where('name', 'SUPER_ADMIN')
                ->where('guard_name', $guard)
                ->first();

            if ($superAdmin !== null) {
                $superAdmin->givePermissionTo(
                    Permission::whereIn('name', self::ALL_PERMISSIONS)
                        ->where('guard_name', $guard)
                        ->get(),
                );
            }

            $publicAdjuster = Role::where('name', 'PUBLIC_ADJUSTER')
                ->where('guard_name', $guard)
                ->first();

            if ($publicAdjuster !== null) {
                $publicAdjuster->givePermissionTo(
                    Permission::whereIn('name', self::PUBLIC_ADJUSTER_PERMISSIONS)
                        ->where('guard_name', $guard)
                        ->get(),
                );
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
