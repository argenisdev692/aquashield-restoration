<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Src\Modules\TypeDamages\Infrastructure\Persistence\Eloquent\Models\TypeDamageEloquentModel;

uses(RefreshDatabase::class);

function createTypeDamageAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_TYPE_DAMAGE',
        'READ_TYPE_DAMAGE',
        'UPDATE_TYPE_DAMAGE',
        'DELETE_TYPE_DAMAGE',
        'RESTORE_TYPE_DAMAGE',
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

it('lists, creates, shows, updates, deletes and restores type damages', function (): void {
    $admin = createTypeDamageAdmin();

    $this->actingAs($admin)
        ->getJson('/type-damages/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/type-damages/data/admin', [
            'type_damage_name' => 'Pipe Burst',
            'description' => 'Emergency water damage event',
            'severity' => 'high',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/type-damages/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('type_damage_name', 'Pipe Burst')
        ->assertJsonPath('severity', 'high');

    $this->actingAs($admin)
        ->putJson("/type-damages/data/admin/{$uuid}", [
            'type_damage_name' => 'Pipe Leak',
            'description' => 'Updated description',
            'severity' => 'medium',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Type damage updated successfully.']);

    expect(TypeDamageEloquentModel::query()->where('uuid', $uuid)->value('type_damage_name'))->toBe('Pipe Leak');

    $this->actingAs($admin)
        ->deleteJson("/type-damages/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Type damage deleted successfully.']);

    expect(TypeDamageEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/type-damages/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Type damage restored successfully.']);

    expect(TypeDamageEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))->toBeNull();
});
