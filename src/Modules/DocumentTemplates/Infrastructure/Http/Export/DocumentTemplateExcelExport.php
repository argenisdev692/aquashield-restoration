<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplates\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\DocumentTemplates\Application\DTOs\DocumentTemplateFilterData;

final class DocumentTemplateExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    use Exportable;

    public function __construct(
        private readonly DocumentTemplateFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return DocumentTemplateExportQuery::build($this->filters);
    }

    public function title(): string
    {
        return 'Document Templates';
    }

    public function headings(): array
    {
        return [
            'Template Name',
            'Description',
            'Type',
            'Uploaded By',
            'Created At',
        ];
    }

    public function map(mixed $model): array
    {
        return DocumentTemplateExportTransformer::forExcel($model);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
