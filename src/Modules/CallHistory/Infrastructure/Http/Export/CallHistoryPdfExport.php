<?php

declare(strict_types=1);

namespace Modules\CallHistory\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Modules\CallHistory\Application\DTOs\CallHistoryFilterData;

final class CallHistoryPdfExport
{
    public function __construct(
        private readonly CallHistoryFilterData $filters,
    ) {
    }

    public function stream(): Response
    {
        $rows = CallHistoryExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn ($call): array => CallHistoryExportTransformer::transformForPdf($call))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.call_history', [
            'title' => 'Call History Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('call-history-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
