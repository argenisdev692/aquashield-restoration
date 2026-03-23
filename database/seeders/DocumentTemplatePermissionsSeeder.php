<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DocumentTemplatePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'READ_DOCUMENT_TEMPLATE',
            'CREATE_DOCUMENT_TEMPLATE',
            'UPDATE_DOCUMENT_TEMPLATE',
            'DELETE_DOCUMENT_TEMPLATE',
        ];

        foreach (['web', 'sanctum'] as $guard) {
            foreach ($permissions as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }
        }

        foreach (['web', 'sanctum'] as $guard) {
            $superAdmin = Role::where('name', 'SUPER_ADMIN')
                ->where('guard_name', $guard)
                ->first();

            if ($superAdmin === null) {
                continue;
            }

            $guardPermissions = Permission::whereIn('name', $permissions)
                ->where('guard_name', $guard)
                ->get();

            $superAdmin->givePermissionTo($guardPermissions);
        }
    }
}
