<?php

declare(strict_types=1);

namespace Modules\Blog\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Blog\Application\Commands\CreatePost\CreatePostCommand;
use Modules\Blog\Application\Commands\CreatePost\CreatePostHandler;
use Modules\Blog\Application\Commands\GeneratePostContent\GeneratePostContentCommand;
use Modules\Blog\Application\Commands\GeneratePostContent\GeneratePostContentHandler;
use Modules\Blog\Application\DTOs\GeneratePostContentDTO;
use Modules\Blog\Application\Commands\DeletePost\DeletePostCommand;
use Modules\Blog\Application\Commands\DeletePost\DeletePostHandler;
use Modules\Blog\Application\Commands\RestorePost\RestorePostCommand;
use Modules\Blog\Application\Commands\RestorePost\RestorePostHandler;
use Modules\Blog\Application\Commands\UpdatePost\UpdatePostCommand;
use Modules\Blog\Application\Commands\UpdatePost\UpdatePostHandler;
use Modules\Blog\Application\DTOs\CreatePostDTO;
use Modules\Blog\Application\DTOs\PostFilterDTO;
use Modules\Blog\Application\DTOs\UpdatePostDTO;
use Modules\Blog\Application\Queries\GetPost\GetPostHandler;
use Modules\Blog\Application\Queries\GetPost\GetPostQuery;
use Modules\Blog\Application\Queries\ListPosts\ListPostsHandler;
use Modules\Blog\Application\Queries\ListPosts\ListPostsQuery;
use Modules\Blog\Infrastructure\Http\Requests\CreatePostRequest;
use Modules\Blog\Infrastructure\Http\Requests\GeneratePostContentRequest;
use Modules\Blog\Infrastructure\Http\Requests\PostFilterRequest;
use Modules\Blog\Infrastructure\Http\Requests\UpdatePostRequest;
use Modules\Blog\Infrastructure\Http\Resources\PostResource;

/**
 * @OA\Tag(
 *     name="Posts",
 *     description="Blog post management endpoints"
 * )
 */
final class AdminPostController
{
    public function __construct(
        private readonly CreatePostHandler $createHandler,
        private readonly UpdatePostHandler $updateHandler,
        private readonly DeletePostHandler $deleteHandler,
        private readonly RestorePostHandler $restoreHandler,
        private readonly ListPostsHandler $listHandler,
        private readonly GetPostHandler $getHandler,
        private readonly GeneratePostContentHandler $generateContentHandler,
    ) {
    }

    /**
     * List posts (paginated).
     *
     * @OA\Get(
     *     path="/posts/data/admin",
     *     tags={"Posts"},
     *     summary="List posts",
     *     description="Get paginated list of blog posts with optional filtering",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"draft","published"})),
     *     @OA\Parameter(name="sort_field", in="query", required=false, @OA\Schema(type="string", default="created_at")),
     *     @OA\Parameter(name="sort_direction", in="query", required=false, @OA\Schema(type="string", enum={"asc","desc"}, default="desc")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=10)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(PostFilterRequest $request): JsonResponse
    {
        $filters = PostFilterDTO::from($request->validated());
        $result = $this->listHandler->handle(new ListPostsQuery($filters));

        return response()->json($result);
    }

    /**
     * Get a single post by UUID.
     *
     * @OA\Get(
     *     path="/posts/data/admin/{uuid}",
     *     tags={"Posts"},
     *     summary="Get post by UUID",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="excerpt", type="string", nullable=true),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(string $uuid): JsonResponse
    {
        $post = $this->getHandler->handle(new GetPostQuery($uuid));

        return response()->json([
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Create a new post.
     *
     * @OA\Post(
     *     path="/posts/data/admin",
     *     tags={"Posts"},
     *     summary="Create post",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="excerpt", type="string", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft","published"}),
     *             @OA\Property(property="blog_category_uuid", type="string", format="uuid", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        $post = $this->createHandler->handle(new CreatePostCommand(CreatePostDTO::from($request->validated())));

        return response()->json([
            'data' => new PostResource($post),
        ], 201);
    }

    /**
     * Update a post.
     *
     * @OA\Put(
     *     path="/posts/data/admin/{uuid}",
     *     tags={"Posts"},
     *     summary="Update post",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="excerpt", type="string", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft","published"}),
     *             @OA\Property(property="blog_category_uuid", type="string", format="uuid", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdatePostRequest $request, string $uuid): JsonResponse
    {
        $post = $this->updateHandler->handle(new UpdatePostCommand($uuid, UpdatePostDTO::from($request->validated())));

        return response()->json([
            'data' => new PostResource($post),
        ]);
    }

    /**
     * Soft delete a post.
     *
     * @OA\Delete(
     *     path="/posts/data/admin/{uuid}",
     *     tags={"Posts"},
     *     summary="Delete post",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle(new DeletePostCommand($uuid));

        return response()->json(null, 204);
    }

    /**
     * Restore a soft-deleted post.
     *
     * @OA\Patch(
     *     path="/posts/data/admin/{uuid}/restore",
     *     tags={"Posts"},
     *     summary="Restore deleted post",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle(new RestorePostCommand($uuid));

        return response()->json(['message' => 'Post restored successfully.']);
    }

    /**
     * AI-generate blog post content.
     *
     * @OA\Post(
     *     path="/posts/data/admin/generate-content",
     *     tags={"Posts"},
     *     summary="Generate post content via AI",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"topic","niche"},
     *             @OA\Property(property="topic", type="string"),
     *             @OA\Property(property="niche", type="string"),
     *             @OA\Property(property="word_count", type="integer", default=800)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Generated content",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="post_content", type="string"),
     *                 @OA\Property(property="post_title_slug", type="string"),
     *                 @OA\Property(property="post_excerpt", type="string"),
     *                 @OA\Property(property="meta_title", type="string"),
     *                 @OA\Property(property="meta_description", type="string"),
     *                 @OA\Property(property="meta_keywords", type="string"),
     *                 @OA\Property(property="sources", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function generateContent(GeneratePostContentRequest $request): JsonResponse
    {
        $result = $this->generateContentHandler->handle(
            new GeneratePostContentCommand(GeneratePostContentDTO::from($request->validated())),
        );

        return response()->json([
            'data' => [
                'post_content'     => $result->postContent,
                'post_title_slug'  => $result->postTitleSlug,
                'post_excerpt'     => $result->postExcerpt,
                'meta_title'       => $result->metaTitle,
                'meta_description' => $result->metaDescription,
                'meta_keywords'    => $result->metaKeywords,
                'sources'          => $result->sources,
            ],
        ]);
    }

    /**
     * Bulk soft-delete posts.
     *
     * @OA\Post(
     *     path="/posts/data/admin/bulk-delete",
     *     tags={"Posts"},
     *     summary="Bulk delete posts",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"uuids"},
     *             @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *         )
     *     ),
     *     @OA\Response(response=204, description="Deleted"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        foreach ($validated['uuids'] as $uuid) {
            $this->deleteHandler->handle(new DeletePostCommand($uuid));
        }

        return response()->json(null, 204);
    }
}
