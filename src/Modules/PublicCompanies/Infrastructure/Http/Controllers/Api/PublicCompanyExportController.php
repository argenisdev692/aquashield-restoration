<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Controllers\Api;

use Maatwebsite\Excel\Facades\Excel;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyFilterData;
use Modules\PublicCompanies\Infrastructure\Http\Export\PublicCompanyExcelExport;
use Modules\PublicCompanies\Infrastructure\Http\Export\PublicCompanyPdfExport;
use Modules\PublicCompanies\Infrastructure\Http\Requests\ExportPublicCompanyRequest;

final class PublicCompanyExportController
{
    public function __invoke(ExportPublicCompanyRequest $request): mixed
    {
        $filters = PublicCompanyFilterData::from($request->validated());
        $format = $request->query('format', 'excel');

        if ($format === 'pdf') {
            return (new PublicCompanyPdfExport($filters))->stream();
        }

        return Excel::download(
            new PublicCompanyExcelExport($filters),
            'public-companies-' . now()->format('Y-m-d') . '.xlsx',
        );
    }
}
