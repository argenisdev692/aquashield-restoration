<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\DocumentTemplates\Application\DTOs\DocumentTemplateFilterData;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateEloquentModel;

final class DocumentTemplatePdfExport
{
    public function __construct(
        private readonly DocumentTemplateFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = DocumentTemplateExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (DocumentTemplateEloquentModel $model): array => DocumentTemplateExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.document_templates', [
            'title'       => 'Document Templates Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('document-templates-' . now()->format('Y-m-d') . '.pdf');
    }
}
