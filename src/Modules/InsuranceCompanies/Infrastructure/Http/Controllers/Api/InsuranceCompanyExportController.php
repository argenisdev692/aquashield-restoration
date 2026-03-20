<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Controllers\Api;

use Maatwebsite\Excel\Facades\Excel;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterData;
use Modules\InsuranceCompanies\Infrastructure\Http\Export\InsuranceCompanyExcelExport;
use Modules\InsuranceCompanies\Infrastructure\Http\Export\InsuranceCompanyPdfExport;
use Modules\InsuranceCompanies\Infrastructure\Http\Requests\ExportInsuranceCompanyRequest;

/**
 * InsuranceCompanyExportController
 *
 * @OA\Get(
 *     path="/api/insurance-companies/export",
 *     tags={"Insurance Companies"},
 *     summary="Export insurance companies",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel", "pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active", "deleted"})),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File download"),
 *     security={{"sanctum": {}}}
 * )
 */
final class InsuranceCompanyExportController
{
    public function __invoke(ExportInsuranceCompanyRequest $request): mixed
    {
        $filters = InsuranceCompanyFilterData::from($request->validated());
        $format = $request->query('format', 'excel');

        if ($format === 'pdf') {
            return (new InsuranceCompanyPdfExport($filters))->stream();
        }

        return Excel::download(
            new InsuranceCompanyExcelExport($filters),
            'insurance-companies-' . now()->format('Y-m-d') . '.xlsx',
        );
    }
}
