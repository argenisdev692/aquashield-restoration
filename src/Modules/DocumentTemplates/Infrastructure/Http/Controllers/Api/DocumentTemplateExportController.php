<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\DocumentTemplates\Application\DTOs\DocumentTemplateFilterData;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Export\DocumentTemplateExcelExport;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Export\DocumentTemplatePdfExport;
use Src\Modules\DocumentTemplates\Infrastructure\Http\Requests\ExportDocumentTemplateRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/api/document-templates/export",
 *     tags={"Document Templates"},
 *     summary="Export document templates to Excel or PDF",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="template_type", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="signature_path_id", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class DocumentTemplateExportController
{
    public function __invoke(ExportDocumentTemplateRequest $request): Response|BinaryFileResponse
    {
        $filters = DocumentTemplateFilterData::from($request->validated());
        $format  = $request->validated('format', 'excel');

        return match ($format) {
            'pdf'   => (new DocumentTemplatePdfExport($filters))->stream(),
            default => Excel::download(
                new DocumentTemplateExcelExport($filters),
                'document-templates-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
