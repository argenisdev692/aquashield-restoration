<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Http\Controllers\Api;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\ServiceRequests\Application\DTOs\ServiceRequestFilterData;
use Src\Modules\ServiceRequests\Infrastructure\Http\Export\ServiceRequestExcelExport;
use Src\Modules\ServiceRequests\Infrastructure\Http\Export\ServiceRequestPdfExport;
use Src\Modules\ServiceRequests\Infrastructure\Http\Requests\ExportServiceRequestRequest;

/**
 * @OA\Get(
 *     path="/service-requests/data/admin/export",
 *     tags={"Service Requests"},
 *     summary="Export service requests",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel", "pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active", "deleted"})),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File download"),
 *     security={{"sanctum": {}}}
 * )
 */
final class ServiceRequestExportController
{
    public function __invoke(ExportServiceRequestRequest $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = ServiceRequestFilterData::from($request->validated());
        $format = $request->validated('format', 'excel');

        return match ($format) {
            'pdf' => (new ServiceRequestPdfExport($filters))->stream(),
            default => Excel::download(
                new ServiceRequestExcelExport($filters),
                'service-requests-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
