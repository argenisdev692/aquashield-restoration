<?php

declare(strict_types=1);

namespace Modules\Blog\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Blog\Application\Commands\CreateBlogCategory\CreateBlogCategoryCommand;
use Modules\Blog\Application\Commands\CreateBlogCategory\CreateBlogCategoryHandler;
use Modules\Blog\Application\Commands\DeleteBlogCategory\DeleteBlogCategoryCommand;
use Modules\Blog\Application\Commands\DeleteBlogCategory\DeleteBlogCategoryHandler;
use Modules\Blog\Application\Commands\UpdateBlogCategory\UpdateBlogCategoryCommand;
use Modules\Blog\Application\Commands\UpdateBlogCategory\UpdateBlogCategoryHandler;
use Modules\Blog\Application\Commands\RestoreBlogCategory\RestoreBlogCategoryCommand;
use Modules\Blog\Application\Commands\RestoreBlogCategory\RestoreBlogCategoryHandler;
use Modules\Blog\Application\DTOs\BlogCategoryFilterDTO;
use Modules\Blog\Application\DTOs\CreateBlogCategoryDTO;
use Modules\Blog\Application\DTOs\UpdateBlogCategoryDTO;
use Modules\Blog\Application\Queries\GetBlogCategory\GetBlogCategoryHandler;
use Modules\Blog\Application\Queries\GetBlogCategory\GetBlogCategoryQuery;
use Modules\Blog\Application\Queries\ListBlogCategories\ListBlogCategoriesHandler;
use Modules\Blog\Application\Queries\ListBlogCategories\ListBlogCategoriesQuery;
use Modules\Blog\Infrastructure\Http\Requests\BlogCategoryFilterRequest;
use Modules\Blog\Infrastructure\Http\Requests\CreateBlogCategoryRequest;
use Modules\Blog\Infrastructure\Http\Requests\UpdateBlogCategoryRequest;
use Modules\Blog\Infrastructure\Http\Resources\BlogCategoryResource;

/**
 * AdminBlogCategoryController — Full CRUD Web-JSON API for blog category management.
 *
 * @OA\Tag(
 *     name="Blog Categories",
 *     description="Blog category management endpoints"
 * )
 */
final class AdminBlogCategoryController
{
    public function __construct(
        private readonly CreateBlogCategoryHandler $createHandler,
        private readonly UpdateBlogCategoryHandler $updateHandler,
        private readonly DeleteBlogCategoryHandler $deleteHandler,
        private readonly RestoreBlogCategoryHandler $restoreHandler,
        private readonly ListBlogCategoriesHandler $listHandler,
        private readonly GetBlogCategoryHandler $getHandler,
    ) {
    }

    /**
     * List blog categories (paginated).
     *
     * @OA\Get(
     *     path="/blog-categories/data/admin",
     *     tags={"Blog Categories"},
     *     summary="List blog categories",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
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
    public function index(BlogCategoryFilterRequest $request): JsonResponse
    {
        $filters = BlogCategoryFilterDTO::from($request->validated());
        $result = $this->listHandler->handle(new ListBlogCategoriesQuery($filters));

        return response()->json($result);
    }

    /**
     * Get a single blog category by UUID.
     *
     * @OA\Get(
     *     path="/blog-categories/data/admin/{uuid}",
     *     tags={"Blog Categories"},
     *     summary="Get blog category by UUID",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="uuid", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="slug", type="string"),
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
        $category = $this->getHandler->handle(new GetBlogCategoryQuery($uuid));

        return response()->json([
            'data' => new BlogCategoryResource($category),
        ]);
    }

    /**
     * Create a new blog category.
     *
     * @OA\Post(
     *     path="/blog-categories/data/admin",
     *     tags={"Blog Categories"},
     *     summary="Create blog category",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(CreateBlogCategoryRequest $request): JsonResponse
    {
        $dto = CreateBlogCategoryDTO::from($request->validated());
        $category = $this->createHandler->handle(new CreateBlogCategoryCommand($dto));

        return response()->json([
            'data' => new BlogCategoryResource($category),
        ], 201);
    }

    /**
     * Update a blog category.
     *
     * @OA\Put(
     *     path="/blog-categories/data/admin/{uuid}",
     *     tags={"Blog Categories"},
     *     summary="Update blog category",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(@OA\Property(property="data", type="object"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(UpdateBlogCategoryRequest $request, string $uuid): JsonResponse
    {
        $dto = UpdateBlogCategoryDTO::from($request->validated());
        $category = $this->updateHandler->handle(new UpdateBlogCategoryCommand($uuid, $dto));

        return response()->json([
            'data' => new BlogCategoryResource($category),
        ]);
    }

    /**
     * Soft delete a blog category.
     *
     * @OA\Delete(
     *     path="/blog-categories/data/admin/{uuid}",
     *     tags={"Blog Categories"},
     *     summary="Delete blog category",
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
        $this->deleteHandler->handle(new DeleteBlogCategoryCommand($uuid));

        return response()->json(null, 204);
    }

    /**
     * Restore a soft-deleted blog category.
     *
     * @OA\Patch(
     *     path="/blog-categories/data/admin/{uuid}/restore",
     *     tags={"Blog Categories"},
     *     summary="Restore deleted blog category",
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
        $this->restoreHandler->handle(new RestoreBlogCategoryCommand($uuid));

        return response()->json(['message' => 'Blog category restored successfully.']);
    }

    /**
     * Bulk soft-delete blog categories.
     *
     * @OA\Post(
     *     path="/blog-categories/data/admin/bulk-delete",
     *     tags={"Blog Categories"},
     *     summary="Bulk delete blog categories",
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
            $this->deleteHandler->handle(new DeleteBlogCategoryCommand($uuid));
        }

        return response()->json(null, 204);
    }
}
