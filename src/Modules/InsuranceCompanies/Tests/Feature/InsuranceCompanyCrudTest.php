<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createInsuranceCompanyAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_INSURANCE_COMPANY',
        'READ_INSURANCE_COMPANY',
        'UPDATE_INSURANCE_COMPANY',
        'DELETE_INSURANCE_COMPANY',
        'RESTORE_INSURANCE_COMPANY',
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

it('lists, creates, shows, updates, deletes, restores and bulk deletes insurance companies', function (): void {
    $admin = createInsuranceCompanyAdmin();

    $this->actingAs($admin)
        ->getJson('/insurance-companies/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'perPage', 'currentPage', 'lastPage'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/insurance-companies/data/admin', [
            'insurance_company_name' => 'Aqua Shield Carrier',
            'address' => '123 Main St, Houston, TX',
            'address_2' => 'Suite 200',
            'phone' => '+1 (555) 555-0101',
            'email' => 'carrier@example.com',
            'website' => 'https://carrier.example.com',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/insurance-companies/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('data.insurance_company_name', 'Aqua Shield Carrier')
        ->assertJsonPath('data.address_2', 'Suite 200');

    $this->actingAs($admin)
        ->putJson("/insurance-companies/data/admin/{$uuid}", [
            'insurance_company_name' => 'Aqua Shield Carrier Updated',
            'address' => '456 Updated St, Houston, TX',
            'address_2' => 'Suite 300',
            'phone' => '+1 (555) 555-0102',
            'email' => 'updated@example.com',
            'website' => 'https://updated.example.com',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Insurance company updated successfully.']);

    expect(InsuranceCompanyEloquentModel::query()->where('uuid', $uuid)->value('insurance_company_name'))
        ->toBe('Aqua Shield Carrier Updated');

    $this->actingAs($admin)
        ->deleteJson("/insurance-companies/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Insurance company deleted successfully.']);

    expect(InsuranceCompanyEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)
        ->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/insurance-companies/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Insurance company restored successfully.']);

    expect(InsuranceCompanyEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))
        ->toBeNull();

    $secondResponse = $this->actingAs($admin)
        ->postJson('/insurance-companies/data/admin', [
            'insurance_company_name' => 'Second Insurance Company',
            'address' => '789 Bulk St, Houston, TX',
            'address_2' => 'Floor 2',
            'phone' => '+1 (555) 555-0103',
            'email' => 'second@example.com',
            'website' => 'https://second.example.com',
        ])
        ->assertCreated();

    $thirdResponse = $this->actingAs($admin)
        ->postJson('/insurance-companies/data/admin', [
            'insurance_company_name' => 'Third Insurance Company',
            'address' => '790 Bulk St, Houston, TX',
            'address_2' => 'Floor 3',
            'phone' => '+1 (555) 555-0104',
            'email' => 'third@example.com',
            'website' => 'https://third.example.com',
        ])
        ->assertCreated();

    $this->actingAs($admin)
        ->postJson('/insurance-companies/data/admin/bulk-delete', [
            'uuids' => [
                $secondResponse->json('uuid'),
                $thirdResponse->json('uuid'),
            ],
        ])
        ->assertOk()
        ->assertJsonPath('deleted_count', 2);

    expect(
        InsuranceCompanyEloquentModel::withTrashed()
            ->whereIn('uuid', [
                (string) $secondResponse->json('uuid'),
                (string) $thirdResponse->json('uuid'),
            ])
            ->whereNotNull('deleted_at')
            ->count(),
    )->toBe(2);
});

it('exports insurance companies to excel and pdf', function (): void {
    $admin = createInsuranceCompanyAdmin();

    InsuranceCompanyEloquentModel::query()->create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'insurance_company_name' => 'Export Insurance Company',
        'address' => '123 Export St',
        'address_2' => 'Unit 4',
        'phone' => '+15555550199',
        'email' => 'export@example.com',
        'website' => 'https://export.example.com',
        'user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get('/insurance-companies/data/admin/export?format=excel')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)
        ->get('/insurance-companies/data/admin/export?format=pdf')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
