<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Controllers\Api;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Src\Modules\Customers\Application\DTOs\CustomerFilterData;
use Src\Modules\Customers\Infrastructure\Http\Export\CustomerExcelExport;
use Src\Modules\Customers\Infrastructure\Http\Export\CustomerPdfExport;
use Src\Modules\Customers\Infrastructure\Http\Requests\ExportCustomerRequest;

/**
 * @OA\Get(
 *     path="/customers/data/admin/export",
 *     tags={"Customers"},
 *     summary="Export customers to Excel or PDF",
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
final class CustomerExportController
{
    public function __invoke(ExportCustomerRequest $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = CustomerFilterData::from($request->validated());
        $format  = $request->validated('format', 'excel');

        return match ($format) {
            'pdf'   => (new CustomerPdfExport($filters))->stream(),
            default => Excel::download(
                new CustomerExcelExport($filters),
                'customers-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
