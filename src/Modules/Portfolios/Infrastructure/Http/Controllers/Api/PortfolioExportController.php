<?php

declare(strict_types=1);

namespace Src\Modules\Portfolios\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\Portfolios\Application\DTOs\PortfolioFilterData;
use Src\Modules\Portfolios\Infrastructure\Http\Export\PortfolioExcelExport;
use Src\Modules\Portfolios\Infrastructure\Http\Export\PortfolioPdfExport;
use Src\Modules\Portfolios\Infrastructure\Http\Requests\ExportPortfolioRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/portfolios/data/admin/export",
 *     tags={"Portfolios"},
 *     summary="Export portfolios to Excel or PDF",
 *     description="Downloads all portfolios matching the given filters as an Excel or PDF file.",
 *     @OA\Parameter(
 *         name="format",
 *         in="query",
 *         required=false,
 *         description="Export format",
 *         @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")
 *     ),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
 *     @OA\Parameter(name="project_type_uuid", in="query", required=false, @OA\Schema(type="string", format="uuid")),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class PortfolioExportController
{
    public function __invoke(ExportPortfolioRequest $request): Response|BinaryFileResponse
    {
        $filters = PortfolioFilterData::from($request->validated());
        $format  = $request->query('format', 'excel') |> (fn(mixed $f): string => (string) $f);

        return match ($format) {
            'pdf'   => (new PortfolioPdfExport($filters))->stream(),
            default => Excel::download(
                new PortfolioExcelExport($filters),
                'portfolios-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
