<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\Customers\Application\DTOs\CustomerFilterData;
use Src\Modules\Customers\Infrastructure\Persistence\Eloquent\Models\CustomerEloquentModel;

final class CustomerPdfExport
{
    public function __construct(
        private readonly CustomerFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = CustomerExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (CustomerEloquentModel $customer): array => CustomerExportTransformer::transformForPdf($customer))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.customers', [
            'title'       => 'Customers Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('customers-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
