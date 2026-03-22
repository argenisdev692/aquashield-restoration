<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\ServiceRequests\Application\Commands\BulkDeleteServiceRequestHandler;
use Src\Modules\ServiceRequests\Application\Commands\CreateServiceRequestHandler;
use Src\Modules\ServiceRequests\Application\Commands\DeleteServiceRequestHandler;
use Src\Modules\ServiceRequests\Application\Commands\RestoreServiceRequestHandler;
use Src\Modules\ServiceRequests\Application\Commands\UpdateServiceRequestHandler;
use Src\Modules\ServiceRequests\Application\DTOs\BulkDeleteServiceRequestData;
use Src\Modules\ServiceRequests\Application\DTOs\ServiceRequestFilterData;
use Src\Modules\ServiceRequests\Application\DTOs\StoreServiceRequestData;
use Src\Modules\ServiceRequests\Application\DTOs\UpdateServiceRequestData;
use Src\Modules\ServiceRequests\Application\Queries\GetServiceRequestHandler;
use Src\Modules\ServiceRequests\Application\Queries\ListServiceRequestsHandler;
use Src\Modules\ServiceRequests\Infrastructure\Http\Requests\BulkDeleteServiceRequestRequest;
use Src\Modules\ServiceRequests\Infrastructure\Http\Requests\StoreServiceRequestRequest;
use Src\Modules\ServiceRequests\Infrastructure\Http\Requests\UpdateServiceRequestRequest;

/**
 * @OA\Tag(name="Service Requests", description="Service request CRUD operations")
 */
final class ServiceRequestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/service-requests",
     *     tags={"Service Requests"},
     *     summary="List service requests",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated service requests list", @OA\JsonContent(
     *         @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="meta", type="object")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListServiceRequestsHandler $handler): JsonResponse
    {
        $serviceRequests = $handler->handle(ServiceRequestFilterData::from(request()->query()));

        return response()->json([
            'data' => $serviceRequests->items(),
            'meta' => [
                'current_page' => $serviceRequests->currentPage(),
                'last_page' => $serviceRequests->lastPage(),
                'per_page' => $serviceRequests->perPage(),
                'total' => $serviceRequests->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/service-requests/{uuid}",
     *     tags={"Service Requests"},
     *     summary="Show service request",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Service request detail", @OA\JsonContent(type="object")),
     *     @OA\Response(response=404, description="Service request not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetServiceRequestHandler $handler): JsonResponse
    {
        $serviceRequest = $handler->handle($uuid);

        if ($serviceRequest === null) {
            return response()->json(['message' => 'Service request not found.'], 404);
        }

        return response()->json($serviceRequest);
    }

    /**
     * @OA\Post(
     *     path="/api/service-requests",
     *     tags={"Service Requests"},
     *     summary="Create service request",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"requested_service"},
     *         @OA\Property(property="requested_service", type="string", maxLength=255)
     *     )),
     *     @OA\Response(response=201, description="Service request created", @OA\JsonContent(
     *         @OA\Property(property="uuid", type="string", format="uuid"),
     *         @OA\Property(property="message", type="string")
     *     )),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreServiceRequestRequest $request, CreateServiceRequestHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreServiceRequestData::from($request->validated()));

        return response()->json([
            'uuid' => $uuid,
            'message' => 'Service request created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/service-requests/{uuid}",
     *     tags={"Service Requests"},
     *     summary="Update service request",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"requested_service"},
     *         @OA\Property(property="requested_service", type="string", maxLength=255)
     *     )),
     *     @OA\Response(response=200, description="Service request updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Service request not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateServiceRequestRequest $request, UpdateServiceRequestHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateServiceRequestData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Service request updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/service-requests/{uuid}",
     *     tags={"Service Requests"},
     *     summary="Soft-delete service request",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Service request deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteServiceRequestHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Service request deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/service-requests/{uuid}/restore",
     *     tags={"Service Requests"},
     *     summary="Restore soft-deleted service request",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Service request restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreServiceRequestHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Service request restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/service-requests/bulk-delete",
     *     tags={"Service Requests"},
     *     summary="Bulk soft-delete service requests",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=200, description="Service requests deleted", @OA\JsonContent(
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="deleted_count", type="integer")
     *     )),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteServiceRequestRequest $request, BulkDeleteServiceRequestHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteServiceRequestData::from($request->validated()));

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} service request record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
