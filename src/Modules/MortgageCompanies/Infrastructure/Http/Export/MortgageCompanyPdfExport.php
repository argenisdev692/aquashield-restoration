<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterData;
use Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models\MortgageCompanyEloquentModel;

final class MortgageCompanyPdfExport
{
    public function __construct(
        private readonly MortgageCompanyFilterData $filters,
    ) {
    }

    public function stream(): Response
    {
        $rows = MortgageCompanyExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (MortgageCompanyEloquentModel $company): array => MortgageCompanyExportTransformer::transformForPdf($company))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.mortgage_companies', [
            'title'       => 'Mortgage Companies Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('mortgage-companies-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
