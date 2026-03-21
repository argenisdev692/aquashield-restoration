<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Modules\ProjectTypes\Infrastructure\Persistence\Eloquent\Models\ProjectTypeEloquentModel;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;
use Tests\TestCase;

class ProjectTypeCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_with_permission_can_list_project_types(): void
    {
        $user = $this->createUserWithPermission('READ_PROJECT_TYPE');

        $response = $this->actingAs($user)->getJson('/project-types/data/admin');

        $response->assertOk()->assertJsonStructure(['data', 'meta']);
    }

    public function test_authenticated_user_with_permission_can_create_project_type(): void
    {
        $user            = $this->createUserWithPermission('CREATE_PROJECT_TYPE');
        $serviceCategory = ServiceCategoryEloquentModel::factory()->create();

        $response = $this->actingAs($user)->postJson('/project-types/data/admin', [
            'title'                 => 'Roof Repair',
            'description'           => 'Standard roof repair project',
            'status'                => 'active',
            'service_category_uuid' => $serviceCategory->uuid,
        ]);

        $response->assertCreated()->assertJsonFragment(['message' => 'Project type created successfully.']);
        $this->assertDatabaseHas('project_types', ['title' => 'Roof Repair']);
    }

    public function test_authenticated_user_with_permission_can_update_project_type(): void
    {
        $user            = $this->createUserWithPermission('UPDATE_PROJECT_TYPE');
        $serviceCategory = ServiceCategoryEloquentModel::factory()->create();
        $model           = ProjectTypeEloquentModel::factory()->create(['title' => 'Old Title', 'service_category_id' => $serviceCategory->id]);

        $response = $this->actingAs($user)->putJson("/project-types/data/admin/{$model->uuid}", [
            'title'                 => 'New Title',
            'status'                => 'active',
            'service_category_uuid' => $serviceCategory->uuid,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('project_types', ['title' => 'New Title']);
    }

    public function test_authenticated_user_with_permission_can_soft_delete_project_type(): void
    {
        $user  = $this->createUserWithPermission('DELETE_PROJECT_TYPE');
        $model = ProjectTypeEloquentModel::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/project-types/data/admin/{$model->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('project_types', ['uuid' => $model->uuid]);
    }

    public function test_authenticated_user_with_permission_can_restore_project_type(): void
    {
        $user  = $this->createUserWithPermission('RESTORE_PROJECT_TYPE');
        $model = ProjectTypeEloquentModel::factory()->create(['deleted_at' => now()]);

        $response = $this->actingAs($user)->patchJson("/project-types/data/admin/{$model->uuid}/restore");

        $response->assertOk();
        $this->assertDatabaseHas('project_types', ['uuid' => $model->uuid, 'deleted_at' => null]);
    }

    public function test_unauthenticated_user_cannot_access_project_types(): void
    {
        $this->getJson('/project-types/data/admin')->assertUnauthorized();
    }

    private function createUserWithPermission(string $permission): mixed
    {
        $user = \Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel::factory()->create();
        $user->givePermissionTo(\Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));

        return $user;
    }
}
