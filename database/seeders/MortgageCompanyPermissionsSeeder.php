<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;

final class MortgageCompanyPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'READ_MORTGAGE_COMPANY',
            'CREATE_MORTGAGE_COMPANY',
            'UPDATE_MORTGAGE_COMPANY',
            'DELETE_MORTGAGE_COMPANY',
            'RESTORE_MORTGAGE_COMPANY',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['uuid' => Uuid::uuid4()->toString()]
            );
        }
    }
}
