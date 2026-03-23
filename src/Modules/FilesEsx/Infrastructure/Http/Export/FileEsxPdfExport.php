<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Src\Modules\FilesEsx\Application\DTOs\FileEsxFilterData;
use Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models\FileEsxEloquentModel;

final class FileEsxPdfExport
{
    public function __construct(
        private readonly FileEsxFilterData $filters,
    ) {}

    public function stream(): Response
    {
        $rows = FileEsxExportQuery::build($this->filters)
            ->cursor()
            ->map(static fn (FileEsxEloquentModel $file): array => FileEsxExportTransformer::transformForPdf($file))
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.pdf.files_esx', [
            'title'       => 'Files ESX Report',
            'generatedAt' => now()->format('F j, Y H:i'),
            'rows'        => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('files-esx-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
