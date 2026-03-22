<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\ServiceRequests\Application\DTOs\ServiceRequestFilterData;
use Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models\ServiceRequestEloquentModel;

final class ServiceRequestPdfExport
{
    public function __construct(
        private readonly ServiceRequestFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = ServiceRequestExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (ServiceRequestEloquentModel $serviceRequest): array => ServiceRequestExportTransformer::transformForPdf($serviceRequest))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.service_requests', [
            'title' => 'Service Requests Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('service-requests-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
