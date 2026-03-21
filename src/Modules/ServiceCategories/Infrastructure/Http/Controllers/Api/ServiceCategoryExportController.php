<?php

declare(strict_types=1);

namespace Src\Modules\ServiceCategories\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\ServiceCategories\Application\DTOs\ServiceCategoryFilterData;
use Src\Modules\ServiceCategories\Infrastructure\Http\Export\ServiceCategoryExcelExport;
use Src\Modules\ServiceCategories\Infrastructure\Http\Export\ServiceCategoryPdfExport;
use Src\Modules\ServiceCategories\Infrastructure\Http\Requests\ExportServiceCategoryRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/service-categories/data/admin/export",
 *     tags={"Service Categories"},
 *     summary="Export service categories to Excel or PDF",
 *     description="Downloads all service categories matching the given filters as an Excel or PDF file.",
 *     @OA\Parameter(
 *         name="format",
 *         in="query",
 *         required=false,
 *         description="Export format",
 *         @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", maxLength=255)
 *     ),
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", enum={"active","deleted"})
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="File downloaded successfully"
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class ServiceCategoryExportController
{
    public function __invoke(ExportServiceCategoryRequest $request): Response|BinaryFileResponse
    {
        $filters = ServiceCategoryFilterData::from($request->validated());
        $format  = (string) $request->query('format', 'excel');

        return match ($format) {
            'pdf'   => (new ServiceCategoryPdfExport($filters))->stream(),
            default => Excel::download(
                new ServiceCategoryExcelExport($filters),
                'service-categories-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
