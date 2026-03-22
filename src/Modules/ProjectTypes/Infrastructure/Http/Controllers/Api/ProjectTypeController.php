<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\ProjectTypes\Application\Commands\BulkDeleteProjectTypeHandler;
use Src\Modules\ProjectTypes\Application\Commands\CreateProjectTypeHandler;
use Src\Modules\ProjectTypes\Application\Commands\DeleteProjectTypeHandler;
use Src\Modules\ProjectTypes\Application\Commands\RestoreProjectTypeHandler;
use Src\Modules\ProjectTypes\Application\Commands\UpdateProjectTypeHandler;
use Src\Modules\ProjectTypes\Application\DTOs\BulkDeleteProjectTypeData;
use Src\Modules\ProjectTypes\Application\DTOs\ProjectTypeFilterData;
use Src\Modules\ProjectTypes\Application\DTOs\StoreProjectTypeData;
use Src\Modules\ProjectTypes\Application\DTOs\UpdateProjectTypeData;
use Src\Modules\ProjectTypes\Application\Queries\GetProjectTypeHandler;
use Src\Modules\ProjectTypes\Application\Queries\ListProjectTypesHandler;
use Src\Modules\ProjectTypes\Infrastructure\Http\Requests\BulkDeleteProjectTypeRequest;
use Src\Modules\ProjectTypes\Infrastructure\Http\Requests\StoreProjectTypeRequest;
use Src\Modules\ProjectTypes\Infrastructure\Http\Requests\UpdateProjectTypeRequest;
use Src\Modules\ServiceCategories\Infrastructure\Persistence\Eloquent\Models\ServiceCategoryEloquentModel;

/**
 * @OA\Tag(name="Project Types", description="Project types CRUD operations")
 */
final class ProjectTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/project-types",
     *     tags={"Project Types"},
     *     summary="List project types",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="service_category_uuid", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated project types list",
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
    public function index(ListProjectTypesHandler $handler): JsonResponse
    {
        $projectTypes = $handler->handle(ProjectTypeFilterData::from(request()->query()));

        return response()->json([
            'data' => $projectTypes->items(),
            'meta' => [
                'current_page' => $projectTypes->currentPage(),
                'last_page'    => $projectTypes->lastPage(),
                'per_page'     => $projectTypes->perPage(),
                'total'        => $projectTypes->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/project-types/service-categories",
     *     tags={"Project Types"},
     *     summary="List available service categories for project type filters",
     *     @OA\Response(
     *         response=200,
     *         description="Service categories list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="uuid", type="string", format="uuid"),
     *                 @OA\Property(property="category", type="string"),
     *                 @OA\Property(property="type", type="string", nullable=true)
     *             ))
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function serviceCategories(): JsonResponse
    {
        $serviceCategories = ServiceCategoryEloquentModel::query()
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->get(['uuid', 'category', 'type']);

        return response()->json(['data' => $serviceCategories]);
    }

    /**
     * @OA\Get(
     *     path="/api/project-types/{uuid}",
     *     tags={"Project Types"},
     *     summary="Show project type",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Project type detail", @OA\JsonContent(type="object")),
     *     @OA\Response(response=404, description="Project type not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetProjectTypeHandler $handler): JsonResponse
    {
        $projectType = $handler->handle($uuid);

        if ($projectType === null) {
            return response()->json(['message' => 'Project type not found.'], 404);
        }

        return response()->json($projectType);
    }

    /**
     * @OA\Post(
     *     path="/api/project-types",
     *     tags={"Project Types"},
     *     summary="Create project type",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="description", type="string", nullable=true),
     *         @OA\Property(property="status", type="string", enum={"active","inactive"}),
     *         @OA\Property(property="service_category_uuid", type="string", format="uuid")
     *     )),
     *     @OA\Response(
     *         response=201,
     *         description="Project type created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation or business rule error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreProjectTypeRequest $request, CreateProjectTypeHandler $handler): JsonResponse
    {
        try {
            $uuid = $handler->handle(StoreProjectTypeData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Project type created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/project-types/{uuid}",
     *     tags={"Project Types"},
     *     summary="Update project type",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="description", type="string", nullable=true),
     *         @OA\Property(property="status", type="string", enum={"active","inactive"}),
     *         @OA\Property(property="service_category_uuid", type="string", format="uuid")
     *     )),
     *     @OA\Response(response=200, description="Project type updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Project type not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateProjectTypeRequest $request, UpdateProjectTypeHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateProjectTypeData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Project type updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/project-types/{uuid}",
     *     tags={"Project Types"},
     *     summary="Soft-delete project type",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Project type deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteProjectTypeHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Project type deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/project-types/{uuid}/restore",
     *     tags={"Project Types"},
     *     summary="Restore project type",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Project type restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreProjectTypeHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Project type restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/project-types/bulk-delete",
     *     tags={"Project Types"},
     *     summary="Bulk delete project types",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="Project types deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteProjectTypeRequest $request, BulkDeleteProjectTypeHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteProjectTypeData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} project type record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
