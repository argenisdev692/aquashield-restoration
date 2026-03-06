<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Modules\Blog\Application\Commands\CreatePost\CreatePostCommand;
use Modules\Blog\Application\Commands\CreatePost\CreatePostHandler;
use Modules\Blog\Application\DTOs\CreatePostDTO;
use Modules\Blog\Domain\Entities\Post;
use Modules\Blog\Domain\Ports\PostRepositoryPort;
use Modules\Blog\Domain\ValueObjects\PostId;
use Shared\Infrastructure\Audit\AuditInterface;

it('creates a post and records the audit entry', function (): void {
    /** @var PostRepositoryPort&MockInterface $repository */
    $repository = Mockery::mock(PostRepositoryPort::class);
    $repository->shouldReceive('findCategoryIdByUuid')
        ->once()
        ->with('e4a95324-48e9-40f1-b30f-c6cbef2c7741')
        ->andReturn(33);
    $repository->shouldReceive('create')
        ->once()
        ->with(Mockery::on(static function (array $payload): bool {
            return $payload['post_title'] === 'New Post'
                && $payload['post_title_slug'] === 'new-post'
                && $payload['category_id'] === 33
                && $payload['post_status'] === 'published';
        }))
        ->andReturn(new Post(
            id: new PostId(1),
            uuid: '95b94731-20ad-4e9d-a61a-1c42e680f111',
            title: 'New Post',
            slug: 'new-post',
            content: '<p>Hello</p>',
            status: 'published',
        ));

    /** @var AuditInterface&MockInterface $audit */
    $audit = Mockery::mock(AuditInterface::class);
    $audit->shouldReceive('log')
        ->once()
        ->with(
            'posts.created',
            'Post created: New Post',
            Mockery::on(static fn(array $properties): bool => ($properties['title'] ?? null) === 'New Post'),
            null,
        );

    $handler = new CreatePostHandler($repository, $audit);

    $dto = new CreatePostDTO(
        postTitle: 'New Post',
        postContent: '<p>Hello</p>',
        postTitleSlug: null,
        postExcerpt: 'Excerpt',
        postCoverImage: null,
        metaTitle: 'Meta',
        metaDescription: 'Description',
        metaKeywords: 'alpha,beta',
        categoryUuid: 'e4a95324-48e9-40f1-b30f-c6cbef2c7741',
        postStatus: 'published',
        publishedAt: '2026-03-06T10:00:00+00:00',
        scheduledAt: null,
    );

    $post = $handler->handle(new CreatePostCommand($dto));

    expect($post->slug)->toBe('new-post')
        ->and($post->status)->toBe('published');
});
