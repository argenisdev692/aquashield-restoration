<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\DocumentTemplateAdjusterFilterData;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Export\DocumentTemplateAdjusterExcelExport;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Export\DocumentTemplateAdjusterPdfExport;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Requests\ExportDocumentTemplateAdjusterRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/api/document-template-adjusters/export",
 *     tags={"Document Template Adjusters"},
 *     summary="Export document template adjusters to Excel or PDF",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="public_adjuster_id", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="template_type_adjuster", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class DocumentTemplateAdjusterExportController
{
    public function __invoke(ExportDocumentTemplateAdjusterRequest $request): Response|BinaryFileResponse
    {
        $filters = DocumentTemplateAdjusterFilterData::from($request->validated());
        $format  = $request->validated('format', 'excel');

        return match ($format) {
            'pdf'   => (new DocumentTemplateAdjusterPdfExport($filters))->stream(),
            default => Excel::download(
                new DocumentTemplateAdjusterExcelExport($filters),
                'document-template-adjusters-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
