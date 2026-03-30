<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ClaimCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'ClaimsPermissionsSeeder']);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/claims/data/admin/')
            ->assertStatus(401);
    }

    public function test_authenticated_user_without_permission_is_forbidden(): void
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();

        $this->actingAs($user)
            ->getJson('/claims/data/admin/')
            ->assertStatus(403);
    }

    public function test_list_claims_returns_paginated_response(): void
    {
        $user = $this->createUserWithPermission('VIEW_CLAIM');

        $this->actingAs($user)
            ->getJson('/claims/data/admin/')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['currentPage', 'lastPage', 'perPage', 'total'],
            ]);
    }

    public function test_create_claim_returns_uuid(): void
    {
        $user = $this->createUserWithPermission('CREATE_CLAIM');

        $this->actingAs($user)
            ->postJson('/claims/data/admin/', $this->validClaimPayload())
            ->assertCreated()
            ->assertJsonStructure(['uuid', 'message']);
    }

    public function test_create_claim_validates_required_fields(): void
    {
        $user = $this->createUserWithPermission('CREATE_CLAIM');

        $this->actingAs($user)
            ->postJson('/claims/data/admin/', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'property_id',
                'type_damage_id',
                'user_id_ref_by',
                'claim_status',
                'policy_number',
            ]);
    }

    public function test_create_claim_auto_generates_internal_id(): void
    {
        $user = $this->createUserWithPermission('CREATE_CLAIM');

        $uuid = $this->actingAs($user)
            ->postJson('/claims/data/admin/', $this->validClaimPayload())
            ->assertCreated()
            ->json('uuid');

        $viewUser = $this->createUserWithPermission('VIEW_CLAIM');

        $this->actingAs($viewUser)
            ->getJson("/claims/data/admin/{$uuid}")
            ->assertOk()
            ->assertJsonPath('claim_internal_id', 'AQ-000001');
    }

    public function test_show_claim_returns_404_for_unknown_uuid(): void
    {
        $user = $this->createUserWithPermission('VIEW_CLAIM');

        $this->actingAs($user)
            ->getJson('/claims/data/admin/00000000-0000-4000-a000-000000000000')
            ->assertNotFound();
    }

    public function test_delete_and_restore_claim_lifecycle(): void
    {
        $creator = $this->createUserWithPermission('CREATE_CLAIM');

        $uuid = $this->actingAs($creator)
            ->postJson('/claims/data/admin/', $this->validClaimPayload())
            ->assertCreated()
            ->json('uuid');

        $deleter = $this->createUserWithPermission('DELETE_CLAIM');
        $this->actingAs($deleter)
            ->deleteJson("/claims/data/admin/{$uuid}")
            ->assertOk();

        $restorer = $this->createUserWithPermission('RESTORE_CLAIM');
        $this->actingAs($restorer)
            ->patchJson("/claims/data/admin/{$uuid}/restore")
            ->assertOk();
    }

    public function test_bulk_delete_claims(): void
    {
        $creator = $this->createUserWithPermission('CREATE_CLAIM');

        $uuid1 = $this->actingAs($creator)
            ->postJson('/claims/data/admin/', $this->validClaimPayload())
            ->json('uuid');

        $uuid2 = $this->actingAs($creator)
            ->postJson('/claims/data/admin/', $this->validClaimPayload())
            ->json('uuid');

        $deleter = $this->createUserWithPermission('DELETE_CLAIM');
        $this->actingAs($deleter)
            ->postJson('/claims/data/admin/bulk-delete', ['uuids' => [$uuid1, $uuid2]])
            ->assertOk()
            ->assertJsonPath('deleted_count', 2);
    }

    public function test_sequential_ids_increment_correctly(): void
    {
        $user = $this->createUserWithPermission('CREATE_CLAIM');
        $viewer = $this->createUserWithPermission('VIEW_CLAIM');

        $uuid1 = $this->actingAs($user)
            ->postJson('/claims/data/admin/', $this->validClaimPayload())
            ->json('uuid');

        $uuid2 = $this->actingAs($user)
            ->postJson('/claims/data/admin/', $this->validClaimPayload())
            ->json('uuid');

        $id1 = $this->actingAs($viewer)->getJson("/claims/data/admin/{$uuid1}")->json('claim_internal_id');
        $id2 = $this->actingAs($viewer)->getJson("/claims/data/admin/{$uuid2}")->json('claim_internal_id');

        $this->assertSame('AQ-000001', $id1);
        $this->assertSame('AQ-000002', $id2);
    }

    private function createUserWithPermission(string $permission): mixed
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();

        $role = Role::firstOrCreate(['name' => "TEST_ROLE_{$permission}", 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        $role->givePermissionTo($perm);
        $user->assignRole($role);

        return $user;
    }

    private function validClaimPayload(): array
    {
        return [
            'property_id'       => 1,
            'signature_path_id' => 1,
            'type_damage_id'    => 1,
            'user_id_ref_by'    => 1,
            'claim_status'      => 1,
            'policy_number'     => 'POL-FEAT-001',
        ];
    }
}
