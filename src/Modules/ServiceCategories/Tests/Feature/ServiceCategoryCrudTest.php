<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;
use Tests\TestCase;

class ServiceCategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_with_permission_can_list_service_categories(): void
    {
        $user = $this->createUserWithPermission('READ_SERVICE_CATEGORY');

        $response = $this->actingAs($user)->getJson('/service-categories/data/admin');

        $response->assertOk()->assertJsonStructure(['data', 'meta']);
    }

    public function test_authenticated_user_with_permission_can_create_service_category(): void
    {
        $user = $this->createUserWithPermission('CREATE_SERVICE_CATEGORY');

        $response = $this->actingAs($user)->postJson('/service-categories/data/admin', [
            'category' => 'Water Damage',
            'type'     => 'Residential',
        ]);

        $response->assertCreated()->assertJsonFragment(['message' => 'Service category created successfully.']);
        $this->assertDatabaseHas('service_categories', ['category' => 'Water Damage']);
    }

    public function test_authenticated_user_with_permission_can_update_service_category(): void
    {
        $user  = $this->createUserWithPermission('UPDATE_SERVICE_CATEGORY');
        $model = ServiceCategoryEloquentModel::factory()->create(['category' => 'Old Name']);

        $response = $this->actingAs($user)->putJson("/service-categories/data/admin/{$model->uuid}", [
            'category' => 'New Name',
            'type'     => 'Commercial',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('service_categories', ['category' => 'New Name']);
    }

    public function test_authenticated_user_with_permission_can_soft_delete_service_category(): void
    {
        $user  = $this->createUserWithPermission('DELETE_SERVICE_CATEGORY');
        $model = ServiceCategoryEloquentModel::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/service-categories/data/admin/{$model->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('service_categories', ['uuid' => $model->uuid]);
    }

    public function test_authenticated_user_with_permission_can_restore_service_category(): void
    {
        $user  = $this->createUserWithPermission('RESTORE_SERVICE_CATEGORY');
        $model = ServiceCategoryEloquentModel::factory()->create(['deleted_at' => now()]);

        $response = $this->actingAs($user)->patchJson("/service-categories/data/admin/{$model->uuid}/restore");

        $response->assertOk();
        $this->assertDatabaseHas('service_categories', ['uuid' => $model->uuid, 'deleted_at' => null]);
    }

    public function test_authenticated_user_with_permission_can_bulk_delete_service_categories(): void
    {
        $user   = $this->createUserWithPermission('DELETE_SERVICE_CATEGORY');
        $models = ServiceCategoryEloquentModel::factory()->count(3)->create();
        $uuids  = $models->pluck('uuid')->all();

        $response = $this->actingAs($user)->postJson('/service-categories/data/admin/bulk-delete', ['uuids' => $uuids]);

        $response->assertOk()->assertJsonFragment(['deleted_count' => 3]);
    }

    public function test_unauthenticated_user_cannot_access_service_categories(): void
    {
        $this->getJson('/service-categories/data/admin')->assertUnauthorized();
    }

    private function createUserWithPermission(string $permission): mixed
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();
        $user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));

        return $user;
    }
}
