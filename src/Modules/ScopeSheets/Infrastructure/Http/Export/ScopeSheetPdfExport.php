<?php

declare(strict_types=1);

namespace Src\Modules\ScopeSheets\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\ScopeSheets\Application\DTOs\ScopeSheetFilterData;
use Src\Modules\ScopeSheets\Infrastructure\Persistence\Eloquent\Models\ScopeSheetEloquentModel;

final class ScopeSheetPdfExport
{
    public function __construct(
        private readonly ScopeSheetFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = ScopeSheetExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (ScopeSheetEloquentModel $sheet): array => ScopeSheetExportTransformer::transformForPdf($sheet))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.scope_sheets', [
            'title'       => 'Scope Sheets Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('scope-sheets-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
