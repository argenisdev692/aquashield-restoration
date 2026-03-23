<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Src\Modules\FilesEsx\Application\DTOs\FileEsxFilterData;

final class FileEsxExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly FileEsxFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return FileEsxExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'UUID',
            'File Name',
            'File Path',
            'Uploaded By',
            'Assigned Adjusters',
            'Status',
            'Created At',
            'Deleted At',
        ];
    }

    public function map(mixed $file): array
    {
        return FileEsxExportTransformer::transformForExcel($file);
    }
}
