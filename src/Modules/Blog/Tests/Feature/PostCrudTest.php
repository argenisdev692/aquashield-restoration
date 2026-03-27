<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Blog\Infrastructure\Persistence\Eloquent\Models\BlogCategoryEloquentModel;
use Modules\Blog\Infrastructure\Persistence\Eloquent\Models\PostEloquentModel;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel as User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createPostSuperAdmin(): User
{
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $permissions = [
        'VIEW_POST',
        'CREATE_POST',
        'UPDATE_POST',
        'DELETE_POST',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['uuid' => Str::uuid()->toString()]);
    }

    $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web'], ['uuid' => Str::uuid()->toString()]);
    $role->syncPermissions($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function createPostCategory(User $user): BlogCategoryEloquentModel
{
    return BlogCategoryEloquentModel::query()->create([
        'uuid' => Str::uuid()->toString(),
        'blog_category_name' => 'News',
        'blog_category_description' => 'Category description',
        'user_id' => $user->id,
    ]);
}

it('lists posts through the admin data endpoint', function (): void {
    $user = createPostSuperAdmin();
    $category = createPostCategory($user);

    PostEloquentModel::query()->create([
        'uuid' => Str::uuid()->toString(),
        'post_title' => 'First Post',
        'post_title_slug' => 'first-post',
        'post_content' => '<p>Body</p>',
        'category_id' => $category->id,
        'user_id' => $user->id,
        'post_status' => 'draft',
    ]);

    $this->actingAs($user)
        ->getJson(route('posts.data.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'postTitle', 'postTitleSlug', 'postStatus', 'createdAt'],
            ],
            'meta' => ['total', 'perPage', 'currentPage', 'lastPage'],
        ]);
});

it('creates, shows, updates, deletes and restores a post', function (): void {
    $user = createPostSuperAdmin();
    $category = createPostCategory($user);

    $payload = [
        'post_title' => 'Launch Post',
        'post_content' => '<p>Launch body</p>',
        'post_excerpt' => 'Launch excerpt',
        'category_uuid' => $category->uuid,
        'post_status' => 'published',
    ];

    $storeResponse = $this->actingAs($user)
        ->postJson(route('posts.data.store'), $payload)
        ->assertCreated()
        ->assertJsonPath('data.post_title', 'Launch Post');

    $uuid = (string) $storeResponse->json('data.uuid');

    $this->actingAs($user)
        ->getJson(route('posts.data.show', $uuid))
        ->assertOk()
        ->assertJsonPath('data.post_title_slug', 'launch-post');

    $this->actingAs($user)
        ->putJson(route('posts.data.update', $uuid), [
            'post_title' => 'Updated Launch Post',
            'post_status' => 'archived',
        ])
        ->assertOk()
        ->assertJsonPath('data.post_title', 'Updated Launch Post')
        ->assertJsonPath('data.post_status', 'archived');

    $this->actingAs($user)
        ->deleteJson(route('posts.data.destroy', $uuid))
        ->assertNoContent();

    expect(PostEloquentModel::withTrashed()->where('uuid', $uuid)->first()?->deleted_at)->not->toBeNull();

    $this->actingAs($user)
        ->patchJson(route('posts.data.restore', $uuid))
        ->assertOk()
        ->assertJson(['message' => 'Post restored successfully.']);

    expect(PostEloquentModel::query()->where('uuid', $uuid)->first()?->deleted_at)->toBeNull();
});

it('exports posts to excel and pdf', function (): void {
    $user = createPostSuperAdmin();
    $category = createPostCategory($user);

    PostEloquentModel::query()->create([
        'uuid' => Str::uuid()->toString(),
        'post_title' => 'Export Post',
        'post_title_slug' => 'export-post',
        'post_content' => '<p>Export body</p>',
        'category_id' => $category->id,
        'user_id' => $user->id,
        'post_status' => 'published',
    ]);

    $this->actingAs($user)
        ->get(route('posts.data.export', ['format' => 'excel']))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($user)
        ->get(route('posts.data.export', ['format' => 'pdf']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
