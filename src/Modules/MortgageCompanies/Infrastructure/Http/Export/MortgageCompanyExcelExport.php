<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Http\Export;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Modules\MortgageCompanies\Application\DTOs\MortgageCompanyFilterData;

final class MortgageCompanyExcelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(
        private readonly MortgageCompanyFilterData $filters,
    ) {
    }

    public function query(): Builder
    {
        return MortgageCompanyExportQuery::build($this->filters);
    }

    public function headings(): array
    {
        return [
            'Mortgage Company',
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
        return MortgageCompanyExportTransformer::transformForExcel($company);
    }
}
