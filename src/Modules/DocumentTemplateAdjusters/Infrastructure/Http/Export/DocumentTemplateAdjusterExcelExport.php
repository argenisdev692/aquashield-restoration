<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAdjusters\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\DocumentTemplateAdjusters\Application\DTOs\DocumentTemplateAdjusterFilterData;

final class DocumentTemplateAdjusterExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    use Exportable;

    public function __construct(
        private readonly DocumentTemplateAdjusterFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return DocumentTemplateAdjusterExportQuery::build($this->filters);
    }

    public function title(): string
    {
        return 'Document Template Adjusters';
    }

    public function headings(): array
    {
        return [
            'Description',
            'Type',
            'Public Adjuster',
            'Uploaded By',
            'Created At',
        ];
    }

    public function map(mixed $model): array
    {
        return DocumentTemplateAdjusterExportTransformer::forExcel($model);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
