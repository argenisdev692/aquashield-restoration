<?php

declare(strict_types=1);

namespace Src\Modules\ClaimStatuses\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\ClaimStatuses\Application\DTOs\ClaimStatusFilterData;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Export\ClaimStatusExcelExport;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Export\ClaimStatusPdfExport;
use Src\Modules\ClaimStatuses\Infrastructure\Http\Requests\ExportClaimStatusRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Get(
 *     path="/api/claim-statuses/export",
 *     tags={"Claim Statuses"},
 *     summary="Export claim statuses to Excel or PDF",
 *     description="Downloads all claim statuses matching the given filters as an Excel or PDF file.",
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
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class ClaimStatusExportController
{
    public function __invoke(ExportClaimStatusRequest $request): Response|BinaryFileResponse
    {
        $filters = ClaimStatusFilterData::from($request->validated());
        $format  = (string) $request->query('format', 'excel');

        return match ($format) {
            'pdf'   => (new ClaimStatusPdfExport($filters))->stream(),
            default => Excel::download(
                new ClaimStatusExcelExport($filters),
                'claim-statuses-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
