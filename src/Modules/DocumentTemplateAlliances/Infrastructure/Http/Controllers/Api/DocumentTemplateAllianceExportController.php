<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\DocumentTemplateAllianceFilterData;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Export\DocumentTemplateAllianceExcelExport;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Export\DocumentTemplateAlliancePdfExport;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Requests\ExportDocumentTemplateAllianceRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/document-template-alliances/data/admin/export",
 *     tags={"Document Template Alliances"},
 *     summary="Export document template alliances to Excel or PDF",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="alliance_company_id", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="template_type_alliance", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class DocumentTemplateAllianceExportController
{
    public function __invoke(ExportDocumentTemplateAllianceRequest $request): Response|BinaryFileResponse
    {
        $filters = DocumentTemplateAllianceFilterData::from($request->validated());
        $format  = $request->validated('format', 'excel');

        return match ($format) {
            'pdf'   => (new DocumentTemplateAlliancePdfExport($filters))->stream(),
            default => Excel::download(
                new DocumentTemplateAllianceExcelExport($filters),
                'document-template-alliances-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
