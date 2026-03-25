<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class CustomerPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'VIEW_CUSTOMER',
            'CREATE_CUSTOMER',
            'UPDATE_CUSTOMER',
            'DELETE_CUSTOMER',
            'RESTORE_CUSTOMER',
        ];

        foreach (['web', 'sanctum'] as $guard) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate([
                    'name'       => $permissionName,
                    'guard_name' => $guard,
                ]);
            }

            $superAdmin = Role::where('name', 'SUPER_ADMIN')
                ->where('guard_name', $guard)
                ->first();

            if ($superAdmin !== null) {
                $superAdmin->givePermissionTo(
                    Permission::where('guard_name', $guard)
                        ->whereIn('name', $permissions)
                        ->get(),
                );
            }
        }
    }
}
