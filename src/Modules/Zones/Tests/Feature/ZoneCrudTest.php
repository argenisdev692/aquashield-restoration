<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Src\Modules\Zones\Infrastructure\Persistence\Eloquent\Models\ZoneEloquentModel;

uses(RefreshDatabase::class);

function createZoneAdmin(): UserEloquentModel
{
    $permissions = [
        'VIEW_ZONE',
        'CREATE_ZONE',
        'UPDATE_ZONE',
        'DELETE_ZONE',
        'RESTORE_ZONE',
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

it('lists, creates, shows, updates, deletes and restores zones', function (): void {
    $admin = createZoneAdmin();

    $this->actingAs($admin)
        ->getJson('/zones/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/zones/data/admin', [
            'zone_name'   => 'Living Room',
            'zone_type'   => 'interior',
            'code'        => 'LR-01',
            'description' => 'Main living area',
            'user_id'     => $admin->id,
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/zones/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('zone_name', 'Living Room')
        ->assertJsonPath('zone_type', 'interior');

    $this->actingAs($admin)
        ->putJson("/zones/data/admin/{$uuid}", [
            'zone_name'   => 'Master Bedroom',
            'zone_type'   => 'interior',
            'code'        => 'MB-01',
            'description' => 'Updated description',
            'user_id'     => $admin->id,
        ])
        ->assertOk()
        ->assertJson(['message' => 'Zone updated successfully.']);

    expect(ZoneEloquentModel::query()->where('uuid', $uuid)->value('zone_name'))->toBe('Master Bedroom');

    $this->actingAs($admin)
        ->deleteJson("/zones/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Zone deleted successfully.']);

    expect(ZoneEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/zones/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Zone restored successfully.']);

    expect(ZoneEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))->toBeNull();
});

it('bulk soft-deletes zones', function (): void {
    $admin = createZoneAdmin();

    $zone1 = ZoneEloquentModel::create([
        'uuid'      => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'zone_name' => 'Kitchen',
        'zone_type' => 'interior',
        'user_id'   => $admin->id,
    ]);

    $zone2 = ZoneEloquentModel::create([
        'uuid'      => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'zone_name' => 'Garage',
        'zone_type' => 'garage',
        'user_id'   => $admin->id,
    ]);

    $this->actingAs($admin)
        ->postJson('/zones/data/admin/bulk-delete', [
            'uuids' => [$zone1->uuid, $zone2->uuid],
        ])
        ->assertOk()
        ->assertJsonStructure(['message', 'deleted_count'])
        ->assertJsonPath('deleted_count', 2);

    expect(ZoneEloquentModel::withTrashed()->where('uuid', $zone1->uuid)->first()?->deleted_at)->not->toBeNull();
    expect(ZoneEloquentModel::withTrashed()->where('uuid', $zone2->uuid)->first()?->deleted_at)->not->toBeNull();
});

it('exports zones as excel', function (): void {
    $admin = createZoneAdmin();

    $this->actingAs($admin)
        ->get('/zones/data/admin/export?format=excel')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('exports zones as pdf', function (): void {
    $admin = createZoneAdmin();

    $this->actingAs($admin)
        ->get('/zones/data/admin/export?format=pdf')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
