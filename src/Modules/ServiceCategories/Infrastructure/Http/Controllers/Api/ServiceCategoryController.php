<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\ServiceCategories\Application\Commands\BulkDeleteServiceCategoryHandler;
use Src\Modules\ServiceCategories\Application\Commands\CreateServiceCategoryHandler;
use Src\Modules\ServiceCategories\Application\Commands\DeleteServiceCategoryHandler;
use Src\Modules\ServiceCategories\Application\Commands\RestoreServiceCategoryHandler;
use Src\Modules\ServiceCategories\Application\Commands\UpdateServiceCategoryHandler;
use Src\Modules\ServiceCategories\Application\DTOs\BulkDeleteServiceCategoryData;
use Src\Modules\ServiceCategories\Application\DTOs\ServiceCategoryFilterData;
use Src\Modules\ServiceCategories\Application\DTOs\StoreServiceCategoryData;
use Src\Modules\ServiceCategories\Application\DTOs\UpdateServiceCategoryData;
use Src\Modules\ServiceCategories\Application\Queries\GetServiceCategoryHandler;
use Src\Modules\ServiceCategories\Application\Queries\ListServiceCategoriesHandler;
use Src\Modules\ServiceCategories\Infrastructure\Http\Requests\BulkDeleteServiceCategoryRequest;
use Src\Modules\ServiceCategories\Infrastructure\Http\Requests\StoreServiceCategoryRequest;
use Src\Modules\ServiceCategories\Infrastructure\Http\Requests\UpdateServiceCategoryRequest;

/**
 * @OA\Tag(name="Service Categories", description="Service categories CRUD operations")
 */
final class ServiceCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/service-categories",
     *     tags={"Service Categories"},
     *     summary="List service categories",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated service categories list",
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
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListServiceCategoriesHandler $handler): JsonResponse
    {
        $serviceCategories = $handler->handle(ServiceCategoryFilterData::from(request()->query()));

        return response()->json([
            'data' => $serviceCategories->items(),
            'meta' => [
                'current_page' => $serviceCategories->currentPage(),
                'last_page'    => $serviceCategories->lastPage(),
                'per_page'     => $serviceCategories->perPage(),
                'total'        => $serviceCategories->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/service-categories/{uuid}",
     *     tags={"Service Categories"},
     *     summary="Show service category",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Service category detail", @OA\JsonContent(type="object")),
     *     @OA\Response(response=404, description="Service category not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetServiceCategoryHandler $handler): JsonResponse
    {
        $serviceCategory = $handler->handle($uuid);

        if ($serviceCategory === null) {
            return response()->json(['message' => 'Service category not found.'], 404);
        }

        return response()->json($serviceCategory);
    }

    /**
     * @OA\Post(
     *     path="/api/service-categories",
     *     tags={"Service Categories"},
     *     summary="Create service category",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="category", type="string"),
     *         @OA\Property(property="type", type="string", nullable=true)
     *     )),
     *     @OA\Response(
     *         response=201,
     *         description="Service category created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreServiceCategoryRequest $request, CreateServiceCategoryHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreServiceCategoryData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Service category created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/service-categories/{uuid}",
     *     tags={"Service Categories"},
     *     summary="Update service category",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="category", type="string"),
     *         @OA\Property(property="type", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Service category updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Service category not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateServiceCategoryRequest $request, UpdateServiceCategoryHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateServiceCategoryData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Service category updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/service-categories/{uuid}",
     *     tags={"Service Categories"},
     *     summary="Soft-delete service category",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Service category deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteServiceCategoryHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Service category deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/service-categories/{uuid}/restore",
     *     tags={"Service Categories"},
     *     summary="Restore service category",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Service category restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreServiceCategoryHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Service category restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/service-categories/bulk-delete",
     *     tags={"Service Categories"},
     *     summary="Bulk delete service categories",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="Service categories deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteServiceCategoryRequest $request, BulkDeleteServiceCategoryHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteServiceCategoryData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} service category record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
