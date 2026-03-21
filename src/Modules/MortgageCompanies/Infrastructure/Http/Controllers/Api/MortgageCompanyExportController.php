<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Controllers\Api;

use Maatwebsite\Excel\Facades\Excel;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterData;
use Modules\MortgageCompanies\Infrastructure\Http\Export\MortgageCompanyExcelExport;
use Modules\MortgageCompanies\Infrastructure\Http\Export\MortgageCompanyPdfExport;
use Modules\MortgageCompanies\Infrastructure\Http\Requests\ExportMortgageCompanyRequest;

/**
 * @OA\Get(
 *     path="/mortgage-companies/data/admin/export",
 *     tags={"Mortgage Companies"},
 *     summary="Export mortgage companies to Excel or PDF",
 *     @OA\Parameter(name="format", in="query", required=false, @OA\Schema(type="string", enum={"excel","pdf"}, default="excel")),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","deleted"})),
 *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="File download"),
 *     security={{"sanctum": {}}}
 * )
 */
final class MortgageCompanyExportController
{
    public function __invoke(ExportMortgageCompanyRequest $request): mixed
    {
        $filters = MortgageCompanyFilterData::from($request->validated());
        $format  = $request->query('format', 'excel');

        if ($format === 'pdf') {
            return (new MortgageCompanyPdfExport($filters))->stream();
        }

        return Excel::download(
            new MortgageCompanyExcelExport($filters),
            'mortgage-companies-' . now()->format('Y-m-d') . '.xlsx',
        );
    }
}
