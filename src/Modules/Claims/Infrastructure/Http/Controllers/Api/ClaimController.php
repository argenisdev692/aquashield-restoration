<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\Claims\Application\Commands\BulkDeleteClaimHandler;
use Src\Modules\Claims\Application\Commands\CreateClaimHandler;
use Src\Modules\Claims\Application\Commands\DeleteClaimHandler;
use Src\Modules\Claims\Application\Commands\RestoreClaimHandler;
use Src\Modules\Claims\Application\Commands\UpdateClaimHandler;
use Src\Modules\Claims\Application\DTOs\BulkDeleteClaimData;
use Src\Modules\Claims\Application\DTOs\ClaimFilterData;
use Src\Modules\Claims\Application\DTOs\StoreClaimData;
use Src\Modules\Claims\Application\DTOs\UpdateClaimData;
use Src\Modules\Claims\Application\Queries\GetClaimHandler;
use Src\Modules\Claims\Application\Queries\ListClaimsHandler;
use Src\Modules\Claims\Infrastructure\Http\Requests\BulkDeleteClaimRequest;
use Src\Modules\Claims\Infrastructure\Http\Requests\StoreClaimRequest;
use Src\Modules\Claims\Infrastructure\Http\Requests\UpdateClaimRequest;

/**
 * @OA\Tag(name="Claims", description="Claims CRUD operations")
 */
final class ClaimController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/claims",
     *     tags={"Claims"},
     *     summary="List claims (paginated)",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="claim_status_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="type_damage_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated claims list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ClaimReadModel")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="currentPage", type="integer"),
     *                 @OA\Property(property="lastPage", type="integer"),
     *                 @OA\Property(property="perPage", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListClaimsHandler $handler): JsonResponse
    {
        $claims = $handler->handle(ClaimFilterData::from(request()->query()));

        return response()->json([
            'data' => $claims->items(),
            'meta' => [
                'currentPage' => $claims->currentPage(),
                'lastPage'    => $claims->lastPage(),
                'perPage'     => $claims->perPage(),
                'total'       => $claims->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/claims/{uuid}",
     *     tags={"Claims"},
     *     summary="Show claim detail",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Claim detail", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/ClaimReadModel"))),
     *     @OA\Response(response=404, description="Claim not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetClaimHandler $handler): JsonResponse
    {
        $claim = $handler->handle($uuid);

        if ($claim === null) {
            return response()->json(['message' => 'Claim not found.'], 404);
        }

        return response()->json(['data' => $claim]);
    }

    /**
     * @OA\Post(
     *     path="/api/claims",
     *     tags={"Claims"},
     *     summary="Create claim",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreClaimData")),
     *     @OA\Response(
     *         response=201,
     *         description="Claim created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid", type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreClaimRequest $request, CreateClaimHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreClaimData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Claim created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/claims/{uuid}",
     *     tags={"Claims"},
     *     summary="Update claim",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateClaimData")),
     *     @OA\Response(response=200, description="Claim updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Claim not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateClaimRequest $request, UpdateClaimHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateClaimData::from($request->validated()));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json(['message' => 'Claim updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/claims/{uuid}",
     *     tags={"Claims"},
     *     summary="Soft-delete claim",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Claim deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteClaimHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Claim deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/claims/{uuid}/restore",
     *     tags={"Claims"},
     *     summary="Restore soft-deleted claim",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Claim restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreClaimHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Claim restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/claims/bulk-delete",
     *     tags={"Claims"},
     *     summary="Bulk soft-delete claims",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/BulkDeleteClaimData")),
     *     @OA\Response(
     *         response=200,
     *         description="Claims deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteClaimRequest $request, BulkDeleteClaimHandler $handler): JsonResponse
    {
        $count = $handler->handle(BulkDeleteClaimData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$count} claim record(s).",
            'deleted_count' => $count,
        ]);
    }
}
