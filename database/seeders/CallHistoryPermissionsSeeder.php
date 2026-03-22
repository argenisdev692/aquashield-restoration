<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class CallHistoryPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'READ_CALL_HISTORY',
            'CREATE_CALL_HISTORY',
            'UPDATE_CALL_HISTORY',
            'DELETE_CALL_HISTORY',
            'RESTORE_CALL_HISTORY',
            'SYNC_CALL_HISTORY',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdmin = Role::where('name', 'SUPER_ADMIN')->first();
        if ($superAdmin !== null) {
            $superAdmin->givePermissionTo($permissions);
        }
    }
}
