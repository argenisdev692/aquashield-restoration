<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyFilterData;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;

final class PublicCompanyPdfExport
{
    public function __construct(
        private readonly PublicCompanyFilterData $filters,
    ) {
    }

    public function stream(): Response
    {
        $rows = PublicCompanyExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (PublicCompanyEloquentModel $company): array => PublicCompanyExportTransformer::transformForPdf($company))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.public_companies', [
            'title' => 'Public Companies Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('public-companies-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
