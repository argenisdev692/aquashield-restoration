<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Src\Modules\ServiceRequests\Application\DTOs\ServiceRequestFilterData;

final class ServiceRequestExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly ServiceRequestFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return ServiceRequestExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'Requested Service',
            'Status',
            'Created At',
            'Deleted At',
        ];
    }

    public function map(mixed $serviceRequest): array
    {
        return ServiceRequestExportTransformer::transformForExcel($serviceRequest);
    }
}
