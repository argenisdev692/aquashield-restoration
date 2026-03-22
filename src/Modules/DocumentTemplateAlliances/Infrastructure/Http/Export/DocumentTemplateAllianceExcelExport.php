<?php

declare(strict_types=1);

namespace Src\Modules\DocumentTemplateAlliances\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Src\Modules\DocumentTemplateAlliances\Application\DTOs\DocumentTemplateAllianceFilterData;

final class DocumentTemplateAllianceExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    use Exportable;

    public function __construct(
        private readonly DocumentTemplateAllianceFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return DocumentTemplateAllianceExportQuery::build($this->filters);
    }

    public function title(): string
    {
        return 'Document Template Alliances';
    }

    public function headings(): array
    {
        return [
            'Template Name',
            'Description',
            'Type',
            'Alliance Company',
            'Uploaded By',
            'Created At',
        ];
    }

    public function map(mixed $model): array
    {
        return DocumentTemplateAllianceExportTransformer::forExcel($model);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
