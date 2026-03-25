<?php

declare(strict_types=1);

namespace Src\Modules\Properties\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Src\Modules\Properties\Application\DTOs\PropertyFilterData;

final class PropertyExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly PropertyFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return PropertyExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'Address',
            'Address 2',
            'State',
            'City',
            'Postal Code',
            'Country',
            'Latitude',
            'Longitude',
            'Status',
            'Created At',
            'Deleted At',
        ];
    }

    public function map(mixed $property): array
    {
        return PropertyExportTransformer::transformForExcel($property);
    }
}
