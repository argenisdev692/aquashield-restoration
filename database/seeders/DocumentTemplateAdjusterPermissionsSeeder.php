<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class DocumentTemplateAdjusterPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'VIEW_DOCUMENT_TEMPLATE_ADJUSTER',
            'CREATE_DOCUMENT_TEMPLATE_ADJUSTER',
            'UPDATE_DOCUMENT_TEMPLATE_ADJUSTER',
            'DELETE_DOCUMENT_TEMPLATE_ADJUSTER',
        ];

        foreach (['web', 'sanctum'] as $guard) {
            foreach ($permissions as $name) {
                Permission::firstOrCreate([
                    'name'       => $name,
                    'guard_name' => $guard,
                ]);
            }
        }

        foreach (['web', 'sanctum'] as $guard) {
            $role = Role::firstOrCreate([
                'name'       => 'SUPER_ADMIN',
                'guard_name' => $guard,
            ]);

            $guardPermissions = Permission::where('guard_name', $guard)
                ->whereIn('name', $permissions)
                ->get();

            $role->givePermissionTo($guardPermissions);
        }
    }
}
