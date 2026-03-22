<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\DocumentTemplateAllianceFilterData;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAllianceEloquentModel;

final class DocumentTemplateAlliancePdfExport
{
    public function __construct(
        private readonly DocumentTemplateAllianceFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = DocumentTemplateAllianceExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (DocumentTemplateAllianceEloquentModel $model): array => DocumentTemplateAllianceExportTransformer::forPdf($model))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.document_template_alliances', [
            'title'       => 'Document Template Alliances Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('document-template-alliances-' . now()->format('Y-m-d') . '.pdf');
    }
}
