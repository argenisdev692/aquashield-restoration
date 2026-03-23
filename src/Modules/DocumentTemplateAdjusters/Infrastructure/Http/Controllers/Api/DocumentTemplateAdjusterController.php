<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\DocumentTemplateAdjusters\Application\Commands\BulkDeleteDocumentTemplateAdjusterHandler;
use Src\Modules\DocumentTemplateAdjusters\Application\Commands\CreateDocumentTemplateAdjusterHandler;
use Src\Modules\DocumentTemplateAdjusters\Application\Commands\DeleteDocumentTemplateAdjusterHandler;
use Src\Modules\DocumentTemplateAdjusters\Application\Commands\UpdateDocumentTemplateAdjusterHandler;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\BulkDeleteDocumentTemplateAdjusterData;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\DocumentTemplateAdjusterFilterData;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\StoreDocumentTemplateAdjusterData;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\UpdateDocumentTemplateAdjusterData;
use Src\Modules\DocumentTemplateAdjusters\Application\Queries\GetDocumentTemplateAdjusterHandler;
use Src\Modules\DocumentTemplateAdjusters\Application\Queries\ListDocumentTemplateAdjustersHandler;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Requests\BulkDeleteDocumentTemplateAdjusterRequest;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Requests\StoreDocumentTemplateAdjusterRequest;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Requests\UpdateDocumentTemplateAdjusterRequest;

/**
 * @OA\Tag(name="Document Template Adjusters", description="Document template adjusters CRUD operations")
 */
final class DocumentTemplateAdjusterController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/document-template-adjusters",
     *     tags={"Document Template Adjusters"},
     *     summary="List document template adjusters",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="public_adjuster_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="template_type_adjuster", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Paginated list", @OA\JsonContent(
     *         @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         @OA\Property(property="meta", type="object")
     *     )),
     *     @OA\Response(response=403, description="Forbidden"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(ListDocumentTemplateAdjustersHandler $handler): JsonResponse
    {
        $items = $handler->handle(DocumentTemplateAdjusterFilterData::from(request()->query()));

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
     *     path="/api/document-template-adjusters/{uuid}",
     *     tags={"Document Template Adjusters"},
     *     summary="Show document template adjuster",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Detail"),
     *     @OA\Response(response=404, description="Not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetDocumentTemplateAdjusterHandler $handler): JsonResponse
    {
        $item = $handler->handle($uuid);

        if ($item === null) {
            return response()->json(['message' => 'Document template adjuster not found.'], 404);
        }

        return response()->json($item);
    }

    /**
     * @OA\Post(
     *     path="/api/document-template-adjusters",
     *     tags={"Document Template Adjusters"},
     *     summary="Create document template adjuster",
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
     *         @OA\Schema(
     *             required={"template_type_adjuster","template_path_adjuster","public_adjuster_id"},
     *             @OA\Property(property="template_description_adjuster", type="string"),
     *             @OA\Property(property="template_type_adjuster", type="string"),
     *             @OA\Property(property="template_path_adjuster", type="string", format="binary"),
     *             @OA\Property(property="public_adjuster_id", type="integer")
     *         )
     *     )),
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreDocumentTemplateAdjusterRequest $request, CreateDocumentTemplateAdjusterHandler $handler): JsonResponse
    {
        $payload = array_merge($request->validated(), [
            'uploaded_by' => (int) $request->user()->getAuthIdentifier(),
        ]);

        $uuid = $handler->handle(
            StoreDocumentTemplateAdjusterData::from($payload),
            $request->file('template_path_adjuster'),
        );

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Document template adjuster created successfully.',
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/document-template-adjusters/{uuid}",
     *     tags={"Document Template Adjusters"},
     *     summary="Update document template adjuster (multipart/form-data with _method=PUT)",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
     *         @OA\Schema(
     *             required={"template_type_adjuster","public_adjuster_id"},
     *             @OA\Property(property="template_description_adjuster", type="string"),
     *             @OA\Property(property="template_type_adjuster", type="string"),
     *             @OA\Property(property="template_path_adjuster", type="string", format="binary"),
     *             @OA\Property(property="public_adjuster_id", type="integer")
     *         )
     *     )),
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=404, description="Not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateDocumentTemplateAdjusterRequest $request, UpdateDocumentTemplateAdjusterHandler $handler): JsonResponse
    {
        try {
            $handler->handle(
                $uuid,
                UpdateDocumentTemplateAdjusterData::from($request->validated()),
                $request->hasFile('template_path_adjuster') ? $request->file('template_path_adjuster') : null,
            );
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Document template adjuster updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/document-template-adjusters/{uuid}",
     *     tags={"Document Template Adjusters"},
     *     summary="Delete document template adjuster",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteDocumentTemplateAdjusterHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Document template adjuster deleted successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/document-template-adjusters/bulk-delete",
     *     tags={"Document Template Adjusters"},
     *     summary="Bulk delete document template adjusters",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=200, description="Bulk deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteDocumentTemplateAdjusterRequest $request, BulkDeleteDocumentTemplateAdjusterHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteDocumentTemplateAdjusterData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} document template adjuster record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
