<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\TypeDamages\Application\Commands\BulkDeleteTypeDamageHandler;
use Src\Modules\TypeDamages\Application\Commands\CreateTypeDamageHandler;
use Src\Modules\TypeDamages\Application\Commands\DeleteTypeDamageHandler;
use Src\Modules\TypeDamages\Application\Commands\RestoreTypeDamageHandler;
use Src\Modules\TypeDamages\Application\Commands\UpdateTypeDamageHandler;
use Src\Modules\TypeDamages\Application\DTOs\BulkDeleteTypeDamageData;
use Src\Modules\TypeDamages\Application\DTOs\StoreTypeDamageData;
use Src\Modules\TypeDamages\Application\DTOs\TypeDamageFilterData;
use Src\Modules\TypeDamages\Application\DTOs\UpdateTypeDamageData;
use Src\Modules\TypeDamages\Application\Queries\GetTypeDamageHandler;
use Src\Modules\TypeDamages\Application\Queries\ListTypeDamagesHandler;
use Src\Modules\TypeDamages\Infrastructure\Http\Requests\BulkDeleteTypeDamageRequest;
use Src\Modules\TypeDamages\Infrastructure\Http\Requests\StoreTypeDamageRequest;
use Src\Modules\TypeDamages\Infrastructure\Http\Requests\UpdateTypeDamageRequest;

final class TypeDamageController extends Controller
{
    public function index(ListTypeDamagesHandler $handler): JsonResponse
    {
        $typeDamages = $handler->handle(TypeDamageFilterData::from(request()->query()));

        return response()->json([
            'data' => $typeDamages->items(),
            'meta' => [
                'current_page' => $typeDamages->currentPage(),
                'last_page' => $typeDamages->lastPage(),
                'per_page' => $typeDamages->perPage(),
                'total' => $typeDamages->total(),
            ],
        ]);
    }

    public function show(string $uuid, GetTypeDamageHandler $handler): JsonResponse
    {
        $typeDamage = $handler->handle($uuid);

        if ($typeDamage === null) {
            return response()->json(['message' => 'Type damage not found.'], 404);
        }

        return response()->json($typeDamage);
    }

    public function store(StoreTypeDamageRequest $request, CreateTypeDamageHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreTypeDamageData::from($request->validated()));

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Type damage created successfully.',
        ], 201);
    }

    public function update(string $uuid, UpdateTypeDamageRequest $request, UpdateTypeDamageHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateTypeDamageData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Type damage updated successfully.']);
    }

    public function destroy(string $uuid, DeleteTypeDamageHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Type damage deleted successfully.']);
    }

    public function restore(string $uuid, RestoreTypeDamageHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Type damage restored successfully.']);
    }

    public function bulkDelete(BulkDeleteTypeDamageRequest $request, BulkDeleteTypeDamageHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteTypeDamageData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} type damage record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
