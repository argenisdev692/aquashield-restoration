<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Src\Modules\CauseOfLosses\Infrastructure\Persistence\Eloquent\Models\CauseOfLossEloquentModel;

uses(RefreshDatabase::class);

function createCauseOfLossAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_CAUSE_OF_LOSS',
        'READ_CAUSE_OF_LOSS',
        'UPDATE_CAUSE_OF_LOSS',
        'DELETE_CAUSE_OF_LOSS',
        'RESTORE_CAUSE_OF_LOSS',
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

it('lists, creates, shows, updates, deletes and restores cause of losses', function (): void {
    $admin = createCauseOfLossAdmin();

    $this->actingAs($admin)
        ->getJson('/cause-of-losses/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/cause-of-losses/data/admin', [
            'cause_loss_name' => 'Fire Damage',
            'description' => 'Originated by kitchen fire',
            'severity' => 'high',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/cause-of-losses/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('cause_loss_name', 'Fire Damage')
        ->assertJsonPath('severity', 'high');

    $this->actingAs($admin)
        ->putJson("/cause-of-losses/data/admin/{$uuid}", [
            'cause_loss_name' => 'Water Damage',
            'description' => 'Updated description',
            'severity' => 'medium',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Cause of loss updated successfully.']);

    expect(CauseOfLossEloquentModel::query()->where('uuid', $uuid)->value('cause_loss_name'))->toBe('Water Damage');

    $this->actingAs($admin)
        ->deleteJson("/cause-of-losses/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Cause of loss deleted successfully.']);

    expect(CauseOfLossEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/cause-of-losses/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Cause of loss restored successfully.']);

    expect(CauseOfLossEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))->toBeNull();
});
