<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterData;
use Modules\InsuranceCompanies\Infrastructure\Persistence\Eloquent\Models\InsuranceCompanyEloquentModel;

final class InsuranceCompanyPdfExport
{
    public function __construct(
        private readonly InsuranceCompanyFilterData $filters,
    ) {
    }

    public function stream(): Response
    {
        $rows = InsuranceCompanyExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (InsuranceCompanyEloquentModel $company): array => InsuranceCompanyExportTransformer::transformForPdf($company))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.insurance_companies', [
            'title' => 'Insurance Companies Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('insurance-companies-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
