<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Src\Modules\Customers\Application\DTOs\CustomerFilterData;

final class CustomerExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly CustomerFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return CustomerExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Last Name',
            'Email',
            'Cell Phone',
            'Home Phone',
            'Occupation',
            'Assigned User',
            'Status',
            'Created At',
            'Deleted At',
        ];
    }

    public function map(mixed $customer): array
    {
        return CustomerExportTransformer::transformForExcel($customer);
    }
}
