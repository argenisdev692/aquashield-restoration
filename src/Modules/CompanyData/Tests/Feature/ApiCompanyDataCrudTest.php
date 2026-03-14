<?php

declare(strict_types=1);

use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel as User;
use Modules\CompanyData\Infrastructure\Persistence\Eloquent\Models\CompanyDataEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

// Pest uses $this for assertions and requests
uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Generate a user to authenticate with during tests
});

function createCompanyDataCrudAdmin(): User
{
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $permissions = [
        'VIEW_COMPANY_DATA',
        'CREATE_COMPANY_DATA',
        'UPDATE_COMPANY_DATA',
        'DELETE_COMPANY_DATA',
        'RESTORE_COMPANY_DATA',
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

it('lists company data', function () {
    $admin = createCompanyDataCrudAdmin();
    CompanyDataEloquentModel::factory()->count(3)->create(['user_id' => $admin->id]);

    $this->actingAs($admin)
        ->getJson(route('api.admin.company_data.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'user_uuid', 'company_name', 'created_at']
            ],
            'meta' => ['total', 'perPage']
        ]);
});

it('creates company data', function () {
    $admin = createCompanyDataCrudAdmin();
    $user = User::factory()->create([
        'status' => 'active',
        'terms_and_conditions' => true,
    ]);
    $payload = [
        'user_uuid' => $user->uuid,
        'company_name' => 'Acme Corp',
        'email' => 'contact@acme.com',
        'phone' => '1234567890'
    ];

    $this->actingAs($admin)
        ->postJson(route('api.admin.company_data.store'), $payload)
        ->assertCreated()
        ->assertJsonStructure(['message']);

    $this->assertDatabaseHas('company_data', [
        'user_id' => $user->id,
        'company_name' => 'Acme Corp'
    ]);
});

it('validates required fields on create', function () {
    $admin = createCompanyDataCrudAdmin();
    $this->actingAs($admin)
        ->postJson(route('api.admin.company_data.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['user_uuid', 'company_name']);
});

it('shows company data', function () {
    $admin = createCompanyDataCrudAdmin();
    $uuid = (string) Str::uuid();
    CompanyDataEloquentModel::factory()->create([
        'uuid' => $uuid,
        'user_id' => $admin->id,
        'company_name' => 'Show Test Corp'
    ]);

    $this->actingAs($admin)
        ->getJson(route('api.admin.company_data.show', $uuid))
        ->assertOk()
        ->assertJsonPath('data.company_name', 'Show Test Corp');
});

it('updates company data', function () {
    $admin = createCompanyDataCrudAdmin();
    $uuid = (string) Str::uuid();
    CompanyDataEloquentModel::factory()->create([
        'uuid' => $uuid,
        'user_id' => $admin->id,
        'company_name' => 'Old Name'
    ]);

    $this->actingAs($admin)
        ->putJson(route('api.admin.company_data.update', $uuid), [
            'company_name' => 'New Name'
        ])
        ->assertOk()
        ->assertJson(['message' => 'Company data updated successfully']);

    $this->assertDatabaseHas('company_data', [
        'uuid' => $uuid,
        'company_name' => 'New Name'
    ]);
});

it('soft deletes company data', function () {
    $admin = createCompanyDataCrudAdmin();
    $uuid = (string) Str::uuid();
    CompanyDataEloquentModel::factory()->create([
        'uuid' => $uuid,
        'user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->deleteJson(route('api.admin.company_data.destroy', $uuid))
        ->assertOk()
        ->assertJson(['message' => 'Company data deleted successfully']);

    $this->assertDatabaseHas('company_data', [
        'uuid' => $uuid,
    ]);

    expect(CompanyDataEloquentModel::withTrashed()->where('uuid', $uuid)->first()->deleted_at)->not->toBeNull();
});

it('restores soft deleted company data', function () {
    $admin = createCompanyDataCrudAdmin();
    $uuid = (string) Str::uuid();
    CompanyDataEloquentModel::factory()->create([
        'uuid' => $uuid,
        'user_id' => $admin->id,
        'deleted_at' => now(),
    ]);

    $this->actingAs($admin)
        ->patchJson(route('api.admin.company_data.restore', $uuid))
        ->assertOk()
        ->assertJson(['message' => 'Company data restored successfully']);

    expect(CompanyDataEloquentModel::where('uuid', $uuid)->first()->deleted_at)->toBeNull();
});
