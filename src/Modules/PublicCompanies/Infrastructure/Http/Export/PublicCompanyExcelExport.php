<?php

declare(strict_types=1);

namespace Modules\PublicCompanies\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\PublicCompanies\Application\DTOs\PublicCompanyFilterData;

final class PublicCompanyExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly PublicCompanyFilterData $filters,
    ) {
    }

    public function query(): Builder
    {
        return PublicCompanyExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'Public Company',
            'Email',
            'Phone',
            'Address',
            'Website',
            'Status',
            'Created At',
            'Deleted At',
        ];
    }

    public function map(mixed $company): array
    {
        return PublicCompanyExportTransformer::transformForExcel($company);
    }
}
