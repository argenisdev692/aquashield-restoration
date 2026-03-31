<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Src\Modules\ScopeSheets\Infrastructure\Services\ScopeSheetDocumentService;

/**
 * @OA\Get(
 *     path="/api/scope-sheets/admin/{uuid}/generate-pdf",
 *     tags={"Scope Sheets"},
 *     summary="Generate the scope sheet document PDF (stores to R2 and returns download)",
 *     @OA\Parameter(name="uuid", in="path", required=true, @OA\Schema(type="string", format="uuid")),
 *     @OA\Parameter(name="download", in="query", required=false, @OA\Schema(type="boolean", default=false)),
 *     @OA\Response(response=200, description="PDF document streamed or download link returned"),
 *     @OA\Response(response=404, description="Not found"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class ScopeSheetDocumentController extends Controller
{
    public function __construct(
        private readonly ScopeSheetDocumentService $documentService,
    ) {}

    public function __invoke(string $uuid): Response|JsonResponse
    {
        return $this->documentService->generate($uuid);
    }
}
