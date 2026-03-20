<?php

declare(strict_types=1);

namespace Modules\InsuranceCompanies\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\InsuranceCompanies\Application\DTOs\InsuranceCompanyFilterData;

final class InsuranceCompanyExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly InsuranceCompanyFilterData $filters,
    ) {
    }

    public function query(): Builder
    {
        return InsuranceCompanyExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'Insurance Company',
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
        return InsuranceCompanyExportTransformer::transformForExcel($company);
    }
}
