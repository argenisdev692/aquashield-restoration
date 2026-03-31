<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Src\Modules\ScopeSheets\Application\Commands\BulkDeleteScopeSheetHandler;
use Src\Modules\ScopeSheets\Application\Commands\CreateScopeSheetHandler;
use Src\Modules\ScopeSheets\Application\Commands\DeleteScopeSheetHandler;
use Src\Modules\ScopeSheets\Application\Commands\RestoreScopeSheetHandler;
use Src\Modules\ScopeSheets\Application\Commands\UpdateScopeSheetHandler;
use Src\Modules\ScopeSheets\Application\DTOs\BulkDeleteScopeSheetData;
use Src\Modules\ScopeSheets\Application\DTOs\ScopeSheetFilterData;
use Src\Modules\ScopeSheets\Application\DTOs\StoreScopeSheetData;
use Src\Modules\ScopeSheets\Application\DTOs\UpdateScopeSheetData;
use Src\Modules\ScopeSheets\Application\Queries\GetScopeSheetHandler;
use Src\Modules\ScopeSheets\Application\Queries\ListScopeSheetsHandler;
use Src\Modules\ScopeSheets\Infrastructure\Http\Requests\BulkDeleteScopeSheetRequest;
use Src\Modules\ScopeSheets\Infrastructure\Http\Requests\StoreScopeSheetRequest;
use Src\Modules\ScopeSheets\Infrastructure\Http\Requests\UpdateScopeSheetRequest;

/**
 * @OA\Tag(name="Scope Sheets", description="Scope Sheet CRUD operations")
 */
final class ScopeSheetController extends Controller
{
    public function __construct(
        private readonly ListScopeSheetsHandler $listHandler,
        private readonly GetScopeSheetHandler $getHandler,
        private readonly CreateScopeSheetHandler $createHandler,
        private readonly UpdateScopeSheetHandler $updateHandler,
        private readonly DeleteScopeSheetHandler $deleteHandler,
        private readonly RestoreScopeSheetHandler $restoreHandler,
        private readonly BulkDeleteScopeSheetHandler $bulkDeleteHandler,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/scope-sheets/admin",
     *     tags={"Scope Sheets"},
     *     summary="List scope sheets (paginated)",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="claim_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated list of scope sheets"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(): JsonResponse
    {
        $filters = ScopeSheetFilterData::from(request()->all());
        $result  = $this->listHandler->handle($filters);

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/scope-sheets/admin",
     *     tags={"Scope Sheets"},
     *     summary="Create a new scope sheet",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreScopeSheetData")),
     *     @OA\Response(response=201, description="Scope sheet created"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreScopeSheetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $data = StoreScopeSheetData::from([
            'claimId'                => $validated['claim_id'],
            'generatedBy'            => $validated['generated_by'],
            'scopeSheetDescription'  => $validated['scope_sheet_description'] ?? null,
            'presentations'          => $validated['presentations'] ?? [],
            'zones'                  => $validated['zones'] ?? [],
        ]);

        $uuid = $this->createHandler->handle($data);

        return response()->json(['uuid' => $uuid, 'message' => 'Scope sheet created.'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/scope-sheets/admin/{uuid}",
     *     tags={"Scope Sheets"},
     *     summary="Get a scope sheet by UUID",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Scope sheet detail"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid): JsonResponse
    {
        $result = $this->getHandler->handle($uuid);

        if ($result === null) {
            return response()->json(['message' => 'Scope sheet not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * @OA\Put(
     *     path="/api/scope-sheets/admin/{uuid}",
     *     tags={"Scope Sheets"},
     *     summary="Update a scope sheet",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateScopeSheetData")),
     *     @OA\Response(response=200, description="Scope sheet updated"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(UpdateScopeSheetRequest $request, string $uuid): JsonResponse
    {
        $validated = $request->validated();

        $data = UpdateScopeSheetData::from([
            'scopeSheetDescription' => $validated['scope_sheet_description'] ?? null,
            'presentations'         => $validated['presentations'] ?? [],
            'zones'                 => $validated['zones'] ?? [],
        ]);

        $this->updateHandler->handle($uuid, $data);

        return response()->json(['message' => 'Scope sheet updated.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/scope-sheets/admin/{uuid}",
     *     tags={"Scope Sheets"},
     *     summary="Soft-delete a scope sheet",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Scope sheet deleted"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->deleteHandler->handle($uuid);

        return response()->json(['message' => 'Scope sheet deleted.']);
    }

    /**
     * @OA\Patch(
     *     path="/api/scope-sheets/admin/{uuid}/restore",
     *     tags={"Scope Sheets"},
     *     summary="Restore a soft-deleted scope sheet",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Scope sheet restored"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function restore(string $uuid): JsonResponse
    {
        $this->restoreHandler->handle($uuid);

        return response()->json(['message' => 'Scope sheet restored.']);
    }

    /**
     * @OA\Post(
     *     path="/api/scope-sheets/admin/bulk-delete",
     *     tags={"Scope Sheets"},
     *     summary="Bulk soft-delete scope sheets",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=200, description="Scope sheets deleted"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteScopeSheetRequest $request): JsonResponse
    {
        $data  = BulkDeleteScopeSheetData::from($request->validated());
        $count = $this->bulkDeleteHandler->handle($data);

        return response()->json(['message' => "{$count} scope sheet(s) deleted."]);
    }
}
