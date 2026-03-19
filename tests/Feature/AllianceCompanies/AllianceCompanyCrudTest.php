<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\AllianceCompanies\Infrastructure\Persistence\Eloquent\Models\AllianceCompanyEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;

uses(RefreshDatabase::class);

function createAllianceCompanyAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_ALLIANCE_COMPANY',
        'READ_ALLIANCE_COMPANY',
        'UPDATE_ALLIANCE_COMPANY',
        'DELETE_ALLIANCE_COMPANY',
        'RESTORE_ALLIANCE_COMPANY',
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

it('lists, creates, shows, updates, deletes and restores alliance companies', function (): void {
    $admin = createAllianceCompanyAdmin();

    $this->actingAs($admin)
        ->getJson('/alliance-companies/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/alliance-companies/data/admin', [
            'alliance_company_name' => 'ServX Alliance',
            'address' => '123 Main St, Houston, TX',
            'phone' => '+1 (555) 555-0101',
            'email' => 'alliance@example.com',
            'website' => 'https://example.com',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/alliance-companies/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('alliance_company_name', 'ServX Alliance')
        ->assertJsonPath('email', 'alliance@example.com');

    $this->actingAs($admin)
        ->putJson("/alliance-companies/data/admin/{$uuid}", [
            'alliance_company_name' => 'ServX Alliance Updated',
            'address' => '456 Updated St, Houston, TX',
            'phone' => '+1 (555) 555-0102',
            'email' => 'updated@example.com',
            'website' => 'https://updated.example.com',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Alliance company updated successfully.']);

    expect(AllianceCompanyEloquentModel::query()->where('uuid', $uuid)->value('alliance_company_name'))->toBe('ServX Alliance Updated');

    $this->actingAs($admin)
        ->deleteJson("/alliance-companies/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Alliance company deleted successfully.']);

    expect(AllianceCompanyEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/alliance-companies/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Alliance company restored successfully.']);

    expect(AllianceCompanyEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))->toBeNull();

    $secondResponse = $this->actingAs($admin)
        ->postJson('/alliance-companies/data/admin', [
            'alliance_company_name' => 'Second Alliance Company',
            'address' => '789 Bulk St, Houston, TX',
            'phone' => '+1 (555) 555-0103',
            'email' => 'second@example.com',
            'website' => 'https://second.example.com',
        ])
        ->assertCreated();

    $thirdResponse = $this->actingAs($admin)
        ->postJson('/alliance-companies/data/admin', [
            'alliance_company_name' => 'Third Alliance Company',
            'address' => '790 Bulk St, Houston, TX',
            'phone' => '+1 (555) 555-0104',
            'email' => 'third@example.com',
            'website' => 'https://third.example.com',
        ])
        ->assertCreated();

    $this->actingAs($admin)
        ->postJson('/alliance-companies/data/admin/bulk-delete', [
            'uuids' => [
                $secondResponse->json('uuid'),
                $thirdResponse->json('uuid'),
            ],
        ])
        ->assertOk()
        ->assertJsonPath('deleted_count', 2);

    expect(
        AllianceCompanyEloquentModel::withTrashed()
            ->whereIn('uuid', [
                (string) $secondResponse->json('uuid'),
                (string) $thirdResponse->json('uuid'),
            ])
            ->whereNotNull('deleted_at')
            ->count(),
    )->toBe(2);
});
