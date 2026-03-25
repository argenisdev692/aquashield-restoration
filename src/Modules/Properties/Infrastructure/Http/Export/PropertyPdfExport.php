<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\Properties\Application\DTOs\PropertyFilterData;
use Src\Modules\Properties\Infrastructure\Persistence\Eloquent\Models\PropertyEloquentModel;

final class PropertyPdfExport
{
    public function __construct(
        private readonly PropertyFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = PropertyExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (PropertyEloquentModel $property): array => PropertyExportTransformer::transformForPdf($property))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.properties', [
            'title'       => 'Properties Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('properties-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
