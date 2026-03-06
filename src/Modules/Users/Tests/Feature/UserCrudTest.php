<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel as User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createUsersSuperAdmin(): User
{
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $permissions = [
        'VIEW_USERS',
        'CREATE_USERS',
        'UPDATE_USERS',
        'DELETE_USERS',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['uuid' => Str::uuid()->toString()]);
    }

    $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web'], ['uuid' => Str::uuid()->toString()]);
    $role->syncPermissions($permissions);

    /** @var User $user */
    $user = User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);
    $user->assignRole($role);

    return $user;
}

it('lists users through the admin data endpoint', function (): void {
    $admin = createUsersSuperAdmin();

    User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);

    $this->actingAs($admin)
        ->getJson(route('users.data.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'name', 'lastName', 'fullName', 'email', 'status', 'createdAt'],
            ],
            'meta' => ['total', 'perPage', 'currentPage', 'lastPage'],
        ]);
});

it('creates, shows, updates, deletes and restores a user', function (): void {
    $admin = createUsersSuperAdmin();

    $storeResponse = $this->actingAs($admin)
        ->postJson(route('users.data.store'), [
            'name' => 'Created',
            'last_name' => 'User',
            'email' => 'created-user@example.com',
            'username' => 'created-user',
            'phone' => '5550001',
            'city' => 'Miami',
            'state' => 'Florida',
            'country' => 'USA',
            'zip_code' => '33101',
        ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'created-user@example.com');

    $uuid = (string) $storeResponse->json('data.uuid');

    $this->actingAs($admin)
        ->getJson(route('users.data.show', $uuid))
        ->assertOk()
        ->assertJsonPath('data.uuid', $uuid);

    $this->actingAs($admin)
        ->putJson(route('users.data.update', $uuid), [
            'last_name' => 'Updated',
            'city' => 'Orlando',
        ])
        ->assertOk()
        ->assertJsonPath('data.last_name', 'Updated')
        ->assertJsonPath('data.city', 'Orlando');

    $this->actingAs($admin)
        ->deleteJson(route('users.data.destroy', $uuid))
        ->assertNoContent();

    expect(User::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson(route('users.data.restore', $uuid))
        ->assertOk()
        ->assertJson(['message' => 'User restored successfully.']);

    expect(User::query()->where('uuid', $uuid)->first()?->deleted_at)->toBeNull();
});

it('exports users to excel and pdf', function (): void {
    $admin = createUsersSuperAdmin();

    User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('users.data.export', ['format' => 'excel']))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)
        ->get(route('users.data.export', ['format' => 'pdf']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
