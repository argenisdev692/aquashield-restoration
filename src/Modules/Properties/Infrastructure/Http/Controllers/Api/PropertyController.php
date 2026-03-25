<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\Properties\Application\Commands\BulkDeletePropertyHandler;
use Src\Modules\Properties\Application\Commands\CreatePropertyHandler;
use Src\Modules\Properties\Application\Commands\DeletePropertyHandler;
use Src\Modules\Properties\Application\Commands\RestorePropertyHandler;
use Src\Modules\Properties\Application\Commands\UpdatePropertyHandler;
use Src\Modules\Properties\Application\DTOs\BulkDeletePropertyData;
use Src\Modules\Properties\Application\DTOs\PropertyFilterData;
use Src\Modules\Properties\Application\DTOs\StorePropertyData;
use Src\Modules\Properties\Application\DTOs\UpdatePropertyData;
use Src\Modules\Properties\Application\Queries\GetPropertyHandler;
use Src\Modules\Properties\Application\Queries\ListPropertiesHandler;
use Src\Modules\Properties\Infrastructure\Http\Requests\BulkDeletePropertyRequest;
use Src\Modules\Properties\Infrastructure\Http\Requests\StorePropertyRequest;
use Src\Modules\Properties\Infrastructure\Http\Requests\UpdatePropertyRequest;

/**
 * @OA\Tag(name="Properties", description="Property CRUD operations")
 */
final class PropertyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/properties",
     *     tags={"Properties"},
     *     summary="List properties",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated properties list", @OA\JsonContent(
     *         @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="meta", type="object")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListPropertiesHandler $handler): JsonResponse
    {
        $properties = $handler->handle(PropertyFilterData::from(request()->query()));

        return response()->json([
            'data' => $properties->items(),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page'    => $properties->lastPage(),
                'per_page'     => $properties->perPage(),
                'total'        => $properties->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/properties/{uuid}",
     *     tags={"Properties"},
     *     summary="Show property",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Property detail", @OA\JsonContent(type="object")),
     *     @OA\Response(response=404, description="Property not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetPropertyHandler $handler): JsonResponse
    {
        $property = $handler->handle($uuid);

        if ($property === null) {
            return response()->json(['message' => 'Property not found.'], 404);
        }

        return response()->json($property);
    }

    /**
     * @OA\Post(
     *     path="/api/properties",
     *     tags={"Properties"},
     *     summary="Create property",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"property_address"},
     *         @OA\Property(property="property_address", type="string", maxLength=255),
     *         @OA\Property(property="property_address_2", type="string", maxLength=255, nullable=true),
     *         @OA\Property(property="property_state", type="string", maxLength=100, nullable=true),
     *         @OA\Property(property="property_city", type="string", maxLength=100, nullable=true),
     *         @OA\Property(property="property_postal_code", type="string", maxLength=20, nullable=true),
     *         @OA\Property(property="property_country", type="string", maxLength=100, nullable=true),
     *         @OA\Property(property="property_latitude", type="string", maxLength=30, nullable=true),
     *         @OA\Property(property="property_longitude", type="string", maxLength=30, nullable=true)
     *     )),
     *     @OA\Response(response=201, description="Property created", @OA\JsonContent(
     *         @OA\Property(property="uuid", type="string", format="uuid"),
     *         @OA\Property(property="message", type="string")
     *     )),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StorePropertyRequest $request, CreatePropertyHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StorePropertyData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Property created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/properties/{uuid}",
     *     tags={"Properties"},
     *     summary="Update property",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"property_address"},
     *         @OA\Property(property="property_address", type="string", maxLength=255),
     *         @OA\Property(property="property_address_2", type="string", maxLength=255, nullable=true),
     *         @OA\Property(property="property_state", type="string", maxLength=100, nullable=true),
     *         @OA\Property(property="property_city", type="string", maxLength=100, nullable=true),
     *         @OA\Property(property="property_postal_code", type="string", maxLength=20, nullable=true),
     *         @OA\Property(property="property_country", type="string", maxLength=100, nullable=true),
     *         @OA\Property(property="property_latitude", type="string", maxLength=30, nullable=true),
     *         @OA\Property(property="property_longitude", type="string", maxLength=30, nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Property updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Property not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdatePropertyRequest $request, UpdatePropertyHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdatePropertyData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Property updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/properties/{uuid}",
     *     tags={"Properties"},
     *     summary="Soft-delete property",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Property deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeletePropertyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Property deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/properties/{uuid}/restore",
     *     tags={"Properties"},
     *     summary="Restore soft-deleted property",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Property restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestorePropertyHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Property restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/properties/bulk-delete",
     *     tags={"Properties"},
     *     summary="Bulk soft-delete properties",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=200, description="Properties deleted", @OA\JsonContent(
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="deleted_count", type="integer")
     *     )),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeletePropertyRequest $request, BulkDeletePropertyHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeletePropertyData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} property record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
