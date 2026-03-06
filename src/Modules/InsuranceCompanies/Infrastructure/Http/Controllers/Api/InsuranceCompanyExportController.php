<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterDTO;
use Modules\InsuranceCompanies\Infrastructure\Http\Export\InsuranceCompanyExcelExport;
use Modules\InsuranceCompanies\Infrastructure\Http\Export\InsuranceCompanyPdfExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @OA\Tag(name="Insurance Companies Export", description="Export insurance companies to Excel or PDF")
 */
final class InsuranceCompanyExportController
{
    /**
     * @OA\Get(
     *     path="/insurance-companies/data/admin/export",
     *     tags={"Insurance Companies Export"},
     *     summary="Export insurance companies",
     *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel", "pdf"}, default="excel")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="dateFrom", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="dateTo", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="File download"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function __invoke(Request $request): Response|BinaryFileResponse
    {
        $filters = InsuranceCompanyFilterDTO::from($request->all());
        $format = $request->query('format', 'excel');

        return match ($format) {
            'pdf' => (new InsuranceCompanyPdfExport($filters))->stream(),
            default => Excel::download(
                new InsuranceCompanyExcelExport($filters),
                'insurance-companies-export-' . now()->format('Y-m-d') . '.xlsx',
            ),
        };
    }
}
