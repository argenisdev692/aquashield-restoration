<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\PermissionEloquentModel;
use Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models\RoleEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Src\Modules\CategoryProducts\Infrastructure\Persistence\Eloquent\Models\CategoryProductEloquentModel;

uses(RefreshDatabase::class);

function createCategoryProductAdmin(): UserEloquentModel
{
    $permissions = [
        'CREATE_CATEGORY_PRODUCT',
        'READ_CATEGORY_PRODUCT',
        'UPDATE_CATEGORY_PRODUCT',
        'DELETE_CATEGORY_PRODUCT',
        'RESTORE_CATEGORY_PRODUCT',
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

it('lists, creates, shows, updates, deletes and restores category products', function (): void {
    $admin = createCategoryProductAdmin();

    $this->actingAs($admin)
        ->getJson('/category-products/data/admin')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);

    $storeResponse = $this->actingAs($admin)
        ->postJson('/category-products/data/admin', [
            'category_product_name' => 'Flooring',
        ])
        ->assertCreated()
        ->assertJsonStructure(['uuid', 'message']);

    $uuid = (string) $storeResponse->json('uuid');

    $this->actingAs($admin)
        ->getJson("/category-products/data/admin/{$uuid}")
        ->assertOk()
        ->assertJsonPath('category_product_name', 'Flooring');

    $this->actingAs($admin)
        ->putJson("/category-products/data/admin/{$uuid}", [
            'category_product_name' => 'Paint',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Category product updated successfully.']);

    expect(CategoryProductEloquentModel::query()->where('uuid', $uuid)->value('category_product_name'))->toBe('Paint');

    $this->actingAs($admin)
        ->deleteJson("/category-products/data/admin/{$uuid}")
        ->assertOk()
        ->assertJson(['message' => 'Category product deleted successfully.']);

    expect(CategoryProductEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($admin)
        ->patchJson("/category-products/data/admin/{$uuid}/restore")
        ->assertOk()
        ->assertJson(['message' => 'Category product restored successfully.']);

    expect(CategoryProductEloquentModel::query()->where('uuid', $uuid)->value('deleted_at'))->toBeNull();
});
