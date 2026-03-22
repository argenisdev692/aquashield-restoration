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

/**
 * @OA\Tag(name="Type Damages", description="Type damage catalog CRUD operations")
 */
final class TypeDamageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/type-damages",
     *     tags={"Type Damages"},
     *     summary="List type damages",
     *     @OA\Parameter(name="search",   in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status",   in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="severity", in="query", required=false, @OA\Schema(type="string", enum={"low","medium","high"})),
     *     @OA\Parameter(name="date_from",in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to",  in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page",     in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated type damages list",
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
    public function index(ListTypeDamagesHandler $handler): JsonResponse
    {
        $typeDamages = $handler->handle(TypeDamageFilterData::from(request()->query()));

        return response()->json([
            'data' => $typeDamages->items(),
            'meta' => [
                'current_page' => $typeDamages->currentPage(),
                'last_page'    => $typeDamages->lastPage(),
                'per_page'     => $typeDamages->perPage(),
                'total'        => $typeDamages->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/type-damages/{uuid}",
     *     tags={"Type Damages"},
     *     summary="Show type damage",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Type damage detail", @OA\JsonContent(
     *         @OA\Property(property="uuid",             type="string", format="uuid"),
     *         @OA\Property(property="type_damage_name", type="string"),
     *         @OA\Property(property="description",      type="string", nullable=true),
     *         @OA\Property(property="severity",         type="string", enum={"low","medium","high"}),
     *         @OA\Property(property="created_at",       type="string", format="date-time"),
     *         @OA\Property(property="updated_at",       type="string", format="date-time"),
     *         @OA\Property(property="deleted_at",       type="string", format="date-time", nullable=true)
     *     )),
     *     @OA\Response(response=404, description="Type damage not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetTypeDamageHandler $handler): JsonResponse
    {
        $typeDamage = $handler->handle($uuid);

        if ($typeDamage === null) {
            return response()->json(['message' => 'Type damage not found.'], 404);
        }

        return response()->json($typeDamage);
    }

    /**
     * @OA\Post(
     *     path="/api/type-damages",
     *     tags={"Type Damages"},
     *     summary="Create type damage",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"type_damage_name","severity"},
     *         @OA\Property(property="type_damage_name", type="string", maxLength=255),
     *         @OA\Property(property="description",      type="string", nullable=true),
     *         @OA\Property(property="severity",         type="string", enum={"low","medium","high"})
     *     )),
     *     @OA\Response(
     *         response=201,
     *         description="Type damage created",
     *         @OA\JsonContent(
     *             @OA\Property(property="uuid",    type="string", format="uuid"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreTypeDamageRequest $request, CreateTypeDamageHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(StoreTypeDamageData::from($request->validated()));

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Type damage created successfully.',
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/type-damages/{uuid}",
     *     tags={"Type Damages"},
     *     summary="Update type damage",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"type_damage_name","severity"},
     *         @OA\Property(property="type_damage_name", type="string", maxLength=255),
     *         @OA\Property(property="description",      type="string", nullable=true),
     *         @OA\Property(property="severity",         type="string", enum={"low","medium","high"})
     *     )),
     *     @OA\Response(response=200, description="Type damage updated", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     @OA\Response(response=404, description="Type damage not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateTypeDamageRequest $request, UpdateTypeDamageHandler $handler): JsonResponse
    {
        try {
            $handler->handle($uuid, UpdateTypeDamageData::from($request->validated()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Type damage updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/type-damages/{uuid}",
     *     tags={"Type Damages"},
     *     summary="Soft-delete type damage",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Type damage deleted", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteTypeDamageHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Type damage deleted successfully.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/type-damages/{uuid}/restore",
     *     tags={"Type Damages"},
     *     summary="Restore soft-deleted type damage",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Type damage restored", @OA\JsonContent(@OA\Property(property="message", type="string"))),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid, RestoreTypeDamageHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Type damage restored successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/type-damages/bulk-delete",
     *     tags={"Type Damages"},
     *     summary="Bulk soft-delete type damages",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="Type damages deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message",       type="string"),
     *             @OA\Property(property="deleted_count", type="integer")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteTypeDamageRequest $request, BulkDeleteTypeDamageHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteTypeDamageData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} type damage record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
