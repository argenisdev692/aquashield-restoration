<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class PropertyPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'VIEW_PROPERTY',
            'CREATE_PROPERTY',
            'UPDATE_PROPERTY',
            'DELETE_PROPERTY',
            'RESTORE_PROPERTY',
        ];

        foreach (['web', 'sanctum'] as $guard) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate([
                    'name'       => $permissionName,
                    'guard_name' => $guard,
                ], [
                    'uuid' => Uuid::uuid4()->toString(),
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
