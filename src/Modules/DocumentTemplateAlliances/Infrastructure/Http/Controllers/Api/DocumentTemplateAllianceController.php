<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\DocumentTemplateAlliances\Application\Commands\BulkDeleteDocumentTemplateAllianceHandler;
use Src\Modules\DocumentTemplateAlliances\Application\Commands\CreateDocumentTemplateAllianceHandler;
use Src\Modules\DocumentTemplateAlliances\Application\Commands\DeleteDocumentTemplateAllianceHandler;
use Src\Modules\DocumentTemplateAlliances\Application\Commands\UpdateDocumentTemplateAllianceHandler;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\BulkDeleteDocumentTemplateAllianceData;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\DocumentTemplateAllianceFilterData;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\StoreDocumentTemplateAllianceData;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\UpdateDocumentTemplateAllianceData;
use Src\Modules\DocumentTemplateAlliances\Application\Queries\GetDocumentTemplateAllianceHandler;
use Src\Modules\DocumentTemplateAlliances\Application\Queries\ListDocumentTemplateAlliancesHandler;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Requests\BulkDeleteDocumentTemplateAllianceRequest;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Requests\StoreDocumentTemplateAllianceRequest;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Requests\UpdateDocumentTemplateAllianceRequest;

/**
 * @OA\Tag(name="Document Template Alliances", description="Document template alliances CRUD operations")
 */
final class DocumentTemplateAllianceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/document-template-alliances",
     *     tags={"Document Template Alliances"},
     *     summary="List document template alliances",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="alliance_company_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="template_type_alliance", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated list"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListDocumentTemplateAlliancesHandler $handler): JsonResponse
    {
        $items = $handler->handle(DocumentTemplateAllianceFilterData::from(request()->query()));

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/document-template-alliances/{uuid}",
     *     tags={"Document Template Alliances"},
     *     summary="Show document template alliance",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Detail"),
     *     @OA\Response(response=404, description="Not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetDocumentTemplateAllianceHandler $handler): JsonResponse
    {
        $item = $handler->handle($uuid);

        if ($item === null) {
            return response()->json(['message' => 'Document template alliance not found.'], 404);
        }

        return response()->json($item);
    }

    /**
     * @OA\Post(
     *     path="/api/document-template-alliances",
     *     tags={"Document Template Alliances"},
     *     summary="Create document template alliance",
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
     *         @OA\Schema(required={"template_name_alliance","template_type_alliance","template_path_alliance","alliance_company_id"},
     *             @OA\Property(property="template_name_alliance", type="string"),
     *             @OA\Property(property="template_description_alliance", type="string"),
     *             @OA\Property(property="template_type_alliance", type="string"),
     *             @OA\Property(property="template_path_alliance", type="string", format="binary"),
     *             @OA\Property(property="alliance_company_id", type="integer")
     *         )
     *     )),
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreDocumentTemplateAllianceRequest $request, CreateDocumentTemplateAllianceHandler $handler): JsonResponse
    {
        $payload = array_merge($request->validated(), [
            'uploaded_by' => (int) $request->user()->getAuthIdentifier(),
        ]);

        $uuid = $handler->handle(
            StoreDocumentTemplateAllianceData::from($payload),
            $request->file('template_path_alliance'),
        );

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Document template alliance created successfully.',
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/document-template-alliances/{uuid}",
     *     tags={"Document Template Alliances"},
     *     summary="Update document template alliance (multipart/form-data with _method=PUT)",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
     *         @OA\Schema(required={"template_name_alliance","template_type_alliance","alliance_company_id"},
     *             @OA\Property(property="template_name_alliance", type="string"),
     *             @OA\Property(property="template_description_alliance", type="string"),
     *             @OA\Property(property="template_type_alliance", type="string"),
     *             @OA\Property(property="template_path_alliance", type="string", format="binary"),
     *             @OA\Property(property="alliance_company_id", type="integer")
     *         )
     *     )),
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=404, description="Not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateDocumentTemplateAllianceRequest $request, UpdateDocumentTemplateAllianceHandler $handler): JsonResponse
    {
        try {
            $handler->handle(
                $uuid,
                UpdateDocumentTemplateAllianceData::from($request->validated()),
                $request->hasFile('template_path_alliance') ? $request->file('template_path_alliance') : null,
            );
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Document template alliance updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/document-template-alliances/{uuid}",
     *     tags={"Document Template Alliances"},
     *     summary="Delete document template alliance",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteDocumentTemplateAllianceHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Document template alliance deleted successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/document-template-alliances/bulk-delete",
     *     tags={"Document Template Alliances"},
     *     summary="Bulk delete document template alliances",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=200, description="Bulk deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteDocumentTemplateAllianceRequest $request, BulkDeleteDocumentTemplateAllianceHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteDocumentTemplateAllianceData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} document template alliance record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
