<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\ScopeSheets\Application\DTOs\ScopeSheetFilterData;
use Src\Modules\ScopeSheets\Infrastructure\Http\Export\ScopeSheetExcelExport;
use Src\Modules\ScopeSheets\Infrastructure\Http\Export\ScopeSheetPdfExport;
use Src\Modules\ScopeSheets\Infrastructure\Http\Requests\ExportScopeSheetRequest;

/**
 * @OA\Get(
 *     path="/scope-sheets/data/admin/export",
 *     tags={"Scope Sheets"},
 *     summary="Export scope sheets list to Excel or PDF",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", maxLength=255)),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="claim_id", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="File downloaded successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     security={{"sanctum": {}}}
 * )
 */
final class ScopeSheetExportController extends Controller
{
    public function __invoke(ExportScopeSheetRequest $request): mixed
    {
        $validated = $request->validated();

        $filters = ScopeSheetFilterData::from([
            'search'    => $validated['search'] ?? null,
            'status'    => $validated['status'] ?? null,
            'dateFrom'  => $validated['date_from'] ?? null,
            'dateTo'    => $validated['date_to'] ?? null,
            'claimId'   => $validated['claim_id'] ?? null,
        ]);

        $format = $request->query('format', 'excel');

        return match ($format) {
            'pdf'   => (new ScopeSheetPdfExport($filters))->stream(),
            default => Excel::download(
                new ScopeSheetExcelExport($filters),
                'scope-sheets-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
