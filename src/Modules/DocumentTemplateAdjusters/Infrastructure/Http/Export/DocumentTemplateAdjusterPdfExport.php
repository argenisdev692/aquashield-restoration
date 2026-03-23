<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\DocumentTemplateAdjusterFilterData;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAdjusterEloquentModel;

final class DocumentTemplateAdjusterPdfExport
{
    public function __construct(
        private readonly DocumentTemplateAdjusterFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = DocumentTemplateAdjusterExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (DocumentTemplateAdjusterEloquentModel $model): array => DocumentTemplateAdjusterExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.document_template_adjusters', [
            'title'       => 'Document Template Adjusters Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('document-template-adjusters-' . now()->format('Y-m-d') . '.pdf');
    }
}
