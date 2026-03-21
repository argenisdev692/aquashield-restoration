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

final class ServiceCategoryController extends Controller
{
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

    public function show(string $uuid, GetServiceCategoryHandler $handler): JsonResponse
    {
        $serviceCategory = $handler->handle($uuid);

        if ($serviceCategory === null) {
            return response()->json(['message' => 'Service category not found.'], 404);
        }

        return response()->json($serviceCategory);
    }

    public function store(StoreServiceCategoryRequest $request, CreateServiceCategoryHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreServiceCategoryData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Service category created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateServiceCategoryRequest $request, UpdateServiceCategoryHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateServiceCategoryData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Service category updated successfully.']);
    }

    public function destroy(string $uuid, DeleteServiceCategoryHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Service category deleted successfully.']);
    }

    public function restore(string $uuid, RestoreServiceCategoryHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Service category restored successfully.']);
    }

    public function bulkDelete(BulkDeleteServiceCategoryRequest $request, BulkDeleteServiceCategoryHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteServiceCategoryData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} service category record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
