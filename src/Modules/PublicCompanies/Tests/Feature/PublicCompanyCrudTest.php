<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createPublicCompanyAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_PUBLIC_COMPANY',
        'READ_PUBLIC_COMPANY',
        'UPDATE_PUBLIC_COMPANY',
        'DELETE_PUBLIC_COMPANY',
        'RESTORE_PUBLIC_COMPANY',
    ];

    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ($permissions as $permissionName) {
        PermissionEloquentModel::query()->firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);
    }

    $role = RoleEloquentModel::query()->firstOrCreate([
        'name' => 'SUPER_ADMIN',
        'guard_name' => 'web',
    ]);

    $role->syncPermissions($permissions);

    $user = UserEloquentModel::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('lists, creates, shows, updates, deletes and restores public companies', function (): void {
    $admin = createPublicCompanyAdmin();

    $this->actingAs($admin)
        ->getJson('/public-companies/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'perPage', 'currentPage', 'lastPage'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/public-companies/data/admin', [
            'public_company_name' => 'Aqua Shield Public Company',
            'address' => '123 Main St, Houston, TX',
            'address_2' => 'Suite 200',
            'unit' => 'Unit 4B',
            'phone' => '+1 (555) 555-0101',
            'email' => 'public@example.com',
            'website' => 'https://public.example.com',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/public-companies/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('data.public_company_name', 'Aqua Shield Public Company')
        ->assertJsonPath('data.unit', 'Unit 4B');

    $this->actingAs($admin)
        ->putJson("/public-companies/data/admin/{$uuid}", [
            'public_company_name' => 'Aqua Shield Public Company Updated',
            'address' => '456 Updated St, Houston, TX',
            'address_2' => 'Floor 3',
            'unit' => 'Unit 7C',
            'phone' => '+1 (555) 555-0102',
            'email' => 'updated-public@example.com',
            'website' => 'https://updated-public.example.com',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Public company updated successfully.']);

    expect(PublicCompanyEloquentModel::query()->where('uuid', $uuid)->value('public_company_name'))
        ->toBe('Aqua Shield Public Company Updated');

    $this->actingAs($admin)
        ->deleteJson("/public-companies/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Public company deleted successfully.']);

    expect(PublicCompanyEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)
        ->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/public-companies/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Public company restored successfully.']);

    expect(PublicCompanyEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))
        ->toBeNull();
});

it('exports public companies to excel and pdf', function (): void {
    $admin = createPublicCompanyAdmin();

    PublicCompanyEloquentModel::query()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'public_company_name' => 'Export Public Company',
        'address' => '123 Export St',
        'address_2' => 'Unit 4',
        'unit' => 'Unit 4',
        'phone' => '+15555550199',
        'email' => 'export@example.com',
        'website' => 'https://export.example.com',
        'user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get('/public-companies/data/admin/export?format=excel')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)
        ->get('/public-companies/data/admin/export?format=pdf')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
