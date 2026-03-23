<?php

declare(strict_types=1);

namespace Src\Modules\Zones\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\Zones\Application\DTOs\ZoneFilterData;
use Src\Modules\Zones\Infrastructure\Http\Export\ZoneExcelExport;
use Src\Modules\Zones\Infrastructure\Http\Export\ZonePdfExport;
use Src\Modules\Zones\Infrastructure\Http\Requests\ExportZoneRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/zones/data/admin/export",
 *     tags={"Zones"},
 *     summary="Export zones to Excel or PDF",
 *     description="Downloads all zones matching the given filters as an Excel or PDF file.",
 *     @OA\Parameter(
 *         name="format",
 *         in="query",
 *         required=false,
 *         description="Export format",
 *         @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")
 *     ),
 *     @OA\Parameter(name="search",    in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="status",    in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
 *     @OA\Parameter(name="zone_type", in="query", required=false, @OA\Schema(type="string", enum={"interior","exterior","basement","attic","garage","crawlspace"})),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to",   in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class ZoneExportController
{
    public function __invoke(ExportZoneRequest $request): Response|BinaryFileResponse
    {
        $filters = ZoneFilterData::from($request->validated());
        $format  = (string) $request->query('format', 'excel');

        return match ($format) {
            'pdf'   => (new ZonePdfExport($filters))->stream(),
            default => Excel::download(
                new ZoneExcelExport($filters),
                'zones-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
