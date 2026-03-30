<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\Claims\Application\DTOs\ClaimFilterData;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

final class ClaimPdfExport
{
    public function __construct(
        private readonly ClaimFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = ClaimExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (ClaimEloquentModel $claim): array => ClaimExportTransformer::transformForPdf($claim))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.claims', [
            'title'       => 'Claims Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('claims-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
