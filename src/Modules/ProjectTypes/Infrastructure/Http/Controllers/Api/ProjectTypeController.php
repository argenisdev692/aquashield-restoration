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

final class ProjectTypeController extends Controller
{
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

    public function serviceCategories(): JsonResponse
    {
        $serviceCategories = ServiceCategoryEloquentModel::query()
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->get(['uuid', 'category', 'type']);

        return response()->json(['data' => $serviceCategories]);
    }

    public function show(string $uuid, GetProjectTypeHandler $handler): JsonResponse
    {
        $projectType = $handler->handle($uuid);

        if ($projectType === null) {
            return response()->json(['message' => 'Project type not found.'], 404);
        }

        return response()->json($projectType);
    }

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

    public function update(string $uuid, UpdateProjectTypeRequest $request, UpdateProjectTypeHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateProjectTypeData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Project type updated successfully.']);
    }

    public function destroy(string $uuid, DeleteProjectTypeHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Project type deleted successfully.']);
    }

    public function restore(string $uuid, RestoreProjectTypeHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Project type restored successfully.']);
    }

    public function bulkDelete(BulkDeleteProjectTypeRequest $request, BulkDeleteProjectTypeHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteProjectTypeData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} project type record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
