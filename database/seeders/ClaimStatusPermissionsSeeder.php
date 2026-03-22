<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class ClaimStatusPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'READ_CLAIM_STATUS',
            'CREATE_CLAIM_STATUS',
            'UPDATE_CLAIM_STATUS',
            'DELETE_CLAIM_STATUS',
            'RESTORE_CLAIM_STATUS',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['uuid' => Uuid::uuid4()->toString()]
            );
        }

        $superAdmin = Role::where('name', 'SUPER_ADMIN')->first();
        if ($superAdmin !== null) {
            $superAdmin->givePermissionTo($permissions);
        }
    }
}
