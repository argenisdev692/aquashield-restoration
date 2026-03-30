<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Src\Modules\Claims\Application\DTOs\ClaimFilterData;

final class ClaimExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly ClaimFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return ClaimExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'Internal ID',
            'Claim Number',
            'Policy Number',
            'Property Address',
            'Type of Damage',
            'Claim Status',
            'Date of Loss',
            'Status',
            'Created At',
            'Deleted At',
        ];
    }

    public function map(mixed $claim): array
    {
        return ClaimExportTransformer::transformForExcel($claim);
    }
}
