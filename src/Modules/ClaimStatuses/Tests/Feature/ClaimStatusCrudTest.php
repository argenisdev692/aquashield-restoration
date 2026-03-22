<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Src\Modules\ClaimStatuses\Infrastructure\Persistence\Eloquent\Models\ClaimStatusEloquentModel;

uses(RefreshDatabase::class);

function createClaimStatusAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_CLAIM_STATUS',
        'READ_CLAIM_STATUS',
        'UPDATE_CLAIM_STATUS',
        'DELETE_CLAIM_STATUS',
        'RESTORE_CLAIM_STATUS',
    ];

    foreach ($permissions as $permissionName) {
        PermissionEloquentModel::query()->firstOrCreate([
            'name'       => $permissionName,
            'guard_name' => 'web',
        ]);
    }

    $role = RoleEloquentModel::query()->firstOrCreate([
        'name'       => 'SUPER_ADMIN',
        'guard_name' => 'web',
    ]);

    $role->syncPermissions($permissions);

    $user = UserEloquentModel::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('lists, creates, shows, updates, deletes and restores claim statuses', function (): void {
    $admin = createClaimStatusAdmin();

    $this->actingAs($admin)
        ->getJson('/claim-statuses/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/claim-statuses/data/admin', [
            'claim_status_name' => 'Open',
            'background_color'  => '#3B82F6',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/claim-statuses/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('claim_status_name', 'Open')
        ->assertJsonPath('background_color', '#3B82F6');

    $this->actingAs($admin)
        ->putJson("/claim-statuses/data/admin/{$uuid}", [
            'claim_status_name' => 'In Progress',
            'background_color'  => '#F59E0B',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Claim status updated successfully.']);

    expect(
        ClaimStatusEloquentModel::query()->where('uuid', $uuid)->value('claim_status_name')
    )->toBe('In Progress');

    $this->actingAs($admin)
        ->deleteJson("/claim-statuses/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Claim status deleted successfully.']);

    expect(
        ClaimStatusEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at
    )->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/claim-statuses/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Claim status restored successfully.']);

    expect(
        ClaimStatusEloquentModel::query()->where('uuid', $uuid)->value('deleted_at')
    )->toBeNull();
});

it('bulk deletes claim statuses', function (): void {
    $admin = createClaimStatusAdmin();

    $uuids = [];

    foreach (['Pending', 'Closed'] as $name) {
        $response = $this->actingAs($admin)
            ->postJson('/claim-statuses/data/admin', [
                'claim_status_name' => $name,
            ])
            ->assertCreated();

        $uuids[] = $response->json('uuid');
    }

    $this->actingAs($admin)
        ->postJson('/claim-statuses/data/admin/bulk-delete', ['uuids' => $uuids])
        ->assertOk()
        ->assertJsonPath('deleted_count', 2);

    foreach ($uuids as $uuid) {
        expect(
            ClaimStatusEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at
        )->not->toBeNull();
    }
});
