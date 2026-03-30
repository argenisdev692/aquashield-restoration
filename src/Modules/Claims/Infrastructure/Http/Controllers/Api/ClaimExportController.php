<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\Claims\Application\DTOs\ClaimFilterData;
use Src\Modules\Claims\Infrastructure\Http\Export\ClaimExcelExport;
use Src\Modules\Claims\Infrastructure\Http\Export\ClaimPdfExport;
use Src\Modules\Claims\Infrastructure\Http\Requests\ExportClaimRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ClaimExportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/claims/export",
     *     tags={"Claims"},
     *     summary="Export claims as Excel or PDF",
     *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="File download"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function __invoke(ExportClaimRequest $request): Response|BinaryFileResponse
    {
        $filters = ClaimFilterData::from($request->validated());
        $format  = $request->input('format', 'excel');

        return match ($format) {
            'pdf' => (new ClaimPdfExport($filters))->stream(),
            default => Excel::download(
                new ClaimExcelExport($filters),
                'claims-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
