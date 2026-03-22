<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Http\Controllers\Api;

use Maatwebsite\Excel\Facades\Excel;
use Modules\CallHistory\Application\DTOs\CallHistoryFilterData;
use Modules\CallHistory\Infrastructure\Http\Export\CallHistoryExcelExport;
use Modules\CallHistory\Infrastructure\Http\Export\CallHistoryPdfExport;
use Modules\CallHistory\Infrastructure\Http\Requests\CallHistoryExportRequest;

/**
 * @OA\Get(
 *     path="/api/call-history/export",
 *     tags={"Call History"},
 *     summary="Export call history records",
 *     description="Export call history data in Excel or PDF format with optional filters",
 *     security={{"sanctum": {}}},
 *     @OA\Parameter(
 *         name="format",
 *         in="query",
 *         required=false,
 *         description="Export format",
 *         @OA\Schema(type="string", enum={"excel", "pdf"}, default="excel")
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         required=false,
 *         description="Search by call ID, agent name, or phone numbers",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filter by record status",
 *         @OA\Schema(type="string", enum={"active", "deleted"})
 *     ),
 *     @OA\Parameter(
 *         name="direction",
 *         in="query",
 *         required=false,
 *         description="Call direction",
 *         @OA\Schema(type="string", enum={"inbound", "outbound"})
 *     ),
 *     @OA\Parameter(
 *         name="call_type",
 *         in="query",
 *         required=false,
 *         description="Type of call",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="date_from",
 *         in="query",
 *         required=false,
 *         description="Start date filter (Y-m-d)",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="date_to",
 *         in="query",
 *         required=false,
 *         description="End date filter (Y-m-d)",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful export - file download",
 *         @OA\Header(
 *             header="Content-Type",
 *             description="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet or application/pdf",
 *             @OA\Schema(type="string")
 *         ),
 *         @OA\Header(
 *             header="Content-Disposition",
 *             description="attachment; filename=...",
 *             @OA\Schema(type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - missing VIEW_CALL_HISTORY permission",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
final readonly class CallHistoryExportController
{
    public function __invoke(CallHistoryExportRequest $request): mixed
    {
        $filters = CallHistoryFilterData::from($request->validated());
        $format = $request->query('format', 'excel');

        if ($format === 'pdf') {
            return (new CallHistoryPdfExport($filters))->stream();
        }

        return Excel::download(
            new CallHistoryExcelExport($filters),
            'call-history-' . now()->format('Y-m-d') . '.xlsx',
        );
    }
}
