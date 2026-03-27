<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Blog\Infrastructure\Persistence\Eloquent\Models\BlogCategoryEloquentModel;
use Modules\Blog\Infrastructure\Persistence\Eloquent\Models\PostEloquentModel;
use Modules\Blog\Infrastructure\Persistence\Mappers\PostMapper;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel as User;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('maps the post eloquent model into the domain entity', function (): void {
    $user = User::factory()->create();

    $category = BlogCategoryEloquentModel::query()->create([
        'uuid' => Str::uuid()->toString(),
        'blog_category_name' => 'Announcements',
        'blog_category_description' => 'Category description',
        'user_id' => $user->id,
    ]);

    $postModel = PostEloquentModel::query()->create([
        'uuid' => Str::uuid()->toString(),
        'post_title' => 'Mapper Post',
        'post_title_slug' => 'mapper-post',
        'post_content' => '<p>Mapper content</p>',
        'post_excerpt' => 'Mapper excerpt',
        'category_id' => $category->id,
        'user_id' => $user->id,
        'post_status' => 'published',
    ]);

    $postModel->load('category');

    $entity = PostMapper::toDomain($postModel);

    expect($entity->title)->toBe('Mapper Post')
        ->and($entity->slug)->toBe('mapper-post')
        ->and($entity->categoryName)->toBe('Announcements')
        ->and($entity->status)->toBe('published');
});
