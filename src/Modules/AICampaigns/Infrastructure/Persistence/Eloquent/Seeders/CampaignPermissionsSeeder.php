<?php

declare(strict_types=1);

namespace Modules\AICampaigns\Infrastructure\Persistence\Eloquent\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class CampaignPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'VIEW_CAMPAIGN',
            'CREATE_CAMPAIGN',
            'UPDATE_CAMPAIGN',
            'DELETE_CAMPAIGN',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['uuid' => Uuid::uuid4()->toString()],
            );

            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'sanctum'],
                ['uuid' => Uuid::uuid4()->toString()],
            );
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdminWeb = Role::firstOrCreate(
            ['name' => 'SUPER_ADMIN', 'guard_name' => 'web'],
            ['uuid' => Uuid::uuid4()->toString()],
        );

        $superAdminSanctum = Role::firstOrCreate(
            ['name' => 'SUPER_ADMIN', 'guard_name' => 'sanctum'],
            ['uuid' => Uuid::uuid4()->toString()],
        );

        $superAdminWeb->givePermissionTo($permissions);
        $superAdminSanctum->givePermissionTo($permissions);
    }
}
