<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel as Permission;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel as Role;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel as User;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createAccessControlPermission(string $name): Permission
{
    return Permission::firstOrCreate(
        ['name' => $name, 'guard_name' => 'web'],
        ['uuid' => Str::uuid()->toString()],
    );
}

function createAccessControlRole(string $name, array $permissions = []): Role
{
    $role = Role::firstOrCreate(
        ['name' => $name, 'guard_name' => 'web'],
        ['uuid' => Str::uuid()->toString()],
    );

    if ($permissions !== []) {
        $role->syncPermissions($permissions);
    }

    return $role;
}

function createAccessControlSuperAdmin(): User
{
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $permissions = [
        'READ_PERMISSION',
        'CREATE_PERMISSION',
        'READ_ROLE',
        'UPDATE_ROLE',
        'UPDATE_PERMISSION',
        'VIEW_USERS',
        'UPDATE_USERS',
    ];

    foreach ($permissions as $permission) {
        createAccessControlPermission($permission);
    }

    $role = createAccessControlRole('SUPER_ADMIN', $permissions);

    $user = User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);

    $user->assignRole($role);

    return $user->fresh();
}

it('lists access control datasets through admin endpoints', function (): void {
    $admin = createAccessControlSuperAdmin();
    $managerRole = createAccessControlRole('MANAGER', ['READ_PERMISSION']);

    $managedUser = User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);
    $managedUser->assignRole($managerRole);

    $this->actingAs($admin)
        ->getJson(route('permissions.data.permissions'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'name', 'guard_name', 'roles_count'],
            ],
        ]);

    $this->actingAs($admin)
        ->getJson(route('permissions.data.roles'))
        ->assertOk()
        ->assertJsonFragment(['name' => 'MANAGER']);

    $this->actingAs($admin)
        ->getJson(route('permissions.data.users'))
        ->assertOk()
        ->assertJsonFragment(['uuid' => $managedUser->uuid]);
});

it('creates permissions and syncs role and user access', function (): void {
    $admin = createAccessControlSuperAdmin();
    createAccessControlPermission('EXPORT_REPORTS');

    $role = createAccessControlRole('SUPERVISOR');
    $user = User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);

    $this->actingAs($admin)
        ->postJson(route('permissions.data.permissions.store'), [
            'name' => 'manage_quotes',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'MANAGE_QUOTES');

    $this->actingAs($admin)
        ->putJson(route('permissions.data.roles.sync', $role->uuid), [
            'permissions' => ['EXPORT_REPORTS', 'MANAGE_QUOTES'],
        ])
        ->assertOk()
        ->assertJsonPath('data.permission_names.0', 'EXPORT_REPORTS');

    $this->actingAs($admin)
        ->putJson(route('permissions.data.users.sync', $user->uuid), [
            'roles' => ['SUPERVISOR'],
            'permissions' => ['MANAGE_QUOTES'],
        ])
        ->assertOk()
        ->assertJsonPath('data.roles.0', 'SUPERVISOR')
        ->assertJsonPath('data.direct_permissions.0', 'MANAGE_QUOTES');
});

it('blocks non super admin users from modifying the super admin role', function (): void {
    $admin = createAccessControlSuperAdmin();

    createAccessControlPermission('UPDATE_ROLE');
    createAccessControlPermission('READ_ROLE');
    createAccessControlPermission('READ_PERMISSION');

    $managerRole = createAccessControlRole('ACCESS_MANAGER', ['UPDATE_ROLE', 'READ_ROLE', 'READ_PERMISSION']);

    $manager = User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);
    $manager->assignRole($managerRole);

    $superAdminRole = Role::query()->where('name', 'SUPER_ADMIN')->firstOrFail();

    $this->actingAs($manager)
        ->putJson(route('permissions.data.roles.sync', $superAdminRole->uuid), [
            'permissions' => ['READ_PERMISSION'],
        ])
        ->assertForbidden();
});
