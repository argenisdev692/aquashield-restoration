<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\ClaimStatuses\Application\Commands\BulkDeleteClaimStatusHandler;
use Src\Modules\ClaimStatuses\Application\Commands\CreateClaimStatusHandler;
use Src\Modules\ClaimStatuses\Application\Commands\DeleteClaimStatusHandler;
use Src\Modules\ClaimStatuses\Application\Commands\RestoreClaimStatusHandler;
use Src\Modules\ClaimStatuses\Application\Commands\UpdateClaimStatusHandler;
use Src\Modules\ClaimStatuses\Application\DTOs\BulkDeleteClaimStatusData;
use Src\Modules\ClaimStatuses\Application\DTOs\ClaimStatusFilterData;
use Src\Modules\ClaimStatuses\Application\DTOs\StoreClaimStatusData;
use Src\Modules\ClaimStatuses\Application\DTOs\UpdateClaimStatusData;
use Src\Modules\ClaimStatuses\Application\Queries\GetClaimStatusHandler;
use Src\Modules\ClaimStatuses\Application\Queries\ListClaimStatusesHandler;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Requests\BulkDeleteClaimStatusRequest;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Requests\StoreClaimStatusRequest;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Requests\UpdateClaimStatusRequest;

/**
 * @OA\Tag(name="Claim Statuses", description="Claim status catalog CRUD operations")
 */
final class ClaimStatusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/claim-statuses",
     *     tags={"Claim Statuses"},
     *     summary="List claim statuses",
     *     @OA\Parameter(name="search",    in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status",    in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to",   in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page",      in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page",  in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated claim statuses list",
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
    public function index(ListClaimStatusesHandler $handler): JsonResponse
    {
        $claimStatuses = $handler->handle(ClaimStatusFilterData::from(request()->query()));

        return response()->json([
            'data' => $claimStatuses->items(),
            'meta' => [
                'current_page' => $claimStatuses->currentPage(),
                'last_page'    => $claimStatuses->lastPage(),
                'per_page'     => $claimStatuses->perPage(),
                'total'        => $claimStatuses->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/claim-statuses/{uuid}",
     *     tags={"Claim Statuses"},
     *     summary="Show claim status",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Claim status detail", @OA\JsonContent(
     *         @OA\Property(property="uuid",               type="string", format="uuid"),
     *         @OA\Property(property="claim_status_name",  type="string"),
     *         @OA\Property(property="background_color",   type="string", nullable=true, example="#3B82F6"),
     *         @OA\Property(property="created_at",         type="string", format="date-time"),
     *         @OA\Property(property="updated_at",         type="string", format="date-time"),
     *         @OA\Property(property="deleted_at",         type="string", format="date-time", nullable=true)
     *     )),
     *     @OA\Response(response=404, description="Claim status not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetClaimStatusHandler $handler): JsonResponse
    {
        $claimStatus = $handler->handle($uuid);

        if ($claimStatus === null) {
            return response()->json(['message' => 'Claim status not found.'], 404);
        }

        return response()->json($claimStatus);
    }

    /**
     * @OA\Post(
     *     path="/api/claim-statuses",
     *     tags={"Claim Statuses"},
     *     summary="Create claim status",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"claim_status_name"},
     *         @OA\Property(property="claim_status_name", type="string", maxLength=255, example="Open"),
     *         @OA\Property(property="background_color",  type="string", nullable=true, example="#3B82F6")
     *     )),
     *     @OA\Response(
     *         response=201,
     *         description="Claim status created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid",    type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreClaimStatusRequest $request, CreateClaimStatusHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreClaimStatusData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Claim status created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/claim-statuses/{uuid}",
     *     tags={"Claim Statuses"},
     *     summary="Update claim status",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"claim_status_name"},
     *         @OA\Property(property="claim_status_name", type="string", maxLength=255),
     *         @OA\Property(property="background_color",  type="string", nullable=true, example="#F59E0B")
     *     )),
     *     @OA\Response(response=200, description="Claim status updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Claim status not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateClaimStatusRequest $request, UpdateClaimStatusHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateClaimStatusData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Claim status updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/claim-statuses/{uuid}",
     *     tags={"Claim Statuses"},
     *     summary="Soft-delete claim status",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Claim status deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteClaimStatusHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Claim status deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/claim-statuses/{uuid}/restore",
     *     tags={"Claim Statuses"},
     *     summary="Restore soft-deleted claim status",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Claim status restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreClaimStatusHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Claim status restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/claim-statuses/bulk-delete",
     *     tags={"Claim Statuses"},
     *     summary="Bulk soft-delete claim statuses",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="Claim statuses deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message",       type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteClaimStatusRequest $request, BulkDeleteClaimStatusHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteClaimStatusData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} claim status record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
