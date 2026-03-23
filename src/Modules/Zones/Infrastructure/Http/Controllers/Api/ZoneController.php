<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\Zones\Application\Commands\BulkDeleteZoneHandler;
use Src\Modules\Zones\Application\Commands\CreateZoneHandler;
use Src\Modules\Zones\Application\Commands\DeleteZoneHandler;
use Src\Modules\Zones\Application\Commands\RestoreZoneHandler;
use Src\Modules\Zones\Application\Commands\UpdateZoneHandler;
use Src\Modules\Zones\Application\DTOs\BulkDeleteZoneData;
use Src\Modules\Zones\Application\DTOs\StoreZoneData;
use Src\Modules\Zones\Application\DTOs\UpdateZoneData;
use Src\Modules\Zones\Application\DTOs\ZoneFilterData;
use Src\Modules\Zones\Application\Queries\GetZoneHandler;
use Src\Modules\Zones\Application\Queries\ListZonesHandler;
use Src\Modules\Zones\Infrastructure\Http\Requests\BulkDeleteZoneRequest;
use Src\Modules\Zones\Infrastructure\Http\Requests\StoreZoneRequest;
use Src\Modules\Zones\Infrastructure\Http\Requests\UpdateZoneRequest;

/**
 * @OA\Tag(name="Zones", description="Zone catalog CRUD operations")
 */
final class ZoneController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/zones",
     *     tags={"Zones"},
     *     summary="List zones",
     *     @OA\Parameter(name="search",    in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status",    in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="zone_type", in="query", required=false, @OA\Schema(type="string", enum={"interior","exterior","basement","attic","garage","crawlspace"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to",   in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page",      in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page",  in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated zones list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page",    type="integer"),
     *                 @OA\Property(property="per_page",     type="integer"),
     *                 @OA\Property(property="total",        type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListZonesHandler $handler): JsonResponse
    {
        $zones = $handler->handle(ZoneFilterData::from(request()->query()));

        return response()->json([
            'data' => $zones->items(),
            'meta' => [
                'current_page' => $zones->currentPage(),
                'last_page'    => $zones->lastPage(),
                'per_page'     => $zones->perPage(),
                'total'        => $zones->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/zones/{uuid}",
     *     tags={"Zones"},
     *     summary="Show zone",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Zone detail", @OA\JsonContent(
     *         @OA\Property(property="uuid",        type="string", format="uuid"),
     *         @OA\Property(property="zone_name",   type="string"),
     *         @OA\Property(property="zone_type",   type="string", enum={"interior","exterior","basement","attic","garage","crawlspace"}),
     *         @OA\Property(property="code",        type="string", nullable=true),
     *         @OA\Property(property="description", type="string", nullable=true),
     *         @OA\Property(property="user_id",     type="integer"),
     *         @OA\Property(property="created_at",  type="string", format="date-time"),
     *         @OA\Property(property="updated_at",  type="string", format="date-time"),
     *         @OA\Property(property="deleted_at",  type="string", format="date-time", nullable=true)
     *     )),
     *     @OA\Response(response=404, description="Zone not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetZoneHandler $handler): JsonResponse
    {
        $zone = $handler->handle($uuid);

        if ($zone === null) {
            return response()->json(['message' => 'Zone not found.'], 404);
        }

        return response()->json($zone);
    }

    /**
     * @OA\Post(
     *     path="/api/zones",
     *     tags={"Zones"},
     *     summary="Create zone",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"zone_name","user_id"},
     *         @OA\Property(property="zone_name",   type="string", maxLength=255),
     *         @OA\Property(property="zone_type",   type="string", enum={"interior","exterior","basement","attic","garage","crawlspace"}),
     *         @OA\Property(property="code",        type="string", nullable=true, maxLength=50),
     *         @OA\Property(property="description", type="string", nullable=true),
     *         @OA\Property(property="user_id",     type="integer")
     *     )),
     *     @OA\Response(
     *         response=201,
     *         description="Zone created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid",    type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreZoneRequest $request, CreateZoneHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreZoneData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Zone created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/zones/{uuid}",
     *     tags={"Zones"},
     *     summary="Update zone",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"zone_name","user_id"},
     *         @OA\Property(property="zone_name",   type="string", maxLength=255),
     *         @OA\Property(property="zone_type",   type="string", enum={"interior","exterior","basement","attic","garage","crawlspace"}),
     *         @OA\Property(property="code",        type="string", nullable=true, maxLength=50),
     *         @OA\Property(property="description", type="string", nullable=true),
     *         @OA\Property(property="user_id",     type="integer")
     *     )),
     *     @OA\Response(response=200, description="Zone updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Zone not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateZoneRequest $request, UpdateZoneHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateZoneData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Zone updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/zones/{uuid}",
     *     tags={"Zones"},
     *     summary="Soft-delete zone",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Zone deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteZoneHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Zone deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/zones/{uuid}/restore",
     *     tags={"Zones"},
     *     summary="Restore soft-deleted zone",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Zone restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreZoneHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Zone restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/zones/bulk-delete",
     *     tags={"Zones"},
     *     summary="Bulk soft-delete zones",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="Zones deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message",       type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteZoneRequest $request, BulkDeleteZoneHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteZoneData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} zone record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
