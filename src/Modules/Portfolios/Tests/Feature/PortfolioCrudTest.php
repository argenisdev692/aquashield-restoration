<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PortfolioCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'VIEW_PORTFOLIO',
            'CREATE_PORTFOLIO',
            'UPDATE_PORTFOLIO',
            'DELETE_PORTFOLIO',
            'RESTORE_PORTFOLIO',
        ] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function test_unauthenticated_user_cannot_list_portfolios(): void
    {
        $response = $this->getJson('/portfolios/data/admin');

        $response->assertStatus(401)->assertRedirectToRoute('login');
    }

    public function test_user_without_permission_cannot_list_portfolios(): void
    {
        $user = \Src\Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();

        $response = $this->actingAs($user)->getJson('/portfolios/data/admin');

        $response->assertForbidden();
    }

    public function test_user_with_permission_can_list_portfolios(): void
    {
        $user = \Src\Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();
        $user->givePermissionTo('VIEW_PORTFOLIO');

        $response = $this->actingAs($user)->getJson('/portfolios/data/admin');

        $response->assertOk()->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
    }

    public function test_user_with_create_permission_can_create_portfolio(): void
    {
        $user = \Src\Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();
        $user->givePermissionTo('CREATE_PORTFOLIO');

        $response = $this->actingAs($user)->postJson('/portfolios/data/admin', [
            'project_type_uuid' => null,
        ]);

        $response->assertCreated()->assertJsonStructure(['uuid', 'message']);
    }

    public function test_user_with_delete_permission_can_soft_delete_portfolio(): void
    {
        $user = \Src\Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();
        $user->givePermissionTo(['CREATE_PORTFOLIO', 'DELETE_PORTFOLIO']);

        $createResponse = $this->actingAs($user)->postJson('/portfolios/data/admin', [
            'project_type_uuid' => null,
        ]);
        $createResponse->assertCreated();

        $uuid = $createResponse->json('uuid');

        $deleteResponse = $this->actingAs($user)->deleteJson("/portfolios/data/admin/{$uuid}");

        $deleteResponse->assertOk()->assertJsonFragment(['message' => 'Portfolio deleted successfully.']);

        $this->assertSoftDeleted('portfolios', ['uuid' => $uuid]);
    }

    public function test_user_with_restore_permission_can_restore_portfolio(): void
    {
        $user = \Src\Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();
        $user->givePermissionTo(['CREATE_PORTFOLIO', 'DELETE_PORTFOLIO', 'RESTORE_PORTFOLIO']);

        $createResponse = $this->actingAs($user)->postJson('/portfolios/data/admin', [
            'project_type_uuid' => null,
        ]);
        $uuid = $createResponse->json('uuid');

        $this->actingAs($user)->deleteJson("/portfolios/data/admin/{$uuid}");

        $restoreResponse = $this->actingAs($user)->patchJson("/portfolios/data/admin/{$uuid}/restore");

        $restoreResponse->assertOk();
        $this->assertNotSoftDeleted('portfolios', ['uuid' => $uuid]);
    }

    public function test_user_with_delete_permission_can_bulk_delete_portfolios(): void
    {
        $user = \Src\Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();
        $user->givePermissionTo(['CREATE_PORTFOLIO', 'DELETE_PORTFOLIO']);

        $uuids = [];
        for ($i = 0; $i < 3; $i++) {
            $res = $this->actingAs($user)->postJson('/portfolios/data/admin', ['project_type_uuid' => null]);
            $uuids[] = $res->json('uuid');
        }

        $bulkResponse = $this->actingAs($user)->postJson('/portfolios/data/admin/bulk-delete', [
            'uuids' => $uuids,
        ]);

        $bulkResponse->assertOk()->assertJsonFragment(['deleted_count' => 3]);

        foreach ($uuids as $uuid) {
            $this->assertSoftDeleted('portfolios', ['uuid' => $uuid]);
        }
    }
}
