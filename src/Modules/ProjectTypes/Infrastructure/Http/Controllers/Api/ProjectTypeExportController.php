<?php

declare(strict_types=1);

namespace Src\Modules\ProjectTypes\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\ProjectTypes\Application\DTOs\ProjectTypeFilterData;
use Src\Modules\ProjectTypes\Infrastructure\Http\Export\ProjectTypeExcelExport;
use Src\Modules\ProjectTypes\Infrastructure\Http\Export\ProjectTypePdfExport;
use Src\Modules\ProjectTypes\Infrastructure\Http\Requests\ExportProjectTypeRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/project-types/data/admin/export",
 *     tags={"Project Types"},
 *     summary="Export project types to Excel or PDF",
 *     description="Downloads all project types matching the given filters as an Excel or PDF file.",
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
 *         name="service_category_uuid",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", format="uuid")
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
final class ProjectTypeExportController
{
    public function __invoke(ExportProjectTypeRequest $request): Response|BinaryFileResponse
    {
        $filters = ProjectTypeFilterData::from($request->validated());
        $format  = (string) $request->query('format', 'excel');

        return match ($format) {
            'pdf'   => (new ProjectTypePdfExport($filters))->stream(),
            default => Excel::download(
                new ProjectTypeExcelExport($filters),
                'project-types-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
