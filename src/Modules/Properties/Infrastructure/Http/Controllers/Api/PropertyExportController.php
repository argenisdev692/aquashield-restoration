<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\Properties\Application\DTOs\PropertyFilterData;
use Src\Modules\Properties\Infrastructure\Http\Export\PropertyExcelExport;
use Src\Modules\Properties\Infrastructure\Http\Export\PropertyPdfExport;
use Src\Modules\Properties\Infrastructure\Http\Requests\ExportPropertyRequest;

/**
 * @OA\Get(
 *     path="/properties/data/admin/export",
 *     tags={"Properties"},
 *     summary="Export properties to Excel or PDF",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class PropertyExportController
{
    public function __invoke(ExportPropertyRequest $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = PropertyFilterData::from($request->validated());
        $format  = $request->validated('format', 'excel');

        return match ($format) {
            'pdf'   => (new PropertyPdfExport($filters))->stream(),
            default => Excel::download(
                new PropertyExcelExport($filters),
                'properties-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
