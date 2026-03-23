<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Modules\DocumentTemplates\Application\Commands\BulkDeleteDocumentTemplateHandler;
use Src\Modules\DocumentTemplates\Application\Commands\CreateDocumentTemplateHandler;
use Src\Modules\DocumentTemplates\Application\Commands\DeleteDocumentTemplateHandler;
use Src\Modules\DocumentTemplates\Application\Commands\UpdateDocumentTemplateHandler;
use Src\Modules\DocumentTemplates\Application\DTOs\BulkDeleteDocumentTemplateData;
use Src\Modules\DocumentTemplates\Application\DTOs\DocumentTemplateFilterData;
use Src\Modules\DocumentTemplates\Application\DTOs\StoreDocumentTemplateData;
use Src\Modules\DocumentTemplates\Application\DTOs\UpdateDocumentTemplateData;
use Src\Modules\DocumentTemplates\Application\Queries\GetDocumentTemplateHandler;
use Src\Modules\DocumentTemplates\Application\Queries\ListDocumentTemplatesHandler;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Requests\BulkDeleteDocumentTemplateRequest;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Requests\StoreDocumentTemplateRequest;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Requests\UpdateDocumentTemplateRequest;

/**
 * @OA\Tag(name="Document Templates", description="Document templates CRUD operations")
 */
final class DocumentTemplateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/document-templates",
     *     tags={"Document Templates"},
     *     summary="List document templates",
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
     *     @OA\Parameter(name="template_type", in="query", required=false, @OA\Schema(type="string")),
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
    public function index(ListDocumentTemplatesHandler $handler): JsonResponse
    {
        $items = $handler->handle(DocumentTemplateFilterData::from(request()->query()));

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
     *     path="/api/document-templates/{uuid}",
     *     tags={"Document Templates"},
     *     summary="Show document template",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Detail"),
     *     @OA\Response(response=404, description="Not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function show(string $uuid, GetDocumentTemplateHandler $handler): JsonResponse
    {
        $item = $handler->handle($uuid);

        if ($item === null) {
            return response()->json(['message' => 'Document template not found.'], 404);
        }

        return response()->json($item);
    }

    /**
     * @OA\Post(
     *     path="/api/document-templates",
     *     tags={"Document Templates"},
     *     summary="Create document template",
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
     *         @OA\Schema(
     *             required={"template_name","template_type","template_path"},
     *             @OA\Property(property="template_name", type="string"),
     *             @OA\Property(property="template_description", type="string"),
     *             @OA\Property(property="template_type", type="string"),
     *             @OA\Property(property="template_path", type="string", format="binary")
     *         )
     *     )),
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreDocumentTemplateRequest $request, CreateDocumentTemplateHandler $handler): JsonResponse
    {
        $uuid = $handler->handle(
            StoreDocumentTemplateData::from([
                'template_name'        => $request->validated('template_name'),
                'template_description' => $request->validated('template_description'),
                'template_type'        => $request->validated('template_type'),
                'uploaded_by'          => (int) $request->user()->getAuthIdentifier(),
            ]),
            $request->file('template_path'),
        );

        return response()->json([
            'uuid'    => $uuid,
            'message' => 'Document template created successfully.',
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/document-templates/{uuid}",
     *     tags={"Document Templates"},
     *     summary="Update document template (multipart/form-data with _method=PUT)",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data",
     *         @OA\Schema(
     *             required={"template_name","template_type"},
     *             @OA\Property(property="template_name", type="string"),
     *             @OA\Property(property="template_description", type="string"),
     *             @OA\Property(property="template_type", type="string"),
     *             @OA\Property(property="template_path", type="string", format="binary")
     *         )
     *     )),
     *     @OA\Response(response=200, description="Updated"),
     *     @OA\Response(response=404, description="Not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(string $uuid, UpdateDocumentTemplateRequest $request, UpdateDocumentTemplateHandler $handler): JsonResponse
    {
        try {
            $handler->handle(
                $uuid,
                UpdateDocumentTemplateData::from($request->validated()),
                $request->hasFile('template_path') ? $request->file('template_path') : null,
            );
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['message' => 'Document template updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/document-templates/{uuid}",
     *     tags={"Document Templates"},
     *     summary="Delete document template",
     *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy(string $uuid, DeleteDocumentTemplateHandler $handler): JsonResponse
    {
        $handler->handle($uuid);

        return response()->json(['message' => 'Document template deleted successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/document-templates/bulk-delete",
     *     tags={"Document Templates"},
     *     summary="Bulk delete document templates",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="uuids", type="array", @OA\Items(type="string", format="uuid"))
     *     )),
     *     @OA\Response(response=200, description="Bulk deleted"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function bulkDelete(BulkDeleteDocumentTemplateRequest $request, BulkDeleteDocumentTemplateHandler $handler): JsonResponse
    {
        $deletedCount = $handler->handle(BulkDeleteDocumentTemplateData::from($request->validated()));

        return response()->json([
            'message'       => "Successfully deleted {$deletedCount} document template record(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
