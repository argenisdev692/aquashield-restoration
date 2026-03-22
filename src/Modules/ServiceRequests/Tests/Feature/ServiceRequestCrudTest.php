<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;

uses(RefreshDatabase::class);

function createServiceRequestAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_SERVICE_REQUEST',
        'READ_SERVICE_REQUEST',
        'UPDATE_SERVICE_REQUEST',
        'DELETE_SERVICE_REQUEST',
        'RESTORE_SERVICE_REQUEST',
    ];

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

it('lists, creates, shows, updates, deletes and restores service requests', function (): void {
    $admin = createServiceRequestAdmin();

    $this->actingAs($admin)
        ->getJson('/service-requests/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/service-requests/data/admin', [
            'requested_service' => 'Water Extraction',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/service-requests/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('requested_service', 'Water Extraction');

    $this->actingAs($admin)
        ->putJson("/service-requests/data/admin/{$uuid}", [
            'requested_service' => 'Structural Drying',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Service request updated successfully.']);

    expect(ServiceRequestEloquentModel::query()->where('uuid', $uuid)->value('requested_service'))->toBe('Structural Drying');

    $bulkStore = $this->actingAs($admin)
        ->postJson('/service-requests/data/admin', [
            'requested_service' => 'Mold Remediation',
        ])
        ->assertCreated();

    $bulkUuid = (string) $bulkStore->json('uuid');

    $this->actingAs($admin)
        ->postJson('/service-requests/data/admin/bulk-delete', [
            'uuids' => [$bulkUuid],
        ])
        ->assertOk()
        ->assertJsonPath('deleted_count', 1);

    expect(ServiceRequestEloquentModel::withTrashed()->where('uuid', $bulkUuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->deleteJson("/service-requests/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Service request deleted successfully.']);

    expect(ServiceRequestEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/service-requests/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Service request restored successfully.']);

    expect(ServiceRequestEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))->toBeNull();
});
