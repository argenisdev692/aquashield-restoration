<?php

declare(strict_types=1);

use Modules\Blog\Domain\Entities\Post;
use Modules\Blog\Domain\ValueObjects\PostId;

it('hydrates the post entity with the expected values', function (): void {
    $post = new Post(
        id: new PostId(10),
        uuid: '0d7aa95a-886b-4a77-a0da-2c0622b4a001',
        title: 'Launch Post',
        slug: 'launch-post',
        content: '<p>Content</p>',
        excerpt: 'Excerpt',
        coverImage: 'cover.jpg',
        metaTitle: 'Meta title',
        metaDescription: 'Meta description',
        metaKeywords: 'alpha,beta',
        categoryId: 15,
        categoryUuid: '0d7aa95a-886b-4a77-a0da-2c0622b4a015',
        categoryName: 'News',
        userId: 7,
        status: 'draft',
        publishedAt: null,
        scheduledAt: null,
        createdAt: '2026-03-06T10:00:00+00:00',
        updatedAt: '2026-03-06T10:10:00+00:00',
        deletedAt: null,
    );

    expect($post->id->value)->toBe(10)
        ->and($post->uuid)->toBe('0d7aa95a-886b-4a77-a0da-2c0622b4a001')
        ->and($post->title)->toBe('Launch Post')
        ->and($post->slug)->toBe('launch-post')
        ->and($post->status)->toBe('draft')
        ->and($post->categoryName)->toBe('News');
});
